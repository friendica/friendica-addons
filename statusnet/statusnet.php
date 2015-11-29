<?php
/**
 * Name: GNU Social Connector
 * Description: Bidirectional (posting, relaying and reading) connector for GNU Social.
 * Version: 1.0.5
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 *
 * Copyright (c) 2011-2013 Tobias Diekershoff, Michael Vogel
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above
 *    * copyright notice, this list of conditions and the following disclaimer in
 *      the documentation and/or other materials provided with the distribution.
 *    * Neither the name of the <organization> nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */


/***
 * We have to alter the TwitterOAuth class a little bit to work with any GNU Social
 * installation abroad. Basically it's only make the API path variable and be happy.
 *
 * Thank you guys for the Twitter compatible API!
 */

define('STATUSNET_DEFAULT_POLL_INTERVAL', 5); // given in minutes

require_once('library/twitteroauth.php');

class StatusNetOAuth extends TwitterOAuth {
    function get_maxlength() {
	$config = $this->get($this->host . 'statusnet/config.json');
	return $config->site->textlimit;
    }
    function accessTokenURL()  { return $this->host.'oauth/access_token'; }
    function authenticateURL() { return $this->host.'oauth/authenticate'; }
    function authorizeURL() { return $this->host.'oauth/authorize'; }
    function requestTokenURL() { return $this->host.'oauth/request_token'; }
    function __construct($apipath, $consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
	parent::__construct($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
	$this->host = $apipath;
    }
  /**
   * Make an HTTP request
   *
   * @return API results
   *
   * Copied here from the twitteroauth library and complemented by applying the proxy settings of friendica
   */
  function http($url, $method, $postfields = NULL) {
    $this->http_info = array();
    $ci = curl_init();
    /* Curl settings */
    $prx = get_config('system','proxy');
    if(strlen($prx)) {
	curl_setopt($ci, CURLOPT_HTTPPROXYTUNNEL, 1);
	curl_setopt($ci, CURLOPT_PROXY, $prx);
	$prxusr = get_config('system','proxyuser');
	if(strlen($prxusr))
	    curl_setopt($ci, CURLOPT_PROXYUSERPWD, $prxusr);
    }
    curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
    curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
    curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
    curl_setopt($ci, CURLOPT_HEADER, FALSE);

    switch ($method) {
      case 'POST':
	curl_setopt($ci, CURLOPT_POST, TRUE);
	if (!empty($postfields)) {
	  curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
	}
	break;
      case 'DELETE':
	curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
	if (!empty($postfields)) {
	  $url = "{$url}?{$postfields}";
	}
    }

    curl_setopt($ci, CURLOPT_URL, $url);
    $response = curl_exec($ci);
    $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
    $this->url = $url;
    curl_close ($ci);
    return $response;
  }
}

function statusnet_install() {
	//  we need some hooks, for the configuration and for sending tweets
	register_hook('connector_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	register_hook('connector_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
	register_hook('notifier_normal', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	register_hook('post_local', 'addon/statusnet/statusnet.php', 'statusnet_post_local');
	register_hook('jot_networks',    'addon/statusnet/statusnet.php', 'statusnet_jot_nets');
	register_hook('cron', 'addon/statusnet/statusnet.php', 'statusnet_cron');
	register_hook('prepare_body', 'addon/statusnet/statusnet.php', 'statusnet_prepare_body');
	logger("installed GNU Social");
}


function statusnet_uninstall() {
	unregister_hook('connector_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	unregister_hook('connector_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');
	unregister_hook('notifier_normal', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	unregister_hook('post_local', 'addon/statusnet/statusnet.php', 'statusnet_post_local');
	unregister_hook('jot_networks',    'addon/statusnet/statusnet.php', 'statusnet_jot_nets');
	unregister_hook('cron', 'addon/statusnet/statusnet.php', 'statusnet_cron');
	unregister_hook('prepare_body', 'addon/statusnet/statusnet.php', 'statusnet_prepare_body');

	// old setting - remove only
	unregister_hook('post_local_end', 'addon/statusnet/statusnet.php', 'statusnet_post_hook');
	unregister_hook('plugin_settings', 'addon/statusnet/statusnet.php', 'statusnet_settings');
	unregister_hook('plugin_settings_post', 'addon/statusnet/statusnet.php', 'statusnet_settings_post');

}

function statusnet_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$statusnet_post = get_pconfig(local_user(),'statusnet','post');
	if(intval($statusnet_post) == 1) {
		$statusnet_defpost = get_pconfig(local_user(),'statusnet','post_by_default');
		$selected = ((intval($statusnet_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="statusnet_enable"' . $selected . ' value="1" /> ' 
			. t('Post to GNU Social') . '</div>';
	}
}

function statusnet_settings_post ($a,$post) {
	if(! local_user())
		return;
	// don't check GNU Social settings if GNU Social submit button is not clicked
	if (!x($_POST,'statusnet-submit'))
		return;

	if (isset($_POST['statusnet-disconnect'])) {
		/***
		 * if the GNU Social-disconnect checkbox is set, clear the GNU Social configuration
		 */
		del_pconfig(local_user(), 'statusnet', 'consumerkey');
		del_pconfig(local_user(), 'statusnet', 'consumersecret');
		del_pconfig(local_user(), 'statusnet', 'post');
		del_pconfig(local_user(), 'statusnet', 'post_by_default');
		del_pconfig(local_user(), 'statusnet', 'oauthtoken');
		del_pconfig(local_user(), 'statusnet', 'oauthsecret');
		del_pconfig(local_user(), 'statusnet', 'baseapi');
		del_pconfig(local_user(), 'statusnet', 'lastid');
		del_pconfig(local_user(), 'statusnet', 'mirror_posts');
		del_pconfig(local_user(), 'statusnet', 'import');
		del_pconfig(local_user(), 'statusnet', 'create_user');
		del_pconfig(local_user(), 'statusnet', 'own_id');
	} else {
	if (isset($_POST['statusnet-preconf-apiurl'])) {
		/***
		 * If the user used one of the preconfigured GNU Social server credentials
		 * use them. All the data are available in the global config.
		 * Check the API Url never the less and blame the admin if it's not working ^^
		 */
		$globalsn = get_config('statusnet', 'sites');
		foreach ( $globalsn as $asn) {
			if ($asn['apiurl'] == $_POST['statusnet-preconf-apiurl'] ) {
				$apibase = $asn['apiurl'];
				$c = fetch_url( $apibase . 'statusnet/version.xml' );
				if (strlen($c) > 0) {
					set_pconfig(local_user(), 'statusnet', 'consumerkey', $asn['consumerkey'] );
					set_pconfig(local_user(), 'statusnet', 'consumersecret', $asn['consumersecret'] );
					set_pconfig(local_user(), 'statusnet', 'baseapi', $asn['apiurl'] );
					//set_pconfig(local_user(), 'statusnet', 'application_name', $asn['applicationname'] );
				} else {
					notice( t('Please contact your site administrator.<br />The provided API URL is not valid.').EOL.$asn['apiurl'].EOL );
				}
			}
		}
		goaway($a->get_baseurl().'/settings/connectors');
	} else {
	if (isset($_POST['statusnet-consumersecret'])) {
		//  check if we can reach the API of the GNU Social server
		//  we'll check the API Version for that, if we don't get one we'll try to fix the path but will
		//  resign quickly after this one try to fix the path ;-)
		$apibase = $_POST['statusnet-baseapi'];
		$c = fetch_url( $apibase . 'statusnet/version.xml' );
		if (strlen($c) > 0) {
			//  ok the API path is correct, let's save the settings
			set_pconfig(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
			set_pconfig(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
			set_pconfig(local_user(), 'statusnet', 'baseapi', $apibase );
			//set_pconfig(local_user(), 'statusnet', 'application_name', $_POST['statusnet-applicationname'] );
		} else {
			//  the API path is not correct, maybe missing trailing / ?
			$apibase = $apibase . '/';
			$c = fetch_url( $apibase . 'statusnet/version.xml' );
			if (strlen($c) > 0) {
				//  ok the API path is now correct, let's save the settings
				set_pconfig(local_user(), 'statusnet', 'consumerkey', $_POST['statusnet-consumerkey']);
				set_pconfig(local_user(), 'statusnet', 'consumersecret', $_POST['statusnet-consumersecret']);
				set_pconfig(local_user(), 'statusnet', 'baseapi', $apibase );
			} else {
				//  still not the correct API base, let's do noting
				notice( t('We could not contact the GNU Social API with the Path you entered.').EOL );
			}
		}
		goaway($a->get_baseurl().'/settings/connectors');
	} else {
	if (isset($_POST['statusnet-pin'])) {
		//  if the user supplied us with a PIN from GNU Social, let the magic of OAuth happen
		$api     = get_pconfig(local_user(), 'statusnet', 'baseapi');
		$ckey    = get_pconfig(local_user(), 'statusnet', 'consumerkey'  );
		$csecret = get_pconfig(local_user(), 'statusnet', 'consumersecret' );
		//  the token and secret for which the PIN was generated were hidden in the settings
		//  form as token and token2, we need a new connection to GNU Social using these token
		//  and secret to request a Access Token with the PIN
		$connection = new StatusNetOAuth($api, $ckey, $csecret, $_POST['statusnet-token'], $_POST['statusnet-token2']);
		$token   = $connection->getAccessToken( $_POST['statusnet-pin'] );
		//  ok, now that we have the Access Token, save them in the user config
		set_pconfig(local_user(),'statusnet', 'oauthtoken',  $token['oauth_token']);
		set_pconfig(local_user(),'statusnet', 'oauthsecret', $token['oauth_token_secret']);
		set_pconfig(local_user(),'statusnet', 'post', 1);
		set_pconfig(local_user(),'statusnet', 'post_taglinks', 1);
		//  reload the Addon Settings page, if we don't do it see Bug #42
		goaway($a->get_baseurl().'/settings/connectors');
	} else {
		//  if no PIN is supplied in the POST variables, the user has changed the setting
		//  to post a dent for every new __public__ posting to the wall
		set_pconfig(local_user(),'statusnet','post',intval($_POST['statusnet-enable']));
		set_pconfig(local_user(),'statusnet','post_by_default',intval($_POST['statusnet-default']));
		set_pconfig(local_user(), 'statusnet', 'mirror_posts', intval($_POST['statusnet-mirror']));
		set_pconfig(local_user(), 'statusnet', 'import', intval($_POST['statusnet-import']));
		set_pconfig(local_user(), 'statusnet', 'create_user', intval($_POST['statusnet-create_user']));

		if (!intval($_POST['statusnet-mirror']))
			del_pconfig(local_user(),'statusnet','lastid');

		info( t('GNU Social settings updated.') . EOL);
	}}}}
}
function statusnet_settings(&$a,&$s) {
	if(! local_user())
		return;
	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/statusnet/statusnet.css' . '" media="all" />' . "\r\n";
	/***
	 * 1) Check that we have a base api url and a consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 *    allow the user to cancel the connection process at this step
	 * 3) Checkbox for "Send public notices (respect size limitation)
	 */
	$api     = get_pconfig(local_user(), 'statusnet', 'baseapi');
	$ckey    = get_pconfig(local_user(), 'statusnet', 'consumerkey');
	$csecret = get_pconfig(local_user(), 'statusnet', 'consumersecret');
	$otoken  = get_pconfig(local_user(), 'statusnet', 'oauthtoken');
	$osecret = get_pconfig(local_user(), 'statusnet', 'oauthsecret');
	$enabled = get_pconfig(local_user(), 'statusnet', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$defenabled = get_pconfig(local_user(),'statusnet','post_by_default');
	$defchecked = (($defenabled) ? ' checked="checked" ' : '');
	$mirrorenabled = get_pconfig(local_user(),'statusnet','mirror_posts');
	$mirrorchecked = (($mirrorenabled) ? ' checked="checked" ' : '');
	$import = get_pconfig(local_user(),'statusnet','import');
	$importselected = array("", "", "");
	$importselected[$import] = ' selected="selected"';
	//$importenabled = get_pconfig(local_user(),'statusnet','import');
	//$importchecked = (($importenabled) ? ' checked="checked" ' : '');
	$create_userenabled = get_pconfig(local_user(),'statusnet','create_user');
	$create_userchecked = (($create_userenabled) ? ' checked="checked" ' : '');

	$css = (($enabled) ? '' : '-disabled');

	$s .= '<span id="settings_statusnet_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_statusnet_expanded\'); openClose(\'settings_statusnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/gnusocial.png" /><h3 class="connector">'. t('GNU Social Import/Export/Mirror').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_statusnet_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_statusnet_expanded\'); openClose(\'settings_statusnet_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/gnusocial.png" /><h3 class="connector">'. t('GNU Social Import/Export/Mirror').'</h3>';
	$s .= '</span>';

	if ( (!$ckey) && (!$csecret) ) {
		/***
		 * no consumer keys
		 */
		$globalsn = get_config('statusnet', 'sites');
		/***
		 * lets check if we have one or more globally configured GNU Social
		 * server OAuth credentials in the configuration. If so offer them
		 * with a little explanation to the user as choice - otherwise
		 * ignore this option entirely.
		 */
		if (! $globalsn == null) {
			$s .= '<h4>' . t('Globally Available GNU Social OAuthKeys') . '</h4>';
			$s .= '<p>'. t("There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance \x28see below\x29.") .'</p>';
			$s .= '<div id="statusnet-preconf-wrapper">';
			foreach ($globalsn as $asn) {
				$s .= '<input type="radio" name="statusnet-preconf-apiurl" value="'. $asn['apiurl'] .'">'. $asn['sitename'] .'<br />';
			}
			$s .= '<p></p><div class="clear"></div></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
		}
		$s .= '<h4>' . t('Provide your own OAuth Credentials') . '</h4>';
		$s .= '<p>'. t('No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation.') .'</p>';
		$s .= '<div id="statusnet-consumer-wrapper">';
		$s .= '<label id="statusnet-consumerkey-label" for="statusnet-consumerkey">'. t('OAuth Consumer Key') .'</label>';
		$s .= '<input id="statusnet-consumerkey" type="text" name="statusnet-consumerkey" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		$s .= '<label id="statusnet-consumersecret-label" for="statusnet-consumersecret">'. t('OAuth Consumer Secret') .'</label>';
		$s .= '<input id="statusnet-consumersecret" type="text" name="statusnet-consumersecret" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		$s .= '<label id="statusnet-baseapi-label" for="statusnet-baseapi">'. t("Base API Path \x28remember the trailing /\x29") .'</label>';
		$s .= '<input id="statusnet-baseapi" type="text" name="statusnet-baseapi" size="35" /><br />';
		$s .= '<div class="clear"></div>';
		//$s .= '<label id="statusnet-applicationname-label" for="statusnet-applicationname">'.t('GNU Socialapplication name').'</label>';
		//$s .= '<input id="statusnet-applicationname" type="text" name="statusnet-applicationname" size="35" /><br />';
		$s .= '<p></p><div class="clear"></div>';
		$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
		$s .= '</div>';
	} else {
		/***
		 * ok we have a consumer key pair now look into the OAuth stuff
		 */
		if ( (!$otoken) && (!$osecret) ) {
			/***
			 * the user has not yet connected the account to GNU Social
			 * get a temporary OAuth key/secret pair and display a button with
			 * which the user can request a PIN to connect the account to a
			 * account at GNU Social
			 */
			$connection = new StatusNetOAuth($api, $ckey, $csecret);
			$request_token = $connection->getRequestToken('oob');
			$token = $request_token['oauth_token'];
			/***
			 *  make some nice form
			 */
			$s .= '<p>'. t('To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.') .'</p>';
			$s .= '<a href="'.$connection->getAuthorizeURL($token,False).'" target="_statusnet"><img src="addon/statusnet/signinwithstatusnet.png" alt="'. t('Log in with GNU Social') .'"></a>';
			$s .= '<div id="statusnet-pin-wrapper">';
			$s .= '<label id="statusnet-pin-label" for="statusnet-pin">'. t('Copy the security code from GNU Social here') .'</label>';
			$s .= '<input id="statusnet-pin" type="text" name="statusnet-pin" />';
			$s .= '<input id="statusnet-token" type="hidden" name="statusnet-token" value="'.$token.'" />';
			$s .= '<input id="statusnet-token2" type="hidden" name="statusnet-token2" value="'.$request_token['oauth_token_secret'].'" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
			$s .= '<h4>'.t('Cancel Connection Process').'</h4>';
			$s .= '<div id="statusnet-cancel-wrapper">';
			$s .= '<p>'.t('Current GNU Social API is').': '.$api.'</p>';
			$s .= '<label id="statusnet-cancel-label" for="statusnet-cancel">'. t('Cancel GNU Social Connection') . '</label>';
			$s .= '<input id="statusnet-cancel" type="checkbox" name="statusnet-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
		} else {
			/***
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to GNU Social
			 */
			$connection = new StatusNetOAuth($api,$ckey,$csecret,$otoken,$osecret);
			$details = $connection->get('account/verify_credentials');
			$s .= '<div id="statusnet-info" ><img id="statusnet-avatar" src="'.$details->profile_image_url.'" /><p id="statusnet-info-block">'. t('Currently connected to: ') .'<a href="'.$details->statusnet_profile_url.'" target="_statusnet">'.$details->screen_name.'</a><br /><em>'.$details->description.'</em></p></div>';
			$s .= '<p>'. t('If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.') .'</p>';
			if ($a->user['hidewall']) {
			    $s .= '<p>'. t('<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') .'</p>';
			}
			$s .= '<div id="statusnet-enable-wrapper">';
			$s .= '<label id="statusnet-enable-label" for="statusnet-checkbox">'. t('Allow posting to GNU Social') .'</label>';
			$s .= '<input id="statusnet-checkbox" type="checkbox" name="statusnet-enable" value="1" ' . $checked . '/>';
			$s .= '<div class="clear"></div>';
			$s .= '<label id="statusnet-default-label" for="statusnet-default">'. t('Send public postings to GNU Social by default') .'</label>';
			$s .= '<input id="statusnet-default" type="checkbox" name="statusnet-default" value="1" ' . $defchecked . '/>';
			$s .= '<div class="clear"></div>';

			$s .= '<label id="statusnet-mirror-label" for="statusnet-mirror">'.t('Mirror all posts from GNU Social that are no replies or repeated messages').'</label>';
			$s .= '<input id="statusnet-mirror" type="checkbox" name="statusnet-mirror" value="1" '. $mirrorchecked . '/>';

			$s .= '<div class="clear"></div>';
			$s .= '</div>';

			$s .= '<label id="statusnet-import-label" for="statusnet-import">'.t('Import the remote timeline').'</label>';
			//$s .= '<input id="statusnet-import" type="checkbox" name="statusnet-import" value="1" '. $importchecked . '/>';

			$s .= '<select name="statusnet-import" id="statusnet-import" />';
			$s .= '<option value="0" '.$importselected[0].'>'.t("Disabled").'</option>';
			$s .= '<option value="1" '.$importselected[1].'>'.t("Full Timeline").'</option>';
			$s .= '<option value="2" '.$importselected[2].'>'.t("Only Mentions").'</option>';
			$s .= '</select>';
			$s .= '<div class="clear"></div>';
/*
			$s .= '<label id="statusnet-create_user-label" for="statusnet-create_user">'.t('Automatically create contacts').'</label>';
			$s .= '<input id="statusnet-create_user" type="checkbox" name="statusnet-create_user" value="1" '. $create_userchecked . '/>';
			$s .= '<div class="clear"></div>';
*/
			$s .= '<div id="statusnet-disconnect-wrapper">';
			$s .= '<label id="statusnet-disconnect-label" for="statusnet-disconnect">'. t('Clear OAuth configuration') .'</label>';
			$s .= '<input id="statusnet-disconnect" type="checkbox" name="statusnet-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="statusnet-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>'; 
		}
	}
	$s .= '</div><div class="clear"></div>';
}


function statusnet_post_local(&$a,&$b) {
	if($b['edit'])
		return;

	if((local_user()) && (local_user() == $b['uid']) && (! $b['private'])) {

		$statusnet_post = get_pconfig(local_user(),'statusnet','post');
		$statusnet_enable = (($statusnet_post && x($_REQUEST,'statusnet_enable')) ? intval($_REQUEST['statusnet_enable']) : 0);

		// if API is used, default to the chosen settings
		if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'statusnet','post_by_default')))
			$statusnet_enable = 1;

		if(! $statusnet_enable)
			return;

		if(strlen($b['postopts']))
			$b['postopts'] .= ',';
		$b['postopts'] .= 'statusnet';
	}
}

function statusnet_action($a, $uid, $pid, $action) {
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');

	$connection = new StatusNetOAuth($api,$ckey,$csecret,$otoken,$osecret);

	logger("statusnet_action '".$action."' ID: ".$pid, LOGGER_DATA);

	switch ($action) {
		case "delete":
			$result = $connection->post("statuses/destroy/".$pid);
			break;
		case "like":
			$result = $connection->post("favorites/create/".$pid);
			break;
		case "unlike":
			$result = $connection->post("favorites/destroy/".$pid);
			break;
	}
	logger("statusnet_action '".$action."' send, result: " . print_r($result, true), LOGGER_DEBUG);
}

function statusnet_post_hook(&$a,&$b) {

	/**
	 * Post to GNU Social
	 */

	if (!get_pconfig($b["uid"],'statusnet','import')) {
		if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	$api = get_pconfig($b["uid"], 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	if($b['parent'] != $b['id']) {
		logger("statusnet_post_hook: parameter ".print_r($b, true), LOGGER_DATA);

		// Looking if its a reply to a GNU Social post
		$hostlength = strlen($hostname) + 2;
		if ((substr($b["parent-uri"], 0, $hostlength) != $hostname."::") AND (substr($b["extid"], 0, $hostlength) != $hostname."::")
			AND (substr($b["thr-parent"], 0, $hostlength) != $hostname."::")) {
			logger("statusnet_post_hook: no GNU Social post ".$b["parent"]);
			return;
		}

		$r = q("SELECT `item`.`author-link`, `item`.`uri`, `contact`.`nick` AS contact_nick
			FROM `item` INNER JOIN `contact` ON `contact`.`id` = `item`.`contact-id`
			WHERE `item`.`uri` = '%s' AND `item`.`uid` = %d LIMIT 1",
			dbesc($b["thr-parent"]),
			intval($b["uid"]));

		if(!count($r)) {
			logger("statusnet_post_hook: no parent found ".$b["thr-parent"]);
			return;
		} else {
			$iscomment = true;
			$orig_post = $r[0];
		}

		//$nickname = "@[url=".$orig_post["author-link"]."]".$orig_post["contact_nick"]."[/url]";
		//$nicknameplain = "@".$orig_post["contact_nick"];

		$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post["author-link"]);

		$nickname = "@[url=".$orig_post["author-link"]."]".$nick."[/url]";
		$nicknameplain = "@".$nick;

		logger("statusnet_post_hook: comparing ".$nickname." and ".$nicknameplain." with ".$b["body"], LOGGER_DEBUG);
		if ((strpos($b["body"], $nickname) === false) AND (strpos($b["body"], $nicknameplain) === false))
			$b["body"] = $nickname." ".$b["body"];

		logger("statusnet_post_hook: parent found ".print_r($orig_post, true), LOGGER_DEBUG);
	} else {
		$iscomment = false;

		if($b['private'] OR !strstr($b['postopts'],'statusnet'))
			return;
	}

	if (($b['verb'] == ACTIVITY_POST) AND $b['deleted'])
		statusnet_action($a, $b["uid"], substr($orig_post["uri"], $hostlength), "delete");

	if($b['verb'] == ACTIVITY_LIKE) {
		logger("statusnet_post_hook: parameter 2 ".substr($b["thr-parent"], $hostlength), LOGGER_DEBUG);
		if ($b['deleted'])
			statusnet_action($a, $b["uid"], substr($b["thr-parent"], $hostlength), "unlike");
		else
			statusnet_action($a, $b["uid"], substr($b["thr-parent"], $hostlength), "like");
		return;
	}

	if($b['deleted'] || ($b['created'] !== $b['edited']))
		return;

	// if posts comes from GNU Social don't send it back
	if($b['extid'] == NETWORK_STATUSNET)
		return;

	if($b['app'] == "StatusNet")
		return;

	logger('GNU Socialpost invoked');

	load_pconfig($b['uid'], 'statusnet');

	$api     = get_pconfig($b['uid'], 'statusnet', 'baseapi');
	$ckey    = get_pconfig($b['uid'], 'statusnet', 'consumerkey');
	$csecret = get_pconfig($b['uid'], 'statusnet', 'consumersecret');
	$otoken  = get_pconfig($b['uid'], 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($b['uid'], 'statusnet', 'oauthsecret');

	if($ckey && $csecret && $otoken && $osecret) {

		// If it's a repeated message from GNU Social then do a native retweet and exit
		if (statusnet_is_retweet($a, $b['uid'], $b['body']))
			return;

		require_once('include/bbcode.php');
		$dent = new StatusNetOAuth($api,$ckey,$csecret,$otoken,$osecret);
		$max_char = $dent->get_maxlength(); // max. length for a dent

		set_pconfig($b['uid'], 'statusnet', 'max_char', $max_char);

		$tempfile = "";
		require_once("include/plaintext.php");
		require_once("include/network.php");
		$msgarr = plaintext($a, $b, $max_char, true, 7);
		$msg = $msgarr["text"];

		if (($msg == "") AND isset($msgarr["title"]))
			$msg = shortenmsg($msgarr["title"], $max_char - 50);

		$image = "";

		if (isset($msgarr["url"])) {
			if ((strlen($msgarr["url"]) > 20) AND
				((strlen($msg." \n".$msgarr["url"]) > $max_char)))
				$msg .= " \n".short_link($msgarr["url"]);
			else
				$msg .= " \n".$msgarr["url"];
		} elseif (isset($msgarr["image"]) AND ($msgarr["type"] != "video"))
			$image = $msgarr["image"];

		if ($image != "") {
			$img_str = fetch_url($image);
			$tempfile = tempnam(get_temppath(), "cache");
			file_put_contents($tempfile, $img_str);
			$postdata = array("status" => $msg, "media[]" => $tempfile);
		} else
			$postdata = array("status"=>$msg);

		// and now dent it :-)
		if(strlen($msg)) {

			if ($iscomment) {
				$postdata["in_reply_to_status_id"] = substr($orig_post["uri"], $hostlength);
				logger('statusnet_post send reply '.print_r($postdata, true), LOGGER_DEBUG);
			}

			// New code that is able to post pictures
			require_once("addon/statusnet/codebird.php");
			$cb = \CodebirdSN\CodebirdSN::getInstance();
			$cb->setAPIEndpoint($api);
			$cb->setConsumerKey($ckey, $csecret);
			$cb->setToken($otoken, $osecret);
			$result = $cb->statuses_update($postdata);
			//$result = $dent->post('statuses/update', $postdata);
			logger('statusnet_post send, result: ' . print_r($result, true).
				"\nmessage: ".$msg, LOGGER_DEBUG."\nOriginal post: ".print_r($b, true)."\nPost Data: ".print_r($postdata, true));

			if ($result->source)
				set_pconfig($b["uid"], "statusnet", "application_name", strip_tags($result->source));

			if ($result->error) {
				logger('Send to GNU Social failed: "'.$result->error.'"');
			} elseif ($iscomment) {
				logger('statusnet_post: Update extid '.$result->id." for post id ".$b['id']);
				q("UPDATE `item` SET `extid` = '%s', `body` = '%s' WHERE `id` = %d",
					dbesc($hostname."::".$result->id),
					dbesc($result->text),
					intval($b['id'])
				);
			}
		}
		if ($tempfile != "")
			unlink($tempfile);
	}
}

function statusnet_plugin_admin_post(&$a){

	$sites = array();

	foreach($_POST['sitename'] as $id=>$sitename){
		$sitename=trim($sitename);
		$apiurl=trim($_POST['apiurl'][$id]);
		if (! (substr($apiurl, -1)=='/'))
		    $apiurl=$apiurl.'/';
		$secret=trim($_POST['secret'][$id]);
		$key=trim($_POST['key'][$id]);
		//$applicationname = ((x($_POST, 'applicationname')) ? notags(trim($_POST['applicationname'][$id])):'');
		if ($sitename!="" &&
			$apiurl!="" &&
			$secret!="" &&
			$key!="" &&
			!x($_POST['delete'][$id])){

				$sites[] = Array(
					'sitename' => $sitename,
					'apiurl' => $apiurl,
					'consumersecret' => $secret,
					'consumerkey' => $key,
					//'applicationname' => $applicationname
				);
		}
	}

	$sites = set_config('statusnet','sites', $sites);

}

function statusnet_plugin_admin(&$a, &$o){

	$sites = get_config('statusnet','sites');
	$sitesform=array();
	if (is_array($sites)){
		foreach($sites as $id=>$s){
			$sitesform[] = Array(
				'sitename' => Array("sitename[$id]", "Site name", $s['sitename'], ""),
				'apiurl' => Array("apiurl[$id]", "Api url", $s['apiurl'], t("Base API Path \x28remember the trailing /\x29") ),
				'secret' => Array("secret[$id]", "Secret", $s['consumersecret'], ""),
				'key' => Array("key[$id]", "Key", $s['consumerkey'], ""),
				//'applicationname' => Array("applicationname[$id]", "Application name", $s['applicationname'], ""),
				'delete' => Array("delete[$id]", "Delete", False , "Check to delete this preset"),
			);
		}
	}
	/* empty form to add new site */
	$id++;
	$sitesform[] = Array(
		'sitename' => Array("sitename[$id]", t("Site name"), "", ""),
		'apiurl' => Array("apiurl[$id]", "Api url", "", t("Base API Path \x28remember the trailing /\x29") ),
		'secret' => Array("secret[$id]", t("Consumer Secret"), "", ""),
		'key' => Array("key[$id]", t("Consumer Key"), "", ""),
		//'applicationname' => Array("applicationname[$id]", t("Application name"), "", ""),
	);

	$t = get_markup_template( "admin.tpl", "addon/statusnet/" );
	$o = replace_macros($t, array(
		'$submit' => t('Save Settings'),
		'$sites' => $sitesform,
	));
}

function statusnet_prepare_body(&$a,&$b) {
        if ($b["item"]["network"] != NETWORK_STATUSNET)
                return;

        if ($b["preview"]) {
		$max_char = get_pconfig(local_user(),'statusnet','max_char');
		if (intval($max_char) == 0)
			$max_char = 140;

                require_once("include/plaintext.php");
                $item = $b["item"];
                $item["plink"] = $a->get_baseurl()."/display/".$a->user["nickname"]."/".$item["parent"];

		$r = q("SELECT `item`.`author-link`, `item`.`uri`, `contact`.`nick` AS contact_nick
                        FROM `item` INNER JOIN `contact` ON `contact`.`id` = `item`.`contact-id`
                        WHERE `item`.`uri` = '%s' AND `item`.`uid` = %d LIMIT 1",
                        dbesc($item["thr-parent"]),
                        intval(local_user()));

                if(count($r)) {
                        $orig_post = $r[0];
			//$nickname = "@[url=".$orig_post["author-link"]."]".$orig_post["contact_nick"]."[/url]";
			//$nicknameplain = "@".$orig_post["contact_nick"];

			$nick = preg_replace("=https?://(.*)/(.*)=ism", "$2", $orig_post["author-link"]);

			$nickname = "@[url=".$orig_post["author-link"]."]".$nick."[/url]";
			$nicknameplain = "@".$nick;

	                if ((strpos($item["body"], $nickname) === false) AND (strpos($item["body"], $nicknameplain) === false))
	                        $item["body"] = $nickname." ".$item["body"];
                }


                $msgarr = plaintext($a, $item, $max_char, true, 7);
                $msg = $msgarr["text"];

                if (isset($msgarr["url"]))
                        $msg .= " ".$msgarr["url"];

                if (isset($msgarr["image"]))
                        $msg .= " ".$msgarr["image"];

                $b['html'] = nl2br(htmlspecialchars($msg));
        }
}

function statusnet_cron($a,$b) {
	$last = get_config('statusnet','last_poll');

	$poll_interval = intval(get_config('statusnet','poll_interval'));
	if(! $poll_interval)
		$poll_interval = STATUSNET_DEFAULT_POLL_INTERVAL;

	if($last) {
		$next = $last + ($poll_interval * 60);
		if($next > time()) {
			logger('statusnet: poll intervall not reached');
			return;
		}
	}
	logger('statusnet: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'statusnet' AND `k` = 'mirror_posts' AND `v` = '1' ORDER BY RAND() ");
	if(count($r)) {
		foreach($r as $rr) {
			logger('statusnet: fetching for user '.$rr['uid']);
			statusnet_fetchtimeline($a, $rr['uid']);
		}
	}

	$abandon_days = intval(get_config('system','account_abandon_days'));
	if ($abandon_days < 1)
		$abandon_days = 0;

	$abandon_limit = date("Y-m-d H:i:s", time() - $abandon_days * 86400);

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'statusnet' AND `k` = 'import' AND `v` ORDER BY RAND()");
	if(count($r)) {
		foreach($r as $rr) {
			if ($abandon_days != 0) {
				$user = q("SELECT `login_date` FROM `user` WHERE uid=%d AND `login_date` >= '%s'", $rr['uid'], $abandon_limit);
				if (!count($user)) {
					logger('abandoned account: timeline from user '.$rr['uid'].' will not be imported');
					continue;
				}
			}

			logger('statusnet: importing timeline from user '.$rr['uid']);
			statusnet_fetchhometimeline($a, $rr["uid"], $rr["v"]);
		}
	}

	logger('statusnet: cron_end');

	set_config('statusnet','last_poll', time());
}

function statusnet_fetchtimeline($a, $uid) {
	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');
	$lastid  = get_pconfig($uid, 'statusnet', 'lastid');

	require_once('mod/item.php');
	require_once('include/items.php');

	//  get the application name for the SN app
	//  1st try personal config, then system config and fallback to the
	//  hostname of the node if neither one is set.
	$application_name  = get_pconfig( $uid, 'statusnet', 'application_name');
	if ($application_name == "")
		$application_name  = get_config('statusnet', 'application_name');
	if ($application_name == "")
		$application_name = $a->get_hostname();

	$connection = new StatusNetOAuth($api, $ckey,$csecret,$otoken,$osecret);

	$parameters = array("exclude_replies" => true, "trim_user" => true, "contributor_details" => false, "include_rts" => false);

	$first_time = ($lastid == "");

	if ($lastid <> "")
		$parameters["since_id"] = $lastid;

	$items = $connection->get('statuses/user_timeline', $parameters);

	if (!is_array($items))
		return;

	$posts = array_reverse($items);

	if (count($posts)) {
	    foreach ($posts as $post) {
		if ($post->id > $lastid)
			$lastid = $post->id;

		if ($first_time)
			continue;

		if ($post->source == "activity")
			continue;

		if (is_object($post->retweeted_status))
			continue;

		if ($post->in_reply_to_status_id != "")
			continue;

		if (!stristr($post->source, $application_name)) {
			$_SESSION["authenticated"] = true;
			$_SESSION["uid"] = $uid;

			unset($_REQUEST);
			$_REQUEST["type"] = "wall";
			$_REQUEST["api_source"] = true;
			$_REQUEST["profile_uid"] = $uid;
			//$_REQUEST["source"] = "StatusNet";
			$_REQUEST["source"] = $post->source;
			$_REQUEST["extid"] = NETWORK_STATUSNET;

			//$_REQUEST["date"] = $post->created_at;

			$_REQUEST["title"] = "";

			$_REQUEST["body"] = add_page_info_to_body($post->text, true);
			if (is_string($post->place->name))
				$_REQUEST["location"] = $post->place->name;

			if (is_string($post->place->full_name))
				$_REQUEST["location"] = $post->place->full_name;

			if (is_array($post->geo->coordinates))
				$_REQUEST["coord"] = $post->geo->coordinates[0]." ".$post->geo->coordinates[1];

			if (is_array($post->coordinates->coordinates))
				$_REQUEST["coord"] = $post->coordinates->coordinates[1]." ".$post->coordinates->coordinates[0];

			//print_r($_REQUEST);
			if ($_REQUEST["body"] != "") {
				logger('statusnet: posting for user '.$uid);

				item_post($a);
			}
		}
	    }
	}
	set_pconfig($uid, 'statusnet', 'lastid', $lastid);
}

function statusnet_address($contact) {
	$hostname = normalise_link($contact->statusnet_profile_url);
	$nickname = $contact->screen_name;

	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $contact->statusnet_profile_url);

	$address = $contact->screen_name."@".$hostname;

	return($address);
}

function statusnet_fetch_contact($uid, $contact, $create_user) {
	if ($contact->statusnet_profile_url == "")
		return(-1);

	// Check if the unique contact is existing
	// To-Do: only update once a while
	 $r = q("SELECT id FROM unique_contacts WHERE url='%s' LIMIT 1",
			dbesc(normalise_link($contact->statusnet_profile_url)));

	if (count($r) == 0)
		q("INSERT INTO unique_contacts (url, name, nick, avatar) VALUES ('%s', '%s', '%s', '%s')",
			dbesc(normalise_link($contact->statusnet_profile_url)),
			dbesc($contact->name),
			dbesc($contact->screen_name),
			dbesc($contact->profile_image_url));
	else
		q("UPDATE unique_contacts SET name = '%s', nick = '%s', avatar = '%s' WHERE url = '%s'",
			dbesc($contact->name),
			dbesc($contact->screen_name),
			dbesc($contact->profile_image_url),
			dbesc(normalise_link($contact->statusnet_profile_url)));

	if (DB_UPDATE_VERSION >= "1177")
		q("UPDATE `unique_contacts` SET `location` = '%s', `about` = '%s' WHERE url = '%s'",
			dbesc($contact->location),
			dbesc($contact->description),
			dbesc(normalise_link($contact->statusnet_profile_url)));

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' AND `network` = '%s'LIMIT 1",
		intval($uid), dbesc(normalise_link($contact->statusnet_profile_url)), dbesc(NETWORK_STATUSNET));

	if(!count($r) AND !$create_user)
		return(0);

	if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
		logger("statusnet_fetch_contact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
		return(-1);
	}

	if(!count($r)) {
		// create contact record
		q("INSERT INTO `contact` ( `uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`writable`, `blocked`, `readonly`, `pending` )
					VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0 ) ",
			intval($uid),
			dbesc(datetime_convert()),
			dbesc($contact->statusnet_profile_url),
			dbesc(normalise_link($contact->statusnet_profile_url)),
			dbesc(statusnet_address($contact)),
			dbesc(normalise_link($contact->statusnet_profile_url)),
			dbesc(''),
			dbesc(''),
			dbesc($contact->name),
			dbesc($contact->screen_name),
			dbesc($contact->profile_image_url),
			dbesc(NETWORK_STATUSNET),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d AND `network` = '%s' LIMIT 1",
			dbesc($contact->statusnet_profile_url),
			intval($uid),
			dbesc(NETWORK_STATUSNET));

		if(! count($r))
			return(false);

		$contact_id  = $r[0]['id'];

		$g = q("SELECT def_gid FROM user WHERE uid = %d LIMIT 1",
			intval($uid)
		);

		if($g && intval($g[0]['def_gid'])) {
			require_once('include/group.php');
			group_add_member($uid,'',$contact_id,$g[0]['def_gid']);
		}

		require_once("Photo.php");

		$photos = import_profile_photo($contact->profile_image_url,$uid,$contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d",
			dbesc($photos[0]),
			dbesc($photos[1]),
			dbesc($photos[2]),
			dbesc(datetime_convert()),
			intval($contact_id)
		);

		if (DB_UPDATE_VERSION >= "1177")
			q("UPDATE `contact` SET `location` = '%s',
						`about` = '%s'
					WHERE `id` = %d",
				dbesc($contact->location),
				dbesc($contact->description),
				intval($contact_id)
			);

	} else {
		// update profile photos once every two weeks as we have no notification of when they change.

		//$update_photo = (($r[0]['avatar-date'] < datetime_convert('','','now -2 days')) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion

		if((!$r[0]['photo']) || (!$r[0]['thumb']) || (!$r[0]['micro']) || ($update_photo)) {

			logger("statusnet_fetch_contact: Updating contact ".$contact->screen_name, LOGGER_DEBUG);

			require_once("Photo.php");

			$photos = import_profile_photo($contact->profile_image_url, $uid, $r[0]['id']);

			q("UPDATE `contact` SET `photo` = '%s',
						`thumb` = '%s',
						`micro` = '%s',
						`name-date` = '%s',
						`uri-date` = '%s',
						`avatar-date` = '%s',
						`url` = '%s',
						`nurl` = '%s',
						`addr` = '%s',
						`name` = '%s',
						`nick` = '%s'
					WHERE `id` = %d",
				dbesc($photos[0]),
				dbesc($photos[1]),
				dbesc($photos[2]),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc($contact->statusnet_profile_url),
				dbesc(normalise_link($contact->statusnet_profile_url)),
				dbesc(statusnet_address($contact)),
				dbesc($contact->name),
				dbesc($contact->screen_name),
				intval($r[0]['id'])
			);

			if (DB_UPDATE_VERSION >= "1177")
				q("UPDATE `contact` SET `location` = '%s',
							`about` = '%s'
						WHERE `id` = %d",
					dbesc($contact->location),
					dbesc($contact->description),
					intval($r[0]['id'])
				);
		}
	}

	return($r[0]["id"]);
}

function statusnet_fetchuser($a, $uid, $screen_name = "", $user_id = "") {
	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');

	require_once("addon/statusnet/codebird.php");

	$cb = \Codebird\Codebird::getInstance();
	$cb->setConsumerKey($ckey, $csecret);
	$cb->setToken($otoken, $osecret);

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if(count($r)) {
		$self = $r[0];
	} else
		return;

	$parameters = array();

	if ($screen_name != "")
		$parameters["screen_name"] = $screen_name;

	if ($user_id != "")
		$parameters["user_id"] = $user_id;

	// Fetching user data
	$user = $cb->users_show($parameters);

	if (!is_object($user))
		return;

	$contact_id = statusnet_fetch_contact($uid, $user, true);

	return $contact_id;
}

function statusnet_createpost($a, $uid, $post, $self, $create_user, $only_existing_contact) {

	require_once("include/html2bbcode.php");

	logger("statusnet_createpost: start", LOGGER_DEBUG);

	$api = get_pconfig($uid, 'statusnet', 'baseapi');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	$postarray = array();
	$postarray['network'] = NETWORK_STATUSNET;
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;

	if (is_object($post->retweeted_status)) {
		$content = $post->retweeted_status;
		statusnet_fetch_contact($uid, $content->user, false);
	} else
		$content = $post;

	$postarray['uri'] = $hostname."::".$content->id;

	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
			dbesc($postarray['uri']),
			intval($uid)
		);

	if (count($r))
		return(array());

	$contactid = 0;

	if ($content->in_reply_to_status_id != "") {

		$parent = $hostname."::".$content->in_reply_to_status_id;

		$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($parent),
				intval($uid)
			);
		if (count($r)) {
			$postarray['thr-parent'] = $r[0]["uri"];
			$postarray['parent-uri'] = $r[0]["parent-uri"];
			$postarray['parent'] = $r[0]["parent"];
			$postarray['object-type'] = ACTIVITY_OBJ_COMMENT;
		} else {
			$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($parent),
					intval($uid)
				);
			if (count($r)) {
				$postarray['thr-parent'] = $r[0]['uri'];
				$postarray['parent-uri'] = $r[0]['parent-uri'];
				$postarray['parent'] = $r[0]['parent'];
				$postarray['object-type'] = ACTIVITY_OBJ_COMMENT;
			} else {
				$postarray['thr-parent'] = $postarray['uri'];
				$postarray['parent-uri'] = $postarray['uri'];
				$postarray['object-type'] = ACTIVITY_OBJ_NOTE;
			}
		}

		// Is it me?
		$own_url = get_pconfig($uid, 'statusnet', 'own_url');

		if ($content->user->id == $own_url) {
			$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
				intval($uid));

			if(count($r)) {
				$contactid = $r[0]["id"];

				$postarray['owner-name'] =  $r[0]["name"];
				$postarray['owner-link'] = $r[0]["url"];
				$postarray['owner-avatar'] =  $r[0]["photo"];
			} else
				return(array());
		}
		// Don't create accounts of people who just comment something
		$create_user = false;
	} else {
		$postarray['parent-uri'] = $postarray['uri'];
		$postarray['object-type'] = ACTIVITY_OBJ_NOTE;
	}

	if ($contactid == 0) {
		$contactid = statusnet_fetch_contact($uid, $post->user, $create_user);
		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = $post->user->statusnet_profile_url;
		$postarray['owner-avatar'] = $post->user->profile_image_url;
	}
	if(($contactid == 0) AND !$only_existing_contact)
		$contactid = $self['id'];
	elseif ($contactid <= 0)
		return(array());

	$postarray['contact-id'] = $contactid;

	$postarray['verb'] = ACTIVITY_POST;

	$postarray['author-name'] = $content->user->name;
	$postarray['author-link'] = $content->user->statusnet_profile_url;
	$postarray['author-avatar'] = $content->user->profile_image_url;

	// To-Do: Maybe unreliable? Can the api be entered without trailing "/"?
	$hostname = str_replace("/api/", "/notice/", get_pconfig($uid, 'statusnet', 'baseapi'));

	$postarray['plink'] = $hostname.$content->id;
	$postarray['app'] = strip_tags($content->source);

	if ($content->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	}

	$postarray['body'] = html2bbcode($content->statusnet_html);

	$converted = statusnet_convertmsg($a, $postarray['body'], false);
	$postarray['body'] = $converted["body"];
	$postarray['tag'] = $converted["tags"];

	$postarray['created'] = datetime_convert('UTC','UTC',$content->created_at);
	$postarray['edited'] = datetime_convert('UTC','UTC',$content->created_at);

	if (is_string($content->place->name))
		$postarray["location"] = $content->place->name;

	if (is_string($content->place->full_name))
		$postarray["location"] = $content->place->full_name;

	if (is_array($content->geo->coordinates))
		$postarray["coord"] = $content->geo->coordinates[0]." ".$content->geo->coordinates[1];

	if (is_array($content->coordinates->coordinates))
		$postarray["coord"] = $content->coordinates->coordinates[1]." ".$content->coordinates->coordinates[0];

	/*if (is_object($post->retweeted_status)) {
		$postarray['body'] = html2bbcode($post->retweeted_status->statusnet_html);

		$converted = statusnet_convertmsg($a, $postarray['body'], false);
		$postarray['body'] = $converted["body"];
		$postarray['tag'] = $converted["tags"];

		statusnet_fetch_contact($uid, $post->retweeted_status->user, false);

		// Let retweets look like wall-to-wall posts
		$postarray['author-name'] = $post->retweeted_status->user->name;
		$postarray['author-link'] = $post->retweeted_status->user->statusnet_profile_url;
		$postarray['author-avatar'] = $post->retweeted_status->user->profile_image_url;
	}*/
	logger("statusnet_createpost: end", LOGGER_DEBUG);
	return($postarray);
}

function statusnet_checknotification($a, $uid, $own_url, $top_item, $postarray) {

	// This function necer worked and need cleanup

	$user = q("SELECT * FROM `contact` WHERE `uid` = %d AND `self` LIMIT 1",
			intval($uid)
		);

	if(!count($user))
		return;

	// Is it me?
	if (link_compare($user[0]["url"], $postarray['author-link']))
		return;

	$own_user = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($uid),
			dbesc($own_url)
		);

	if(!count($own_user))
		return;

	// Is it me from GNU Social?
	if (link_compare($own_user[0]["url"], $postarray['author-link']))
		return;

	$myconv = q("SELECT `author-link`, `author-avatar`, `parent` FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `parent` != 0 AND `deleted` = 0",
			dbesc($postarray['parent-uri']),
			intval($uid)
			);

	if(count($myconv)) {

		foreach($myconv as $conv) {
			// now if we find a match, it means we're in this conversation

			if(!link_compare($conv['author-link'],$user[0]["url"]) AND !link_compare($conv['author-link'],$own_user[0]["url"]))
				continue;

			require_once('include/enotify.php');

			$conv_parent = $conv['parent'];

			notification(array(
				'type'         => NOTIFY_COMMENT,
				'notify_flags' => $user[0]['notify-flags'],
				'language'     => $user[0]['language'],
				'to_name'      => $user[0]['username'],
				'to_email'     => $user[0]['email'],
				'uid'          => $user[0]['uid'],
				'item'         => $postarray,
				'link'         => $a->get_baseurl().'/display/'.urlencode(get_item_guid($top_item)),
				'source_name'  => $postarray['author-name'],
				'source_link'  => $postarray['author-link'],
				'source_photo' => $postarray['author-avatar'],
				'verb'         => ACTIVITY_POST,
				'otype'        => 'item',
				'parent'       => $conv_parent,
			));

			// only send one notification
			break;
		}
	}
}

function statusnet_fetchhometimeline($a, $uid, $mode = 1) {
	$conversations = array();

	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');
	$create_user = get_pconfig($uid, 'statusnet', 'create_user');

	// "create_user" is deactivated, since currently you cannot add users manually by now
	$create_user = true;

	logger("statusnet_fetchhometimeline: Fetching for user ".$uid, LOGGER_DEBUG);

	require_once('library/twitteroauth.php');
	require_once('include/items.php');

	$connection = new StatusNetOAuth($api, $ckey,$csecret,$otoken,$osecret);

	$own_contact = statusnet_fetch_own_contact($a, $uid);

	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($own_contact),
		intval($uid));

	if(count($r)) {
		$nick = $r[0]["nick"];
	} else {
		logger("statusnet_fetchhometimeline: Own GNU Social contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if(count($r)) {
		$self = $r[0];
	} else {
		logger("statusnet_fetchhometimeline: Own contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$u = q("SELECT * FROM user WHERE uid = %d LIMIT 1",
		intval($uid));
	if(!count($u)) {
		logger("statusnet_fetchhometimeline: Own user not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$parameters = array("exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true);
	//$parameters["count"] = 200;

	if ($mode == 1) {
		// Fetching timeline
		$lastid  = get_pconfig($uid, 'statusnet', 'lasthometimelineid');
		//$lastid = 1;

		$first_time = ($lastid == "");

		if ($lastid <> "")
			$parameters["since_id"] = $lastid;

		$items = $connection->get('statuses/home_timeline', $parameters);

		if (!is_array($items)) {
			if (is_object($items) AND isset($items->error))
				$errormsg = $items->error;
			elseif (is_object($items))
				$errormsg = print_r($items, true);
			elseif (is_string($items) OR is_float($items) OR is_int($items))
				$errormsg = $items;
			else
				$errormsg = "Unknown error";

			logger("statusnet_fetchhometimeline: Error fetching home timeline: ".$errormsg, LOGGER_DEBUG);
			return;
		}

		$posts = array_reverse($items);

		logger("statusnet_fetchhometimeline: Fetching timeline for user ".$uid." ".sizeof($posts)." items", LOGGER_DEBUG);

		if (count($posts)) {
			foreach ($posts as $post) {

				if ($post->id > $lastid)
					$lastid = $post->id;

				if ($first_time)
					continue;

				if (isset($post->statusnet_conversation_id)) {
					if (!isset($conversations[$post->statusnet_conversation_id])) {
						statusnet_complete_conversation($a, $uid, $self, $create_user, $nick, $post->statusnet_conversation_id);
						$conversations[$post->statusnet_conversation_id] = $post->statusnet_conversation_id;
					}
				} else {
					$postarray = statusnet_createpost($a, $uid, $post, $self, $create_user, true);

					if (trim($postarray['body']) == "")
						continue;

					$item = item_store($postarray);
					$postarray["id"] = $item;

					logger('statusnet_fetchhometimeline: User '.$self["nick"].' posted home timeline item '.$item);

					if ($item != 0)
						statusnet_checknotification($a, $uid, $nick, $item, $postarray);
				}

			}
		}
		set_pconfig($uid, 'statusnet', 'lasthometimelineid', $lastid);
	}

	// Fetching mentions
	$lastid  = get_pconfig($uid, 'statusnet', 'lastmentionid');
	$first_time = ($lastid == "");

	if ($lastid <> "")
		$parameters["since_id"] = $lastid;

	$items = $connection->get('statuses/mentions_timeline', $parameters);

	if (!is_array($items)) {
		logger("statusnet_fetchhometimeline: Error fetching mentions: ".print_r($items, true), LOGGER_DEBUG);
		return;
	}

	$posts = array_reverse($items);

	logger("statusnet_fetchhometimeline: Fetching mentions for user ".$uid." ".sizeof($posts)." items", LOGGER_DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id > $lastid)
				$lastid = $post->id;

			if ($first_time)
				continue;

			$postarray = statusnet_createpost($a, $uid, $post, $self, false, false);

			if (isset($post->statusnet_conversation_id)) {
				if (!isset($conversations[$post->statusnet_conversation_id])) {
					statusnet_complete_conversation($a, $uid, $self, $create_user, $nick, $post->statusnet_conversation_id);
					$conversations[$post->statusnet_conversation_id] = $post->statusnet_conversation_id;
				}
			} else {
				if (trim($postarray['body']) != "") {
					continue;

					$item = item_store($postarray);
					$postarray["id"] = $item;

					logger('statusnet_fetchhometimeline: User '.$self["nick"].' posted mention timeline item '.$item);
				}
			}

			$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($postarray['uri']),
				intval($uid)
			);
			if (count($r)) {
				$item = $r[0]['id'];
				$parent_id = $r[0]['parent'];
			}

			if ($item != 0) {
				require_once('include/enotify.php');
				notification(array(
					'type'         => NOTIFY_TAGSELF,
					'notify_flags' => $u[0]['notify-flags'],
					'language'     => $u[0]['language'],
					'to_name'      => $u[0]['username'],
					'to_email'     => $u[0]['email'],
					'uid'          => $u[0]['uid'],
					'item'         => $postarray,
					'link'         => $a->get_baseurl().'/display/'.urlencode(get_item_guid($item)),
					'source_name'  => $postarray['author-name'],
					'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
					'verb'         => ACTIVITY_TAG,
					'otype'        => 'item',
					'parent'       => $parent_id,
				));
			}
		}
	}

	set_pconfig($uid, 'statusnet', 'lastmentionid', $lastid);
}

function statusnet_complete_conversation($a, $uid, $self, $create_user, $nick, $conversation) {
	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');

	require_once('library/twitteroauth.php');

	$connection = new StatusNetOAuth($api, $ckey,$csecret,$otoken,$osecret);

	$parameters["count"] = 200;

	$items = $connection->get('statusnet/conversation/'.$conversation, $parameters);
	if (is_array($items)) {
		$posts = array_reverse($items);

		foreach($posts AS $post) {
			$postarray = statusnet_createpost($a, $uid, $post, $self, false, false);

			if (trim($postarray['body']) == "")
				continue;

			//print_r($postarray);
			$item = item_store($postarray);
			$postarray["id"] = $item;

			logger('statusnet_complete_conversation: User '.$self["nick"].' posted home timeline item '.$item);

			if ($item != 0)
				statusnet_checknotification($a, $uid, $nick, $item, $postarray);
		}
	}
}

function statusnet_convertmsg($a, $body, $no_tags = false) {

	require_once("include/oembed.php");
	require_once("include/items.php");
	require_once("include/network.php");

	$body = preg_replace("=\[url\=https?://([0-9]*).([0-9]*).([0-9]*).([0-9]*)/([0-9]*)\](.*?)\[\/url\]=ism","$1.$2.$3.$4/$5",$body);

	$URLSearchString = "^\[\]";
	$links = preg_match_all("/[^!#@]\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism", $body,$matches,PREG_SET_ORDER);

	$footer = "";
	$footerurl = "";
	$footerlink = "";
	$type = "";

	if ($links) {
		foreach ($matches AS $match) {
			$search = "[url=".$match[1]."]".$match[2]."[/url]";

			logger("statusnet_convertmsg: expanding url ".$match[1], LOGGER_DEBUG);

			$expanded_url = original_url($match[1]);

			logger("statusnet_convertmsg: fetching data for ".$expanded_url, LOGGER_DEBUG);

			$oembed_data = oembed_fetch_url($expanded_url, true);

			logger("statusnet_convertmsg: fetching data: done", LOGGER_DEBUG);

			if ($type == "")
				$type = $oembed_data->type;
			if ($oembed_data->type == "video") {
				//$body = str_replace($search, "[video]".$expanded_url."[/video]", $body);
				$type = $oembed_data->type;
				$footerurl = $expanded_url;
				$footerlink = "[url=".$expanded_url."]".$expanded_url."[/url]";

				$body = str_replace($search, $footerlink, $body);
			} elseif (($oembed_data->type == "photo") AND isset($oembed_data->url) AND !$dontincludemedia)
				$body = str_replace($search, "[url=".$expanded_url."][img]".$oembed_data->url."[/img][/url]", $body);
			elseif ($oembed_data->type != "link")
				$body = str_replace($search,  "[url=".$expanded_url."]".$expanded_url."[/url]", $body);
			else {
				$img_str = fetch_url($expanded_url, true, $redirects, 4);

				$tempfile = tempnam(get_temppath(), "cache");
				file_put_contents($tempfile, $img_str);
				$mime = image_type_to_mime_type(exif_imagetype($tempfile));
				unlink($tempfile);

				if (substr($mime, 0, 6) == "image/") {
					$type = "photo";
					$body = str_replace($search, "[img]".$expanded_url."[/img]", $body);
				} else {
					$type = $oembed_data->type;
					$footerurl = $expanded_url;
					$footerlink = "[url=".$expanded_url."]".$expanded_url."[/url]";

					$body = str_replace($search, $footerlink, $body);
				}
			}
		}

		if ($footerurl != "")
			$footer = add_page_info($footerurl);

		if (($footerlink != "") AND (trim($footer) != "")) {
			$removedlink = trim(str_replace($footerlink, "", $body));

			if (($removedlink == "") OR strstr($body, $removedlink))
				$body = $removedlink;

			$body .= $footer;
		}
	}

	if ($no_tags)
		return(array("body" => $body, "tags" => ""));

	$str_tags = '';

	$cnt = preg_match_all("/([!#@])\[url\=([$URLSearchString]*)\](.*?)\[\/url\]/ism",$body,$matches,PREG_SET_ORDER);
	if($cnt) {
		foreach($matches as $mtch) {
			if(strlen($str_tags))
				$str_tags .= ',';

			if ($mtch[1] == "#") {
				// Replacing the hash tags that are directed to the GNU Social server with internal links
				$snhash = "#[url=".$mtch[2]."]".$mtch[3]."[/url]";
				$frdchash = '#[url='.$a->get_baseurl().'/search?tag='.rawurlencode($mtch[3]).']'.$mtch[3].'[/url]';
				$body = str_replace($snhash, $frdchash, $body);

				$str_tags .= $frdchash;
			} else
				$str_tags .= "@[url=".$mtch[2]."]".$mtch[3]."[/url]";
				// To-Do:
				// There is a problem with links with to GNU Social groups, so these links are stored with "@" like friendica groups
				//$str_tags .= $mtch[1]."[url=".$mtch[2]."]".$mtch[3]."[/url]";
		}
	}

	return(array("body"=>$body, "tags"=>$str_tags));

}

function statusnet_fetch_own_contact($a, $uid) {
	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');
	$own_url = get_pconfig($uid, 'statusnet', 'own_url');

	$contact_id = 0;

	if ($own_url == "") {
		require_once('library/twitteroauth.php');

		$connection = new StatusNetOAuth($api, $ckey,$csecret,$otoken,$osecret);

		// Fetching user data
		$user = $connection->get('account/verify_credentials');

		set_pconfig($uid, 'statusnet', 'own_url', normalise_link($user->statusnet_profile_url));

		$contact_id = statusnet_fetch_contact($uid, $user, true);

	} else {
		$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
			intval($uid), dbesc($own_url));
		if(count($r))
			$contact_id = $r[0]["id"];
		else
			del_pconfig($uid, 'statusnet', 'own_url');

	}
	return($contact_id);
}

