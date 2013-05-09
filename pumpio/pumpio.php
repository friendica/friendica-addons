<?php
/**
 * Name: pump.io Post Connector
 * Description: Post to pump.io
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */

//require_once('library/OAuth1.php');
//require_once('addon/pumpio/pumpiooauth/pumpiooauth.php');

require('addon/pumpio/oauth/http.php');
require('addon/pumpio/oauth/oauth_client.php');

function pumpio_install() {
    register_hook('post_local',           'addon/pumpio/pumpio.php', 'pumpio_post_local');
    register_hook('notifier_normal',      'addon/pumpio/pumpio.php', 'pumpio_send');
    register_hook('jot_networks',         'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
    register_hook('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
    register_hook('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');

}
function pumpio_uninstall() {
    unregister_hook('post_local',       'addon/pumpio/pumpio.php', 'pumpio_post_local');
    unregister_hook('notifier_normal',  'addon/pumpio/pumpio.php', 'pumpio_send');
    unregister_hook('jot_networks',     'addon/pumpio/pumpio.php', 'pumpio_jot_nets');
    unregister_hook('connector_settings',      'addon/pumpio/pumpio.php', 'pumpio_settings');
    unregister_hook('connector_settings_post', 'addon/pumpio/pumpio.php', 'pumpio_settings_post');
}

function pumpio_module() {}

function pumpio_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return '';
	}

	if (isset($a->argv[1]))
		switch ($a->argv[1]) {
			case "connect":
				$o = pumpio_connect($a);
				break;
			default:
				$o = print_r($a->argv, true);
				break;
		}
	else
		$o = pumpio_connect($a);

	return $o;
}

function pumpio_registerclient($host) {

	$url = "https://".$host."/api/client/register";

        $params = array();

        $params["type"] = "client_associate";
        $params["contacts"] = "icarus@dabo.de";
        $params["application_type"] = "native";
        $params["application_name"] = "pirati.ca";
        $params["logo_url"] = "https://pirati.ca/images/friendica-256.png";
        $params["redirect_uris"] = "http://pirati.ca/addon/pumpio/pumpio.php";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_USERAGENT, "Friendica");

        $s = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        if ($curl_info["http_code"] == "200") {
                $values = json_decode($s);
		return($values);
                $pumpio = array();
                $pumpio["client_id"] = $values->client_id;
                $pumpio["client_secret"] = $values->client_secret;
                print_r($values);
        }
	return(false);
}

function pumpio_connect($a) {
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Define the needed keys
	$consumer_key = get_pconfig(local_user(), 'pumpio','consumer_key');
	$consumer_secret = get_pconfig(local_user(), 'pumpio','consumer_secret');
	$hostname = get_pconfig(local_user(), 'pumpio','host');

	if ((($consumer_key == "") OR ($consumer_secret == "")) AND ($hostname != "")) {
		$clientdata = pumpio_registerclient($hostname);
		set_pconfig(local_user(), 'pumpio','consumer_key', $clientdata->client_id);
		set_pconfig(local_user(), 'pumpio','consumer_secret', $clientdata->client_secret);

		$consumer_key = get_pconfig(local_user(), 'pumpio','consumer_key');
		$consumer_secret = get_pconfig(local_user(), 'pumpio','consumer_secret');
	}

	if (($consumer_key == "") OR ($consumer_secret == ""))
		return;

	// The callback URL is the script that gets called after the user authenticates with pumpio
	$callback_url = $a->get_baseurl()."/pumpio/connect";

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to pumpio's login page.

	// Create a new instance of the TumblrOAuth library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$client = new oauth_client_class;
	$client->debug = 1;
	$client->server = '';
	$client->oauth_version = '1.0a';
	$client->request_token_url = 'https://'.$hostname.'/oauth/request_token';
	$client->dialog_url = 'https://'.$hostname.'/oauth/authorize';
	$client->access_token_url = 'https://'.$hostname.'/oauth/access_token';
	$client->url_parameters = false;
	$client->authorization_header = true;
	$client->redirect_uri = $callback_url;
	$client->client_id = $consumer_key;
	$client->client_secret = $consumer_secret;

	if (($success = $client->Initialize())) {
		if (($success = $client->Process())) {
			if (strlen($client->access_token)) {
				set_pconfig(local_user(), "pumpio", "oauth_token", $client->access_token);
				set_pconfig(local_user(), "pumpio", "oauth_token_secret", $client->access_token_secret);
			}
		}
		$success = $client->Finalize($success);
	}
        if($client->exit)
	    $o = 'Could not connect to pumpio. Refresh the page or try again later.';

        if($success) {
		$o .= t("You are now authenticated to pumpio.");
		$o .= '<br /><a href="'.$a->get_baseurl().'/settings/connectors">'.t("return to the connector page").'</a>';
	}

	return($o);
}

