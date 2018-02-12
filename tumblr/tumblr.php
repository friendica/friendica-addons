<?php

/**
 * Name: Tumblr Post Connector
 * Description: Post to Tumblr
 * Version: 2.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

require_once('library/OAuth1.php');
require_once('addon/tumblr/tumblroauth/tumblroauth.php');

function tumblr_install() {
	register_hook('post_local',           'addon/tumblr/tumblr.php', 'tumblr_post_local');
	register_hook('notifier_normal',      'addon/tumblr/tumblr.php', 'tumblr_send');
	register_hook('jot_networks',         'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
	register_hook('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
	register_hook('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');

}
function tumblr_uninstall() {
	unregister_hook('post_local',       'addon/tumblr/tumblr.php', 'tumblr_post_local');
	unregister_hook('notifier_normal',  'addon/tumblr/tumblr.php', 'tumblr_send');
	unregister_hook('jot_networks',     'addon/tumblr/tumblr.php', 'tumblr_jot_nets');
	unregister_hook('connector_settings',      'addon/tumblr/tumblr.php', 'tumblr_settings');
	unregister_hook('connector_settings_post', 'addon/tumblr/tumblr.php', 'tumblr_settings_post');
}

function tumblr_module() {}

function tumblr_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return '';
	}

	if (isset($a->argv[1]))
		switch ($a->argv[1]) {
			case "connect":
				$o = tumblr_connect($a);
				break;
			case "callback":
				$o = tumblr_callback($a);
				break;
			default:
				$o = print_r($a->argv, true);
				break;
		}
	else
		$o = tumblr_connect($a);

	return $o;
}

function tumblr_plugin_admin(&$a, &$o){
        $t = get_markup_template( "admin.tpl", "addon/tumblr/" );

        $o = replace_macros($t, array(
                '$submit' => t('Save Settings'),
                                                                // name, label, value, help, [extra values]
                '$consumer_key' => array('consumer_key', t('Consumer Key'),  get_config('tumblr', 'consumer_key' ), ''),
                '$consumer_secret' => array('consumer_secret', t('Consumer Secret'),  get_config('tumblr', 'consumer_secret' ), ''),
        ));
}

function tumblr_plugin_admin_post(&$a){
        $consumer_key     =       ((x($_POST,'consumer_key'))              ? notags(trim($_POST['consumer_key']))   : '');
        $consumer_secret =       ((x($_POST,'consumer_secret'))   ? notags(trim($_POST['consumer_secret'])): '');
        set_config('tumblr','consumer_key',$consumer_key);
        set_config('tumblr','consumer_secret',$consumer_secret);
        info( t('Settings updated.'). EOL );
}

function tumblr_connect($a) {
	// Start a session.  This is necessary to hold on to  a few keys the callback script will also need
	session_start();

	// Include the TumblrOAuth library
	//require_once('addon/tumblr/tumblroauth/tumblroauth.php');

	// Define the needed keys
	$consumer_key = get_config('tumblr','consumer_key');
	$consumer_secret = get_config('tumblr','consumer_secret');

	// The callback URL is the script that gets called after the user authenticates with tumblr
	// In this example, it would be the included callback.php
	$callback_url = $a->get_baseurl()."/tumblr/callback";

	// Let's begin.  First we need a Request Token.  The request token is required to send the user
	// to Tumblr's login page.

	// Create a new instance of the TumblrOAuth library.  For this step, all we need to give the library is our
	// Consumer Key and Consumer Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);

	// Ask Tumblr for a Request Token.  Specify the Callback URL here too (although this should be optional)
	$request_token = $tum_oauth->getRequestToken($callback_url);

	// Store the request token and Request Token Secret as out callback.php script will need this
	$_SESSION['request_token'] = $token = $request_token['oauth_token'];
	$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

	// Check the HTTP Code.  It should be a 200 (OK), if it's anything else then something didn't work.
	switch ($tum_oauth->http_code) {
		case 200:
			// Ask Tumblr to give us a special address to their login page
			$url = $tum_oauth->getAuthorizeURL($token);

			// Redirect the user to the login URL given to us by Tumblr
			header('Location: ' . $url);

			// That's it for our side.  The user is sent to a Tumblr Login page and
			// asked to authroize our app.  After that, Tumblr sends the user back to
			// our Callback URL (callback.php) along with some information we need to get
			// an access token.

			break;
		default:
			// Give an error message
			$o = 'Could not connect to Tumblr. Refresh the page or try again later.';
	}
	return($o);
}

function tumblr_callback($a) {

	// Start a session, load the library
	session_start();
	//require_once('addon/tumblr/tumblroauth/tumblroauth.php');

	// Define the needed keys
	$consumer_key = get_config('tumblr','consumer_key');
	$consumer_secret = get_config('tumblr','consumer_secret');

	// Once the user approves your app at Tumblr, they are sent back to this script.
	// This script is passed two parameters in the URL, oauth_token (our Request Token)
	// and oauth_verifier (Key that we need to get Access Token).
	// We'll also need out Request Token Secret, which we stored in a session.

	// Create instance of TumblrOAuth.
	// It'll need our Consumer Key and Secret as well as our Request Token and Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);

	// Ok, let's get an Access Token. We'll need to pass along our oauth_verifier which was given to us in the URL.
	$access_token = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier']);

	// We're done with the Request Token and Secret so let's remove those.
	unset($_SESSION['request_token']);
	unset($_SESSION['request_token_secret']);

	// Make sure nothing went wrong.
	if (200 == $tum_oauth->http_code) {
		// good to go
	} else {
		return('Unable to authenticate');
	}

	// What's next?  Now that we have an Access Token and Secret, we can make an API call.
	set_pconfig(local_user(), "tumblr", "oauth_token", $access_token['oauth_token']);
	set_pconfig(local_user(), "tumblr", "oauth_token_secret", $access_token['oauth_token_secret']);

	$o = t("You are now authenticated to tumblr.");
	$o .= '<br /><a href="'.$a->get_baseurl().'/settings/connectors">'.t("return to the connector page").'</a>';
	return($o);
}

function tumblr_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$tmbl_post = get_pconfig(local_user(),'tumblr','post');
	if(intval($tmbl_post) == 1) {
		$tmbl_defpost = get_pconfig(local_user(),'tumblr','post_by_default');
		$selected = ((intval($tmbl_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="tumblr_enable"' . $selected . ' value="1" /> '
			. t('Post to Tumblr') . '</div>';
	}
}


function tumblr_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/tumblr/tumblr.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = get_pconfig(local_user(),'tumblr','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'tumblr','post_by_default');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_tumblr_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_tumblr_expanded\'); openClose(\'settings_tumblr_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/tumblr.png" /><h3 class="connector">'. t('Tumblr Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_tumblr_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_tumblr_expanded\'); openClose(\'settings_tumblr_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/tumblr.png" /><h3 class="connector">'. t('Tumblr Export').'</h3>';
	$s .= '</span>';

	$s .= '<div id="tumblr-username-wrapper">';
	$s .= '<a href="'.$a->get_baseurl().'/tumblr/connect">'.t("(Re-)Authenticate your tumblr page").'</a>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="tumblr-enable-wrapper">';
	$s .= '<label id="tumblr-enable-label" for="tumblr-checkbox">' . t('Enable Tumblr Post Plugin') . '</label>';
	$s .= '<input id="tumblr-checkbox" type="checkbox" name="tumblr" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="tumblr-bydefault-wrapper">';
	$s .= '<label id="tumblr-bydefault-label" for="tumblr-bydefault">' . t('Post to Tumblr by default') . '</label>';
	$s .= '<input id="tumblr-bydefault" type="checkbox" name="tumblr_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$oauth_token = get_pconfig(local_user(), "tumblr", "oauth_token");
	$oauth_token_secret = get_pconfig(local_user(), "tumblr", "oauth_token_secret");

	$s .= '<div id="tumblr-page-wrapper">';
	if (($oauth_token != "") && ($oauth_token_secret != "")) {

		$page = get_pconfig(local_user(),'tumblr','page');
		$consumer_key = get_config('tumblr','consumer_key');
		$consumer_secret = get_config('tumblr','consumer_secret');

		$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

		$userinfo = $tum_oauth->get('user/info');

		$blogs = array();

		$s .= '<label id="tumblr-page-label" for="tumblr-page">' . t('Post to page:') . '</label>';
		$s .= '<select name="tumblr_page" id="tumblr-page">';
		foreach($userinfo->response->user->blogs as $blog) {
			$blogurl = substr(str_replace(array("http://", "https://"), array("", ""), $blog->url), 0, -1);
			if ($page == $blogurl)
				$s .= "<option value='".$blogurl."' selected>".$blogurl."</option>";
			else
				$s .= "<option value='".$blogurl."'>".$blogurl."</option>";
		}

		$s .= "</select>";
	} else
		$s .= t("You are not authenticated to tumblr");
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="tumblr-submit" name="tumblr-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


function tumblr_settings_post(&$a,&$b) {

	if(x($_POST,'tumblr-submit')) {

		set_pconfig(local_user(),'tumblr','post',intval($_POST['tumblr']));
		set_pconfig(local_user(),'tumblr','page',$_POST['tumblr_page']);
		set_pconfig(local_user(),'tumblr','post_by_default',intval($_POST['tumblr_bydefault']));

	}

}

function tumblr_post_local(&$a, &$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$tmbl_post   = intval(get_pconfig(local_user(),'tumblr','post'));

	$tmbl_enable = (($tmbl_post && x($_REQUEST,'tumblr_enable')) ? intval($_REQUEST['tumblr_enable']) : 0);

	if ($b['api_source'] && intval(get_pconfig(local_user(),'tumblr','post_by_default'))) {
		$tmbl_enable = 1;
	}

	if (!$tmbl_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'tumblr';
}




function tumblr_send(&$a,&$b) {

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'tumblr'))
		return;

	if($b['parent'] != $b['id'])
		return;

	$oauth_token = get_pconfig($b['uid'], "tumblr", "oauth_token");
	$oauth_token_secret = get_pconfig($b['uid'], "tumblr", "oauth_token_secret");
	$page = get_pconfig($b['uid'], "tumblr", "page");
	$tmbl_blog = 'blog/'.$page.'/post';

	if($oauth_token && $oauth_token_secret && $tmbl_blog) {

		require_once('include/bbcode.php');

		$tag_arr = array();
		$tags = '';
		$x = preg_match_all('/\#\[(.*?)\](.*?)\[/',$b['tag'],$matches,PREG_SET_ORDER);

		if($x) {
			foreach($matches as $mtch) {
				$tag_arr[] = $mtch[2];
			}
		}
		if(count($tag_arr))
			$tags = implode(',',$tag_arr);

		$title = trim($b['title']);
		require_once('include/plaintext.php');

		$siteinfo = get_attached_data($b["body"]);

		$params = array(
			'state' => 'published',
			'tags' => $tags,
			'tweet' => 'off',
			'format' => 'html');

		if (!isset($siteinfo["type"]))
			$siteinfo["type"] = "";

		if (($title == "") && isset($siteinfo["title"]))
			$title = $siteinfo["title"];

		if (isset($siteinfo["text"]))
			$body = $siteinfo["text"];
		else
			$body = bb_remove_share_information($b["body"]);

		switch ($siteinfo["type"]) {
			case "photo":
				$params['type'] = "photo";
				$params['caption'] = bbcode($body, false, false, 4);

				if (isset($siteinfo["url"]))
					$params['link'] = $siteinfo["url"];

				$params['source'] = $siteinfo["image"];
				break;
			case "link":
				$params['type'] = "link";
				$params['title'] = $title;
				$params['url'] = $siteinfo["url"];
				$params['description'] = bbcode($body, false, false, 4);
				break;
			case "audio":
				$params['type'] = "audio";
				$params['external_url'] = $siteinfo["url"];
				$params['caption'] = bbcode($body, false, false, 4);
				break;
			case "video":
				$params['type'] = "video";
				$params['embed'] = $siteinfo["url"];
				$params['caption'] = bbcode($body, false, false, 4);
				break;
			default:
				$params['type'] = "text";
				$params['title'] = $title;
				$params['body'] = bbcode($b['body'], false, false, 4);
				break;
		}

		if (isset($params['caption']) && (trim($title) != ""))
			$params['caption'] = '<h1>'.$title."</h1>".
						"<p>".$params['caption']."</p>";

		if (trim($params['caption']) == "")
			$params['caption'] = bbcode("[quote]".$siteinfo["description"]."[/quote]", false, false, 4);

		$consumer_key = get_config('tumblr','consumer_key');
		$consumer_secret = get_config('tumblr','consumer_secret');

		$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

		// Make an API call with the TumblrOAuth instance.
		$x = $tum_oauth->post($tmbl_blog,$params);
		$ret_code = $tum_oauth->http_code;
		//print_r($params);
		if($ret_code == 201)
			logger('tumblr_send: success');
		elseif($ret_code == 403)
			logger('tumblr_send: authentication failure');
		else
			logger('tumblr_send: general error: ' . print_r($x,true));

	}
}