function statusnet_is_retweet($a, $uid, $body) {
	$body = trim($body);

	// Skip if it isn't a pure repeated messages
	// Does it start with a share?
	if (strpos($body, "[share") > 0)
		return(false);

	// Does it end with a share?
	if (strlen($body) > (strrpos($body, "[/share]") + 8))
		return(false);

	$attributes = preg_replace("/\[share(.*?)\]\s?(.*?)\s?\[\/share\]\s?/ism","$1",$body);
	// Skip if there is no shared message in there
	if ($body == $attributes)
		return(false);

	$link = "";
	preg_match("/link='(.*?)'/ism", $attributes, $matches);
	if ($matches[1] != "")
		$link = $matches[1];

	preg_match('/link="(.*?)"/ism', $attributes, $matches);
	if ($matches[1] != "")
		$link = $matches[1];

	$ckey    = get_pconfig($uid, 'statusnet', 'consumerkey');
	$csecret = get_pconfig($uid, 'statusnet', 'consumersecret');
	$api     = get_pconfig($uid, 'statusnet', 'baseapi');
	$otoken  = get_pconfig($uid, 'statusnet', 'oauthtoken');
	$osecret = get_pconfig($uid, 'statusnet', 'oauthsecret');
	$hostname = preg_replace("=https?://([\w\.]*)/.*=ism", "$1", $api);

	$id = preg_replace("=https?://".$hostname."/notice/(.*)=ism", "$1", $link);

	if ($id == $link)
		return(false);

	logger('statusnet_is_retweet: Retweeting id '.$id.' for user '.$uid, LOGGER_DEBUG);

	$connection = new StatusNetOAuth($api, $ckey,$csecret,$otoken,$osecret);

	$result = $connection->post('statuses/retweet/'.$id);

	logger('statusnet_is_retweet: result '.print_r($result, true), LOGGER_DEBUG);
	return(isset($result->id));
}