function pumpio_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $pumpio_post = get_pconfig(local_user(),'pumpio','post');
    if(intval($pumpio_post) == 1) {
        $pumpio_defpost = get_pconfig(local_user(),'pumpio','post_by_default');
        $selected = ((intval($pumpio_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="pumpio_enable"' . $selected . ' value="1" /> '
            . t('Post to pumpio') . '</div>';
    }
}


function pumpio_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/pumpio/pumpio.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'pumpio','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'pumpio','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

    $servername = get_pconfig(local_user(), "pumpio", "host");
    $username = get_pconfig(local_user(), "pumpio", "user");

    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('Pump.io Post Settings') . '</h3>';

    $s .= '<div id="pumpio-servername-wrapper">';
    $s .= '<label id="pumpio-servername-label" for="pumpio-servername">'.t('pump.io servername').'</label>';
    $s .= '<input id="pumpio-servername" type="text" name="pumpio_host" value="'.$servername.'" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="pumpio-username-wrapper">';
    $s .= '<label id="pumpio-username-label" for="pumpio-username">'.t('pump.io username').'</label>';
    $s .= '<input id="pumpio-username" type="text" name="pumpio_user" value="'.$username.'" />';
    $s .= '</div><div class="clear"></div>';

    if (($username != '') AND ($servername != '')) {
	$s .= '<div id="pumpio-authenticate-wrapper">';
	$s .= '<a href="'.$a->get_baseurl().'/pumpio/connect">'.t("(Re-)Authenticate your pump.io connection").'</a>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="pumpio-enable-wrapper">';
	$s .= '<label id="pumpio-enable-label" for="pumpio-checkbox">' . t('Enable pump.io Post Plugin') . '</label>';
	$s .= '<input id="pumpio-checkbox" type="checkbox" name="pumpio" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="pumpio-bydefault-wrapper">';
	$s .= '<label id="pumpio-bydefault-label" for="pumpio-bydefault">' . t('Post to pump.io by default') . '</label>';
	$s .= '<input id="pumpio-bydefault" type="checkbox" name="pumpio_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$oauth_token = get_pconfig(local_user(), "pumpio", "oauth_token");
	$oauth_token_secret = get_pconfig(local_user(), "pumpio", "oauth_token_secret");

	$s .= '<div id="pumpio-password-wrapper">';
	if (($oauth_token == "") OR ($oauth_token_secret == ""))
		$s .= t("You are not authenticated to pumpio");

	$s .= '</div><div class="clear"></div>';
    }

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="pumpio-submit" name="pumpio-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function pumpio_settings_post(&$a,&$b) {

	if(x($_POST,'pumpio-submit')) {

		set_pconfig(local_user(),'pumpio','post',intval($_POST['pumpio']));
		set_pconfig(local_user(),'pumpio','host',$_POST['pumpio_host']);
		set_pconfig(local_user(),'pumpio','user',$_POST['pumpio_user']);
		set_pconfig(local_user(),'pumpio','post_by_default',intval($_POST['pumpio_bydefault']));

	}

}

function pumpio_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

	$pumpio_post   = intval(get_pconfig(local_user(),'pumpio','post'));

	$pumpio_enable = (($pumpio_post && x($_REQUEST,'pumpio_enable')) ? intval($_REQUEST['pumpio_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'pumpio','post_by_default')))
		$pumpio_enable = 1;

	if(! $pumpio_enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'pumpio';
}




function pumpio_send(&$a,&$b) {

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'pumpio'))
		return;

	if($b['parent'] != $b['id'])
		return;

	$oauth_token = get_pconfig($b['uid'], "pumpio", "oauth_token");
	$oauth_token_secret = get_pconfig($b['uid'], "pumpio", "oauth_token_secret");
	$consumer_key = get_pconfig($b['uid'], "pumpio","consumer_key");
	$consumer_secret = get_pconfig($b['uid'], "pumpio","consumer_secret");

	$host = get_pconfig($b['uid'], "pumpio", "host");
	$user = get_pconfig($b['uid'], "pumpio", "user");

	if($oauth_token && $oauth_token_secret) {

		require_once('include/bbcode.php');

		$title = trim($b['title']);

		if ($title != '')
			$title = "<h4>".$title."</h4>";

		$params->verb = "post";

		$params->object = array(
					'objectType' => "note",
					'content' => $title.bbcode($b['body'], false, false));

		$client = new oauth_client_class;
		$client->oauth_version = '1.0a';
		$client->url_parameters = false;
		$client->authorization_header = true;
		$client->access_token = $oauth_token;
		$client->access_token_secret = $oauth_token_secret;
		$client->client_id = $consumer_key;
		$client->client_secret = $consumer_secret;

		$success = $client->CallAPI(
					'https://'.$host.'/api/user/'.$user.'/feed',
					'POST', $params, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

		if($success)
			logger('pumpio_send: success');
		else
			logger('pumpio_send: general error: ' . print_r($user,true));

	}
}

