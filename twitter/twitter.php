<?php
/**
 * Name: Twitter Connector
 * Description: Relay public postings to a connected Twitter account
 * Version: 1.0.4
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
 
/*   Twitter Plugin for Friendica
 *
 *   Author: Tobias Diekershoff
 *           tobias.diekershoff@gmx.net
 *
 *   License:3-clause BSD license
 *
 *   Configuration:
 *     To use this plugin you need a OAuth Consumer key pair (key & secret)
 *     you can get it from Twitter at https://twitter.com/apps
 *
 *     Register your Friendica site as "Client" application with "Read & Write" access
 *     we do not need "Twitter as login". When you've registered the app you get the
 *     OAuth Consumer key and secret pair for your application/site.
 *
 *     Add this key pair to your global .htconfig.php or use the admin panel.
 *
 *     $a->config['twitter']['consumerkey'] = 'your consumer_key here';
 *     $a->config['twitter']['consumersecret'] = 'your consumer_secret here';
 *
 *     To activate the plugin itself add it to the $a->config['system']['addon']
 *     setting. After this, your user can configure their Twitter account settings
 *     from "Settings -> Plugin Settings".
 *
 *     Requirements: PHP5, curl [Slinky library]
 */

define('TWITTER_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function twitter_install() {
	//  we need some hooks, for the configuration and for sending tweets
	register_hook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	register_hook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	register_hook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	register_hook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	register_hook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	register_hook('cron', 'addon/twitter/twitter.php', 'twitter_cron');
	register_hook('queue_predeliver', 'addon/twitter/twitter.php', 'twitter_queue_hook');
	register_hook('follow', 'addon/twitter/twitter.php', 'twitter_follow');
	logger("installed twitter");
}


function twitter_uninstall() {
	unregister_hook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	unregister_hook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	unregister_hook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	unregister_hook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	unregister_hook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	unregister_hook('cron', 'addon/twitter/twitter.php', 'twitter_cron');
	unregister_hook('queue_predeliver', 'addon/twitter/twitter.php', 'twitter_queue_hook');
	unregister_hook('follow', 'addon/twitter/twitter.php', 'twitter_follow');

	// old setting - remove only
	unregister_hook('post_local_end', 'addon/twitter/twitter.php', 'twitter_post_hook');
	unregister_hook('plugin_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	unregister_hook('plugin_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');

}

function twitter_follow($a, &$contact) {

	logger("twitter_follow: Check if contact is twitter contact. ".$contact["url"], LOGGER_DEBUG);

	if (!strstr($contact["url"], "://twitter.com") AND !strstr($contact["url"], "@twitter.com"))
		return;

	// contact seems to be a twitter contact, so continue
	$nickname = preg_replace("=https?://twitter.com/(.*)=ism", "$1", $contact["url"]);
	$nickname = str_replace("@twitter.com", "", $nickname);

	$uid = $a->user["uid"];

	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');

	require_once("addon/twitter/codebird.php");

	$cb = \Codebird\Codebird::getInstance();
	$cb->setConsumerKey($ckey, $csecret);
	$cb->setToken($otoken, $osecret);

	$parameters = array();
	$parameters["screen_name"] = $nickname;

	$user = $cb->friendships_create($parameters);

	twitter_fetchuser($a, $uid, $nickname);

	$r = q("SELECT name,nick,url,addr,batch,notify,poll,request,confirm,poco,photo,priority,network,alias,pubkey
		FROM `contact` WHERE `uid` = %d AND `nick` = '%s'",
				intval($uid),
				dbesc($nickname));
	if (count($r))
		$contact["contact"] = $r[0];
}

function twitter_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$tw_post = get_pconfig(local_user(),'twitter','post');
	if(intval($tw_post) == 1) {
		$tw_defpost = get_pconfig(local_user(),'twitter','post_by_default');
		$selected = ((intval($tw_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="twitter_enable"' . $selected . ' value="1" /> ' 
			. t('Post to Twitter') . '</div>';
	}
}

function twitter_settings_post ($a,$post) {
	if(! local_user())
		return;
	// don't check twitter settings if twitter submit button is not clicked
	if (!x($_POST,'twitter-submit'))
		return;

	if (isset($_POST['twitter-disconnect'])) {
		/***
		 * if the twitter-disconnect checkbox is set, clear the OAuth key/secret pair
		 * from the user configuration
		 */
		del_pconfig(local_user(), 'twitter', 'consumerkey');
		del_pconfig(local_user(), 'twitter', 'consumersecret');
                del_pconfig(local_user(), 'twitter', 'oauthtoken');
                del_pconfig(local_user(), 'twitter', 'oauthsecret');
                del_pconfig(local_user(), 'twitter', 'post');
                del_pconfig(local_user(), 'twitter', 'post_by_default');
                del_pconfig(local_user(), 'twitter', 'post_taglinks');
		del_pconfig(local_user(), 'twitter', 'lastid');
		del_pconfig(local_user(), 'twitter', 'mirror_posts');
		del_pconfig(local_user(), 'twitter', 'intelligent_shortening');
		del_pconfig(local_user(), 'twitter', 'import');
		del_pconfig(local_user(), 'twitter', 'create_user');
	} else {
	if (isset($_POST['twitter-pin'])) {
		//  if the user supplied us with a PIN from Twitter, let the magic of OAuth happen
		logger('got a Twitter PIN');
		require_once('library/twitteroauth.php');
		$ckey    = get_config('twitter', 'consumerkey');
		$csecret = get_config('twitter', 'consumersecret');
		//  the token and secret for which the PIN was generated were hidden in the settings
		//  form as token and token2, we need a new connection to Twitter using these token
		//  and secret to request a Access Token with the PIN
		$connection = new TwitterOAuth($ckey, $csecret, $_POST['twitter-token'], $_POST['twitter-token2']);
		$token   = $connection->getAccessToken( $_POST['twitter-pin'] );
		//  ok, now that we have the Access Token, save them in the user config
 		set_pconfig(local_user(),'twitter', 'oauthtoken',  $token['oauth_token']);
		set_pconfig(local_user(),'twitter', 'oauthsecret', $token['oauth_token_secret']);
                set_pconfig(local_user(),'twitter', 'post', 1);
                set_pconfig(local_user(),'twitter', 'post_taglinks', 1);
                //  reload the Addon Settings page, if we don't do it see Bug #42
                goaway($a->get_baseurl().'/settings/connectors');
	} else {
		//  if no PIN is supplied in the POST variables, the user has changed the setting
		//  to post a tweet for every new __public__ posting to the wall
		set_pconfig(local_user(),'twitter','post',intval($_POST['twitter-enable']));
                set_pconfig(local_user(),'twitter','post_by_default',intval($_POST['twitter-default']));
                set_pconfig(local_user(),'twitter','post_taglinks',intval($_POST['twitter-sendtaglinks']));
		set_pconfig(local_user(), 'twitter', 'mirror_posts', intval($_POST['twitter-mirror']));
		set_pconfig(local_user(), 'twitter', 'intelligent_shortening', intval($_POST['twitter-shortening']));
		set_pconfig(local_user(), 'twitter', 'import', intval($_POST['twitter-import']));
		set_pconfig(local_user(), 'twitter', 'create_user', intval($_POST['twitter-create_user']));
                info( t('Twitter settings updated.') . EOL);
	}}
}
function twitter_settings(&$a,&$s) {
        if(! local_user())
                return;
        $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/twitter/twitter.css' . '" media="all" />' . "\r\n";
	/***
	 * 1) Check that we have global consumer key & secret
	 * 2) If no OAuthtoken & stuff is present, generate button to get some
	 * 3) Checkbox for "Send public notices (140 chars only)
	 */
	$ckey    = get_config('twitter', 'consumerkey' );
	$csecret = get_config('twitter', 'consumersecret' );
	$otoken  = get_pconfig(local_user(), 'twitter', 'oauthtoken'  );
	$osecret = get_pconfig(local_user(), 'twitter', 'oauthsecret' );
        $enabled = get_pconfig(local_user(), 'twitter', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
        $defenabled = get_pconfig(local_user(),'twitter','post_by_default');
	$defchecked = (($defenabled) ? ' checked="checked" ' : '');
        $linksenabled = get_pconfig(local_user(),'twitter','post_taglinks');
        $linkschecked = (($linksenabled) ? ' checked="checked" ' : '');
        $mirrorenabled = get_pconfig(local_user(),'twitter','mirror_posts');
        $mirrorchecked = (($mirrorenabled) ? ' checked="checked" ' : '');
        $shorteningenabled = get_pconfig(local_user(),'twitter','intelligent_shortening');
        $shorteningchecked = (($shorteningenabled) ? ' checked="checked" ' : '');
        $importenabled = get_pconfig(local_user(),'twitter','import');
        $importchecked = (($importenabled) ? ' checked="checked" ' : '');
        $create_userenabled = get_pconfig(local_user(),'twitter','create_user');
        $create_userchecked = (($create_userenabled) ? ' checked="checked" ' : '');

	$s .= '<span id="settings_twitter_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<h3>'. t('Twitter Posting Settings') .'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_twitter_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_twitter_expanded\'); openClose(\'settings_twitter_inflated\');">';
	$s .= '<h3>'. t('Twitter Posting Settings') .'</h3>';
	$s .= '</span>';

	if ( (!$ckey) && (!$csecret) ) {
		/***
		 * no global consumer keys
		 * display warning and skip personal config
		 */
		$s .= '<p>'. t('No consumer key pair for Twitter found. Please contact your site administrator.') .'</p>';
	} else {
		/***
		 * ok we have a consumer key pair now look into the OAuth stuff
		 */
		if ( (!$otoken) && (!$osecret) ) {
			/***
			 * the user has not yet connected the account to twitter...
			 * get a temporary OAuth key/secret pair and display a button with
			 * which the user can request a PIN to connect the account to a
			 * account at Twitter.
			 */
		        require_once('library/twitteroauth.php');
			$connection = new TwitterOAuth($ckey, $csecret);
			$request_token = $connection->getRequestToken();
			$token = $request_token['oauth_token'];
			/***
			 *  make some nice form
			 */
			$s .= '<p>'. t('At this Friendica instance the Twitter plugin was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.') .'</p>';
			$s .= '<a href="'.$connection->getAuthorizeURL($token).'" target="_twitter"><img src="addon/twitter/lighter.png" alt="'.t('Log in with Twitter').'"></a>';
			$s .= '<div id="twitter-pin-wrapper">';
			$s .= '<label id="twitter-pin-label" for="twitter-pin">'. t('Copy the PIN from Twitter here') .'</label>';
			$s .= '<input id="twitter-pin" type="text" name="twitter-pin" />';
			$s .= '<input id="twitter-token" type="hidden" name="twitter-token" value="'.$token.'" />';
			$s .= '<input id="twitter-token2" type="hidden" name="twitter-token2" value="'.$request_token['oauth_token_secret'].'" />';
            $s .= '</div><div class="clear"></div>';
            $s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
		} else {
			/***
			 *  we have an OAuth key / secret pair for the user
			 *  so let's give a chance to disable the postings to Twitter
			 */
                        require_once('library/twitteroauth.php');
			$connection = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);
			$details = $connection->get('account/verify_credentials');
			$s .= '<div id="twitter-info" ><img id="twitter-avatar" src="'.$details->profile_image_url.'" /><p id="twitter-info-block">'. t('Currently connected to: ') .'<a href="https://twitter.com/'.$details->screen_name.'" target="_twitter">'.$details->screen_name.'</a><br /><em>'.$details->description.'</em></p></div>';
			$s .= '<p>'. t('If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.') .'</p>';
                        if ($a->user['hidewall']) {
                            $s .= '<p>'. t('<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.') .'</p>';
                        }
			$s .= '<div id="twitter-enable-wrapper">';
			$s .= '<label id="twitter-enable-label" for="twitter-checkbox">'. t('Allow posting to Twitter'). '</label>';
			$s .= '<input id="twitter-checkbox" type="checkbox" name="twitter-enable" value="1" ' . $checked . '/>';
                        $s .= '<div class="clear"></div>';
                        $s .= '<label id="twitter-default-label" for="twitter-default">'. t('Send public postings to Twitter by default') .'</label>';
                        $s .= '<input id="twitter-default" type="checkbox" name="twitter-default" value="1" ' . $defchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-mirror-label" for="twitter-mirror">'.t('Mirror all posts from twitter that are no replies').'</label>';
                        $s .= '<input id="twitter-mirror" type="checkbox" name="twitter-mirror" value="1" '. $mirrorchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-shortening-label" for="twitter-shortening">'.t('Shortening method that optimizes the tweet').'</label>';
                        $s .= '<input id="twitter-shortening" type="checkbox" name="twitter-shortening" value="1" '. $shorteningchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-sendtaglinks-label" for="twitter-sendtaglinks">'.t('Send linked #-tags and @-names to Twitter').'</label>';
                        $s .= '<input id="twitter-sendtaglinks" type="checkbox" name="twitter-sendtaglinks" value="1" '. $linkschecked . '/>';
			$s .= '</div><div class="clear"></div>';

                        $s .= '<label id="twitter-import-label" for="twitter-import">'.t('Import the remote timeline').'</label>';
                        $s .= '<input id="twitter-import" type="checkbox" name="twitter-import" value="1" '. $importchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-create_user-label" for="twitter-create_user">'.t('Automatically create contacts').'</label>';
                        $s .= '<input id="twitter-create_user" type="checkbox" name="twitter-create_user" value="1" '. $create_userchecked . '/>';
			$s .= '<div class="clear"></div>';

			$s .= '<div id="twitter-disconnect-wrapper">';
                        $s .= '<label id="twitter-disconnect-label" for="twitter-disconnect">'. t('Clear OAuth configuration') .'</label>';
                        $s .= '<input id="twitter-disconnect" type="checkbox" name="twitter-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>'; 
		}
	}
        $s .= '</div><div class="clear"></div>';
}


function twitter_post_local(&$a,&$b) {

	if($b['edit'])
		return;

	if((local_user()) && (local_user() == $b['uid']) && (! $b['private']) && (! $b['parent']) ) {

		$twitter_post = intval(get_pconfig(local_user(),'twitter','post'));
		$twitter_enable = (($twitter_post && x($_REQUEST,'twitter_enable')) ? intval($_REQUEST['twitter_enable']) : 0);

		// if API is used, default to the chosen settings
		if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'twitter','post_by_default')))
			$twitter_enable = 1;

        if(! $twitter_enable)
            return;

        if(strlen($b['postopts']))
            $b['postopts'] .= ',';
        $b['postopts'] .= 'twitter';
	}
}

if (! function_exists('short_link')) {
function short_link ($url) {
    require_once('library/slinky.php');
    $slinky = new Slinky( $url );
    $yourls_url = get_config('yourls','url1');
    if ($yourls_url) {
            $yourls_username = get_config('yourls','username1');
            $yourls_password = get_config('yourls', 'password1');
            $yourls_ssl = get_config('yourls', 'ssl1');
            $yourls = new Slinky_YourLS();
            $yourls->set( 'username', $yourls_username );
            $yourls->set( 'password', $yourls_password );
            $yourls->set( 'ssl', $yourls_ssl );
            $yourls->set( 'yourls-url', $yourls_url );
            $slinky->set_cascade( array( $yourls, new Slinky_UR1ca(), new Slinky_Trim(), new Slinky_IsGd(), new Slinky_TinyURL() ) );
    }
    else {
            // setup a cascade of shortening services
            // try to get a short link from these services
            // in the order ur1.ca, trim, id.gd, tinyurl
            $slinky->set_cascade( array( new Slinky_UR1ca(), new Slinky_Trim(), new Slinky_IsGd(), new Slinky_TinyURL() ) );
    }
    return $slinky->short();
} };

function twitter_shortenmsg($b, $shortlink = false) {
	require_once("include/bbcode.php");
	require_once("include/html2plain.php");

	$max_char = 140;

	// Looking for the first image
	$image = '';
	if(preg_match("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/is",$b['body'],$matches))
		$image = $matches[3];

	if ($image == '')
		if(preg_match("/\[img\](.*?)\[\/img\]/is",$b['body'],$matches))
			$image = $matches[1];

	$multipleimages = (strpos($b['body'], "[img") != strrpos($b['body'], "[img"));

	// When saved into the database the content is sent through htmlspecialchars
	// That means that we have to decode all image-urls
	$image = htmlspecialchars_decode($image);

	$body = $b["body"];
	if ($b["title"] != "")
		$body = $b["title"]."\n\n".$body;

	if (strpos($body, "[bookmark") !== false) {
		// splitting the text in two parts:
		// before and after the bookmark
		$pos = strpos($body, "[bookmark");
		$body1 = substr($body, 0, $pos);
		$body2 = substr($body, $pos);

		// Removing all quotes after the bookmark
		// they are mostly only the content after the bookmark.
		$body2 = preg_replace("/\[quote\=([^\]]*)\](.*?)\[\/quote\]/ism",'',$body2);
		$body2 = preg_replace("/\[quote\](.*?)\[\/quote\]/ism",'',$body2);
		$body = $body1.$body2;
	}

	// Add some newlines so that the message could be cut better
	$body = str_replace(array("[quote", "[bookmark", "[/bookmark]", "[/quote]"),
			array("\n[quote", "\n[bookmark", "[/bookmark]\n", "[/quote]\n"), $body);

	// remove the recycle signs and the names since they aren't helpful on twitter
	// recycle 1
	$recycle = html_entity_decode("&#x2672; ", ENT_QUOTES, 'UTF-8');
	$body = preg_replace( '/'.$recycle.'\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', "\n", $body);
	// recycle 2 (Test)
	$recycle = html_entity_decode("&#x25CC; ", ENT_QUOTES, 'UTF-8');
	$body = preg_replace( '/'.$recycle.'\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', "\n", $body);

	// remove the share element
	//$body = preg_replace("/\[share(.*?)\](.*?)\[\/share\]/ism","\n\n$2\n\n",$body);

	// At first convert the text to html
	$html = bbcode($body, false, false, 2);

	// Then convert it to plain text
	$msg = trim(html2plain($html, 0, true));
	$msg = html_entity_decode($msg,ENT_QUOTES,'UTF-8');

	// Removing multiple newlines
	while (strpos($msg, "\n\n\n") !== false)
		$msg = str_replace("\n\n\n", "\n\n", $msg);

	// Removing multiple spaces
	while (strpos($msg, "  ") !== false)
		$msg = str_replace("  ", " ", $msg);

	$origmsg = trim($msg);

	// Removing URLs
	$msg = preg_replace('/(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)/i', "", $msg);

	$msg = trim($msg);

	$link = '';
	// look for bookmark-bbcode and handle it with priority
	if(preg_match("/\[bookmark\=([^\]]*)\](.*?)\[\/bookmark\]/is",$b['body'],$matches))
		$link = $matches[1];

	$multiplelinks = (strpos($b['body'], "[bookmark") != strrpos($b['body'], "[bookmark"));

	// If there is no bookmark element then take the first link
	if ($link == '') {
		$links = collecturls($html);

		foreach($links AS $singlelink) {
			$img_str = fetch_url($singlelink);

			$tempfile = tempnam(get_config("system","temppath"), "cache");
			file_put_contents($tempfile, $img_str);
			$mime = image_type_to_mime_type(exif_imagetype($tempfile));
			unlink($tempfile);

			if (substr($mime, 0, 6) == "image/") {
				$image = $singlelink;
				unset($links[$singlelink]);
			}
		}

		if (sizeof($links) > 0) {
			reset($links);
			$link = current($links);
		}
		$multiplelinks = (sizeof($links) > 1);
	}

	$msglink = "";
	if ($multiplelinks)
		$msglink = $b["plink"];
	else if ($link != "")
		$msglink = $link;
	else if ($multipleimages)
		$msglink = $b["plink"];
	else if ($image != "")
		$msglink = $image;

	if (($msglink == "") and strlen($msg) > $max_char)
		$msglink = $b["plink"];

	// If the message is short enough then don't modify it.
	if ((strlen($origmsg) <= $max_char) AND ($msglink == ""))
		return(array("msg"=>$origmsg, "image"=>""));

	// If the message is short enough and contains a picture then post the picture as well
	if ((strlen($origmsg) <= ($max_char - 23)) AND strpos($origmsg, $msglink))
		return(array("msg"=>$origmsg, "image"=>$image));

	// If the message is short enough and the link exists in the original message don't modify it as well
	// -3 because of the bad shortener of twitter
	if ((strlen($origmsg) <= ($max_char - 3)) AND strpos($origmsg, $msglink))
		return(array("msg"=>$origmsg, "image"=>""));

	// Preserve the unshortened link
	$orig_link = $msglink;

	// Just replace the message link with a 22 character long string
	// Twitter calculates with this length
	if (trim($msglink) <> '')
		$msglink = "1234567890123456789012";

	if (strlen(trim($msg." ".$msglink)) > ($max_char)) {
		$msg = substr($msg, 0, ($max_char) - (strlen($msglink)));
		$lastchar = substr($msg, -1);
		$msg = substr($msg, 0, -1);
		$pos = strrpos($msg, "\n");
		if ($pos > 0)
			$msg = substr($msg, 0, $pos);
		else if ($lastchar != "\n")
			$msg = substr($msg, 0, -3)."...";

		// if the post contains a picture and a link then the system tries to cut the post earlier.
		// So the link and the picture can be posted.
		if (($image != "") AND ($orig_link != $image)) {
			$msg2 = substr($msg, 0, ($max_char - 20) - (strlen($msglink)));
			$lastchar = substr($msg2, -1);
			$msg2 = substr($msg2, 0, -1);
			$pos = strrpos($msg2, "\n");
			if ($pos > 0)
				$msg = substr($msg2, 0, $pos);
			else if ($lastchar == "\n")
				$msg = trim($msg2);
		}

	}
	// Removing multiple spaces - again
	while (strpos($msg, "  ") !== false)
		$msg = str_replace("  ", " ", $msg);

	$msg = trim($msg);

	// Removing multiple newlines
	//while (strpos($msg, "\n\n") !== false)
	//	$msg = str_replace("\n\n", "\n", $msg);

	// Looking if the link points to an image
	$img_str = fetch_url($orig_link);

	$tempfile = tempnam(get_config("system","temppath"), "cache");
	file_put_contents($tempfile, $img_str);
	$mime = image_type_to_mime_type(exif_imagetype($tempfile));
	unlink($tempfile);

	if (($image == $orig_link) OR (substr($mime, 0, 6) == "image/"))
		return(array("msg"=>$msg, "image"=>$orig_link));
	else if (($image != $orig_link) AND ($image != "") AND (strlen($msg." ".$msglink) <= ($max_char - 23))) {
		if ($shortlink)
			$orig_link = short_link($orig_link);

		return(array("msg"=>$msg." ".$orig_link, "image"=>$image));
	} else {
		if ($shortlink)
			$orig_link = short_link($orig_link);

		return(array("msg"=>$msg." ".$orig_link, "image"=>""));
	}
}

function twitter_action($a, $uid, $pid, $action) {

	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');

	require_once("addon/twitter/codebird.php");

	$cb = \Codebird\Codebird::getInstance();
	$cb->setConsumerKey($ckey, $csecret);
	$cb->setToken($otoken, $osecret);

	$post = array('id' => $pid);

	logger("twitter_action '".$action."' ID: ".$pid." data: " . print_r($post, true), LOGGER_DATA);

	switch ($action) {
		case "delete":
			$result = $cb->statuses_destroy($post);
			break;
		case "like":
			$result = $cb->favorites_create($post);
			break;
		case "unlike":
			$result = $cb->favorites_destroy($post);
			break;
	}
	logger("twitter_action '".$action."' send, result: " . print_r($result, true), LOGGER_DEBUG);
}

function twitter_post_hook(&$a,&$b) {

	/**
	 * Post to Twitter
	 */

	if (!get_pconfig($b["uid"],'twitter','import')) {
		if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
			return;
	}

	if($b['parent'] != $b['id']) {
		logger("twitter_post_hook: parameter ".print_r($b, true), LOGGER_DATA);

                // Looking if its a reply to a twitter post
		if ((substr($b["parent-uri"], 0, 9) != "twitter::") AND (substr($b["extid"], 0, 9) != "twitter::") AND (substr($b["thr-parent"], 0, 9) != "twitter::")) {
                        logger("twitter_post_hook: no twitter post ".$b["parent"]);
                        return;
		}

                $r = q("SELECT * FROM item WHERE item.uri = '%s' AND item.uid = %d LIMIT 1",
                        dbesc($b["thr-parent"]),
                        intval($b["uid"]));

                if(!count($r)) {
                        logger("twitter_post_hook: no parent found ".$b["thr-parent"]);
                        return;
                } else {
                        $iscomment = true;
                        $orig_post = $r[0];
                }

		// To-Do: Ab dem letzten / nehmen
		$b["body"] = "@".substr($orig_post["author-link"], 20)." ".$b["body"];

		logger("twitter_post_hook: parent found ".print_r($orig_post, true), LOGGER_DATA);
	} else {
		$iscomment = false;

		if($b['private'] OR !strstr($b['postopts'],'twitter'))
			return;
	}

	if (($b['verb'] == ACTIVITY_POST) AND $b['deleted'])
		twitter_action($a, $b["uid"], substr($orig_post["uri"], 9), "delete");

	if($b['verb'] == ACTIVITY_LIKE) {
		logger("twitter_post_hook: parameter 2 ".substr($b["thr-parent"], 9), LOGGER_DEBUG);
		if ($b['deleted'])
			twitter_action($a, $b["uid"], substr($b["thr-parent"], 9), "unlike");
		else
			twitter_action($a, $b["uid"], substr($b["thr-parent"], 9), "like");
		return;
        }

	if($b['deleted'] || ($b['created'] !== $b['edited']))
                return;

	// if post comes from twitter don't send it back
	if($b['app'] == "Twitter")
		return;

	logger('twitter post invoked');


	load_pconfig($b['uid'], 'twitter');

	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($b['uid'], 'twitter', 'oauthtoken');
	$osecret = get_pconfig($b['uid'], 'twitter', 'oauthsecret');
	$intelligent_shortening = get_pconfig($b['uid'], 'twitter', 'intelligent_shortening');

	// Global setting overrides this
	if (get_config('twitter','intelligent_shortening'))
                $intelligent_shortening = get_config('twitter','intelligent_shortening');

	if($ckey && $csecret && $otoken && $osecret) {
		logger('twitter: we have customer key and oauth stuff, going to send.', LOGGER_DEBUG);

		require_once('library/twitteroauth.php');
		require_once('include/bbcode.php');
		$tweet = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);
                // in theory max char is 140 but T. uses t.co to make links 
                // longer so we give them 10 characters extra
		if (!$intelligent_shortening) {
			$max_char = 130; // max. length for a tweet
	                // we will only work with up to two times the length of the dent 
	                // we can later send to Twitter. This way we can "gain" some 
	                // information during shortening of potential links but do not 
	                // shorten all the links in a 200000 character long essay.
	                if (! $b['title']=='') {
	                    $tmp = $b['title'] . ' : '. $b['body'];
	//                    $tmp = substr($tmp, 0, 4*$max_char);
	                } else {
	                    $tmp = $b['body']; // substr($b['body'], 0, 3*$max_char);
	                }
	                // if [url=bla][img]blub.png[/img][/url] get blub.png
	                $tmp = preg_replace( '/\[url\=(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)\]\[img\](\\w+.*?)\\[\\/img\]\\[\\/url\]/i', '$2', $tmp);
	                // preserve links to images, videos and audios
	                $tmp = preg_replace( '/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism', '$3', $tmp);
	                $tmp = preg_replace( '/\[\\/?img(\\s+.*?\]|\])/i', '', $tmp);
	                $tmp = preg_replace( '/\[\\/?video(\\s+.*?\]|\])/i', '', $tmp);
	                $tmp = preg_replace( '/\[\\/?youtube(\\s+.*?\]|\])/i', '', $tmp);
	                $tmp = preg_replace( '/\[\\/?vimeo(\\s+.*?\]|\])/i', '', $tmp);
	                $tmp = preg_replace( '/\[\\/?audio(\\s+.*?\]|\])/i', '', $tmp);
	                $linksenabled = get_pconfig($b['uid'],'twitter','post_taglinks');
	                // if a #tag is linked, don't send the [url] over to SN
	                // that is, don't send if the option is not set in the
	                // connector settings
	                if ($linksenabled=='0') {
				// #-tags
				$tmp = preg_replace( '/#\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '#$2', $tmp);
				// @-mentions
				$tmp = preg_replace( '/@\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '@$2', $tmp);
				// recycle 1
				$recycle = html_entity_decode("&#x2672; ", ENT_QUOTES, 'UTF-8');
				$tmp = preg_replace( '/'.$recycle.'\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', $recycle.'$2', $tmp);
				// recycle 2 (Test)
				$recycle = html_entity_decode("&#x25CC; ", ENT_QUOTES, 'UTF-8');
				$tmp = preg_replace( '/'.$recycle.'\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', $recycle.'$2', $tmp);
	                }
	                $tmp = preg_replace( '/\[url\=(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)\](\w+.*?)\[\/url\]/i', '$2 $1', $tmp);
	                $tmp = preg_replace( '/\[bookmark\=(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)\](\w+.*?)\[\/bookmark\]/i', '$2 $1', $tmp);
	                // find all http or https links in the body of the entry and
	                // apply the shortener if the link is longer then 20 characters
	                if (( strlen($tmp)>$max_char ) && ( $max_char > 0 )) {
	                    preg_match_all ( '/(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)/i', $tmp, $allurls  );
	                    foreach ($allurls as $url) {
	                        foreach ($url as $u) {
	                            if (strlen($u)>20) {
	                                $sl = short_link($u);
	                                $tmp = str_replace( $u, $sl, $tmp );
	                            }
	                        }
	                    }
	                }
	                // ok, all the links we want to send out are save, now strip 
	                // away the remaining bbcode
			//$msg = strip_tags(bbcode($tmp, false, false));
			$msg = bbcode($tmp, false, false, true);
			$msg = str_replace(array('<br>','<br />'),"\n",$msg);
			$msg = strip_tags($msg);

			// quotes not working - let's try this
			$msg = html_entity_decode($msg);
			if (( strlen($msg) > $max_char) && $max_char > 0) {
				$shortlink = short_link( $b['plink'] );
				// the new message will be shortened such that "... $shortlink"
				// will fit into the character limit
				$msg = nl2br(substr($msg, 0, $max_char-strlen($shortlink)-4));
        	                $msg = str_replace(array('<br>','<br />'),' ',$msg);
	                        $e = explode(' ', $msg);
	                        //  remove the last word from the cut down message to 
	                        //  avoid sending cut words to the MicroBlog
	                        array_pop($e);
	                        $msg = implode(' ', $e);
				$msg .= '... ' . $shortlink;
			}

			$msg = trim($msg);
			$image = "";
		} else {
			$msgarr = twitter_shortenmsg($b);
                        $msg = $msgarr["msg"];
                        $image = $msgarr["image"];
		}
		// and now tweet it :-)
		if(strlen($msg) and ($image != "")) {
			$img_str = fetch_url($image);

			$tempfile = tempnam(get_config("system","temppath"), "cache");
			file_put_contents($tempfile, $img_str);

			// Twitter had changed something so that the old library doesn't work anymore
			// so we are using a new library for twitter
			// To-Do:
			// Switching completely to this library with all functions
		        require_once("addon/twitter/codebird.php");

			$cb = \Codebird\Codebird::getInstance();
			$cb->setConsumerKey($ckey, $csecret);
			$cb->setToken($otoken, $osecret);

			$post = array('status' => $msg, 'media[]' => $tempfile);

			if ($iscomment)
				$post["in_reply_to_status_id"] = substr($orig_post["uri"], 9);

			$result = $cb->statuses_updateWithMedia($post);
			unlink($tempfile);

			/*
			// Old Code
			$mime = image_type_to_mime_type(exif_imagetype($tempfile));
			unlink($tempfile);

			$filename = "upload";

			$result = $tweet->post('statuses/update_with_media', array('media[]' => "{$img_str};type=".$mime.";filename={$filename}" , 'status' => $msg));
			*/

			logger('twitter_post_with_media send, result: ' . print_r($result, true), LOGGER_DEBUG);
			if ($result->errors OR $result->error) {
				logger('Send to Twitter failed: "' . print_r($result->errors, true) . '"');

				// Workaround: Remove the picture link so that the post can be reposted without it
				$msg .= " ".$image;
				$image = "";
			} elseif ($iscomment) {
				logger('twitter_post: Update extid '.$result->id_str." for post id ".$b['id']);
				q("UPDATE `item` SET `extid` = '%s', `body` = '%s' WHERE `id` = %d",
					dbesc("twitter::".$result->id_str),
					dbesc($result->text),
					intval($b['id'])
				);
			}
		}

		if(strlen($msg) and ($image == "")) {
			$url = 'statuses/update';
			$post = array('status' => $msg);

			if ($iscomment)
				$post["in_reply_to_status_id"] = substr($orig_post["uri"], 9);

			$result = $tweet->post($url, $post);
			logger('twitter_post send, result: ' . print_r($result, true), LOGGER_DEBUG);
			if ($result->errors) {
				logger('Send to Twitter failed: "' . print_r($result->errors, true) . '"');

				$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", intval($b['uid']));
				if (count($r))
					$a->contact = $r[0]["id"];

				$s = serialize(array('url' => $url, 'item' => $b['id'], 'post' => $post));
				require_once('include/queue_fn.php');
				add_to_queue($a->contact,NETWORK_TWITTER,$s);
				notice(t('Twitter post failed. Queued for retry.').EOL);
			} elseif ($iscomment) {
				logger('twitter_post: Update extid '.$result->id_str." for post id ".$b['id']);
				q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d",
					dbesc("twitter::".$result->id_str),
					intval($b['id'])
				);
				//q("UPDATE `item` SET `extid` = '%s', `body` = '%s' WHERE `id` = %d",
				//	dbesc("twitter::".$result->id_str),
				//	dbesc($result->text),
				//	intval($b['id'])
				//);
			}
		}
	}
}

function twitter_plugin_admin_post(&$a){
	$consumerkey	=	((x($_POST,'consumerkey'))		? notags(trim($_POST['consumerkey']))	: '');
	$consumersecret	=	((x($_POST,'consumersecret'))	? notags(trim($_POST['consumersecret'])): '');
        $applicationname = ((x($_POST, 'applicationname')) ? notags(trim($_POST['applicationname'])):'');
	set_config('twitter','consumerkey',$consumerkey);
	set_config('twitter','consumersecret',$consumersecret);
	set_config('twitter','application_name',$applicationname);
	info( t('Settings updated.'). EOL );
}
function twitter_plugin_admin(&$a, &$o){
	$t = get_markup_template( "admin.tpl", "addon/twitter/" );

	$o = replace_macros($t, array(
		'$submit' => t('Save Settings'),
								// name, label, value, help, [extra values]
		'$consumerkey' => array('consumerkey', t('Consumer key'),  get_config('twitter', 'consumerkey' ), ''),
                '$consumersecret' => array('consumersecret', t('Consumer secret'),  get_config('twitter', 'consumersecret' ), ''),
                '$applicationname' => array('applicationname', t('Name of the Twitter Application'), get_config('twitter','application_name'),t('set this to avoid mirroring postings from ~friendica back to ~friendica'))
	));
}

function twitter_cron($a,$b) {
	$last = get_config('twitter','last_poll');

	$poll_interval = intval(get_config('twitter','poll_interval'));
	if(! $poll_interval)
		$poll_interval = TWITTER_DEFAULT_POLL_INTERVAL;

	if($last) {
		$next = $last + ($poll_interval * 60);
		if($next > time()) {
			logger('twitter: poll intervall not reached');
			return;
		}
	}
	logger('twitter: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'mirror_posts' AND `v` = '1' ORDER BY RAND()");
	if(count($r)) {
		foreach($r as $rr) {
			logger('twitter: fetching for user '.$rr['uid']);
			twitter_fetchtimeline($a, $rr['uid']);
		}
	}


	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'import' AND `v` = '1' ORDER BY RAND()");
	if(count($r)) {
		foreach($r as $rr) {
			logger('twitter: importing timeline from user '.$rr['uid']);
			twitter_fetchhometimeline($a, $rr["uid"]);

/*
			// To-Do
                        // check for new contacts once a day
                        $last_contact_check = get_pconfig($rr['uid'],'pumpio','contact_check');
                        if($last_contact_check)
                                $next_contact_check = $last_contact_check + 86400;
                        else
                                $next_contact_check = 0;

                        if($next_contact_check <= time()) {
                                pumpio_getallusers($a, $rr["uid"]);
                                set_pconfig($rr['uid'],'pumpio','contact_check',time());
                        }
*/

                }
        }

	logger('twitter: cron_end');

	set_config('twitter','last_poll', time());
}

function twitter_fetchtimeline($a, $uid) {
	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');
	$lastid  = get_pconfig($uid, 'twitter', 'lastid');

	$application_name  = get_config('twitter', 'application_name');

	if ($application_name == "")
		$application_name = $a->get_hostname();

	require_once('mod/item.php');

	require_once('library/twitteroauth.php');
	$connection = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);

	$parameters = array("exclude_replies" => true, "trim_user" => false, "contributor_details" => true, "include_rts" => true);

	$first_time = ($lastid == "");

	if ($lastid <> "")
		$parameters["since_id"] = $lastid;

	$items = $connection->get('statuses/user_timeline', $parameters);

	if (!is_array($items))
		return;

	$posts = array_reverse($items);

        if (count($posts)) {
	    foreach ($posts as $post) {
		if ($post->id_str > $lastid)
			$lastid = $post->id_str;

		if ($first_time)
			continue;

		if (!strpos($post->source, $application_name)) {
			$_SESSION["authenticated"] = true;
			$_SESSION["uid"] = $uid;

			unset($_REQUEST);
			$_REQUEST["type"] = "wall";
			$_REQUEST["api_source"] = true;
			$_REQUEST["profile_uid"] = $uid;
			$_REQUEST["source"] = "Twitter";

			//$_REQUEST["date"] = $post->created_at;

			$_REQUEST["title"] = "";

			if (is_object($post->retweeted_status)) {

				$_REQUEST['body'] = $post->retweeted_status->text;

				// media
				if (is_array($post->retweeted_status->entities->media)) {
					foreach($post->retweeted_status->entities->media AS $media) {
						switch($media->type) {
							case 'photo':
								$_REQUEST['body'] = str_replace($media->url, "\n\n[img]".$media->media_url_https."[/img]\n", $_REQUEST['body']);
								break;
						}
					}
				}

				$converted = twitter_convertmsg($a, $_REQUEST['body'], true);
				$_REQUEST['body'] = $converted["body"];

				$_REQUEST['body'] = "[share author='".$post->retweeted_status->user->name.
					"' profile='https://twitter.com/".$post->retweeted_status->user->screen_name.
					"' avatar='".$post->retweeted_status->user->profile_image_url_https.
					"' link='https://twitter.com/".$post->retweeted_status->user->screen_name."/status/".$post->retweeted_status->id_str."']".
					$_REQUEST['body'];
				$_REQUEST['body'] .= "[/share]";
			} else {
				$_REQUEST["body"] = $post->text;

				if (is_array($post->entities->media)) {
					foreach($post->entities->media AS $media) {
						switch($media->type) {
							case 'photo':
								$_REQUEST['body'] = str_replace($media->url, "\n\n[img]".$media->media_url_https."[/img]\n", $_REQUEST['body']);
								break;
						}
					}
				}

				$converted = twitter_convertmsg($a, $_REQUEST["body"], true);
				$_REQUEST['body'] = $converted["body"];
			}

			if (is_string($post->place->name))
				$_REQUEST["location"] = $post->place->name;

			if (is_string($post->place->full_name))
				$_REQUEST["location"] = $post->place->full_name;

			if (is_array($post->geo->coordinates))
				$_REQUEST["coord"] = $post->geo->coordinates[0]." ".$post->geo->coordinates[1];

			if (is_array($post->coordinates->coordinates))
				$_REQUEST["coord"] = $post->coordinates->coordinates[1]." ".$post->coordinates->coordinates[0];

			//print_r($_REQUEST);
			logger('twitter: posting for user '.$uid);

//			require_once('mod/item.php');

			item_post($a);
                }
            }
	}
	set_pconfig($uid, 'twitter', 'lastid', $lastid);
}

function twitter_queue_hook(&$a,&$b) {

	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		dbesc(NETWORK_TWITTER)
		);
	if(! count($qi))
		return;

	require_once('include/queue_fn.php');

	foreach($qi as $x) {
		if($x['network'] !== NETWORK_TWITTER)
			continue;

		logger('twitter_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid` 
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);
		if(! count($r))
			continue;

		$user = $r[0];

		$ckey    = get_config('twitter', 'consumerkey');
		$csecret = get_config('twitter', 'consumersecret');
		$otoken  = get_pconfig($user['uid'], 'twitter', 'oauthtoken');
		$osecret = get_pconfig($user['uid'], 'twitter', 'oauthsecret');

		$success = false;

		if ($ckey AND $csecret AND $otoken AND $osecret) {

			logger('twitter_queue: able to post');

			$z = unserialize($x['content']);

			require_once("addon/twitter/codebird.php");

			$cb = \Codebird\Codebird::getInstance();
			$cb->setConsumerKey($ckey, $csecret);
			$cb->setToken($otoken, $osecret);

			if ($z['url'] == "statuses/update")
				$result = $cb->statuses_update($z['post']);

			logger('twitter_queue: post result: ' . print_r($result, true), LOGGER_DEBUG);

			if ($result->errors)
				logger('twitter_queue: Send to Twitter failed: "' . print_r($result->errors, true) . '"');
			else {
				$success = true;
				remove_queue_item($x['id']);
			}
		} else
			logger("twitter_queue: Error getting tokens for user ".$user['uid']);

		if (!$success) {
			logger('twitter_queue: delayed');
			update_queue_time($x['id']);
		}
	}
}

function twitter_fetch_contact($uid, $contact, $create_user) {

	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid), dbesc("twitter::".$contact->id_str));

	if(!count($r) AND !$create_user)
		return(0);

	if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
		logger("twitter_fetch_contact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
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
			dbesc("https://twitter.com/".$contact->screen_name),
			dbesc(normalise_link("https://twitter.com/".$contact->screen_name)),
			dbesc($contact->screen_name."@twitter.com"),
			dbesc("twitter::".$contact->id_str),
			dbesc(''),
			dbesc("twitter::".$contact->id_str),
			dbesc($contact->name),
			dbesc($contact->screen_name),
			dbesc($contact->profile_image_url_https),
			dbesc(NETWORK_TWITTER),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
			dbesc("twitter::".$contact->id_str),
			intval($uid)
			);

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

		$photos = import_profile_photo($contact->profile_image_url_https,$uid,$contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`name-date` = '%s',
					`uri-date` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d",
			dbesc($photos[0]),
			dbesc($photos[1]),
			dbesc($photos[2]),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			intval($contact_id)
		);
	} else {
		// update profile photos once every two weeks as we have no notification of when they change.

		//$update_photo = (($r[0]['avatar-date'] < datetime_convert('','','now -2 days')) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion

		if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {

			logger("twitter_fetch_contact: Updating contact ".$contact->screen_name, LOGGER_DEBUG);

			require_once("Photo.php");

			$photos = import_profile_photo($contact->profile_image_url_https, $uid, $r[0]['id']);

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
				dbesc("https://twitter.com/".$contact->screen_name),
				dbesc(normalise_link("https://twitter.com/".$contact->screen_name)),
				dbesc($contact->screen_name."@twitter.com"),
				dbesc($contact->name),
				dbesc($contact->screen_name),
				intval($r[0]['id'])
			);
		}
	}

	return($r[0]["id"]);
}

function twitter_fetchuser($a, $uid, $screen_name = "", $user_id = "") {
	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');

	require_once("addon/twitter/codebird.php");

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

	$contact_id = twitter_fetch_contact($uid, $user, true);

	return $contact_id;
}

function twitter_createpost($a, $uid, $post, $self, $create_user, $only_existing_contact) {
	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['uri'] = "twitter::".$post->id_str;

	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
			dbesc($postarray['uri']),
			intval($uid)
		);

	if (count($r))
		return(array());

	$contactid = 0;

	if ($post->in_reply_to_status_id_str != "") {

		$parent = "twitter::".$post->in_reply_to_status_id_str;

		$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($parent),
				intval($uid)
			);
		if (count($r)) {
			$postarray['thr-parent'] = $r[0]["uri"];
			$postarray['parent-uri'] = $r[0]["parent-uri"];
		} else {
			$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($parent),
					intval($uid)
				);
			if (count($r)) {
				$postarray['thr-parent'] = $r[0]['uri'];
				$postarray['parent-uri'] = $r[0]['parent-uri'];
			} else {
				$postarray['thr-parent'] = $postarray['uri'];
				$postarray['parent-uri'] = $postarray['uri'];
			}
		}

		// Is it me?
		$own_id = get_pconfig($uid, 'twitter', 'own_id');

		if ($post->user->id_str == $own_id) {
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
	} else
		$postarray['parent-uri'] = $postarray['uri'];

	if ($contactid == 0) {
		$contactid = twitter_fetch_contact($uid, $post->user, $create_user);

		$postarray['owner-name'] = $post->user->name;
		$postarray['owner-link'] = "https://twitter.com/".$post->user->screen_name;
		$postarray['owner-avatar'] = $post->user->profile_image_url_https;
	}

	if(($contactid == 0) AND !$only_existing_contact)
		$contactid = $self['id'];
	elseif ($contactid <= 0)
		return(array());

	$postarray['contact-id'] = $contactid;

	$postarray['verb'] = ACTIVITY_POST;
	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	$postarray['author-avatar'] = $postarray['owner-avatar'];
	$postarray['plink'] = "https://twitter.com/".$post->user->screen_name."/status/".$post->id_str;
	$postarray['app'] = strip_tags($post->source);

	if ($post->user->protected) {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self['id'] . '>';
	}

	$postarray['body'] = $post->text;

	// media
	if (is_array($post->entities->media)) {
		foreach($post->entities->media AS $media) {
			switch($media->type) {
				case 'photo':
					$postarray['body'] = str_replace($media->url, "\n\n[img]".$media->media_url_https."[/img]\n", $postarray['body']);
					break;
				default:
					$postarray['body'] .= print_r($media, true);
			}
		}
	}

	$converted = twitter_convertmsg($a, $postarray['body']);
	$postarray['body'] = $converted["body"];
	$postarray['tag'] = $converted["tags"];

	$postarray['created'] = datetime_convert('UTC','UTC',$post->created_at);
	$postarray['edited'] = datetime_convert('UTC','UTC',$post->created_at);

	if (is_string($post->place->name))
		$postarray["location"] = $post->place->name;

	if (is_string($post->place->full_name))
		$postarray["location"] = $post->place->full_name;

	if (is_array($post->geo->coordinates))
		$postarray["coord"] = $post->geo->coordinates[0]." ".$post->geo->coordinates[1];

	if (is_array($post->coordinates->coordinates))
		$postarray["coord"] = $post->coordinates->coordinates[1]." ".$post->coordinates->coordinates[0];

	if (is_object($post->retweeted_status)) {

		$postarray['body'] = $post->retweeted_status->text;

		// media
		if (is_array($post->retweeted_status->entities->media)) {
			foreach($post->retweeted_status->entities->media AS $media) {
				switch($media->type) {
					case 'photo':
						$postarray['body'] = str_replace($media->url, "\n\n[img]".$media->media_url_https."[/img]\n", $postarray['body']);
						break;
					default:
						$postarray['body'] .= print_r($media, true);
				}
			}
		}

		$converted = twitter_convertmsg($a, $postarray['body']);
		$postarray['body'] = $converted["body"];
		$postarray['tag'] = $converted["tags"];


		// Deactivated at the moment, since there are problems with answers to retweets
		if (false AND !intval(get_config('system','wall-to-wall_share'))) {
			$postarray['body'] = "[share author='".$post->retweeted_status->user->name.
				"' profile='https://twitter.com/".$post->retweeted_status->user->screen_name.
				"' avatar='".$post->retweeted_status->user->profile_image_url_https.
				"' link='https://twitter.com/".$post->retweeted_status->user->screen_name."/status/".$post->retweeted_status->id_str."']".
				$postarray['body'];
			$postarray['body'] .= "[/share]";
		} else {
			// Let retweets look like wall-to-wall posts
			$postarray['author-name'] = $post->retweeted_status->user->name;
			$postarray['author-link'] = "https://twitter.com/".$post->retweeted_status->user->screen_name;
			$postarray['author-avatar'] = $post->retweeted_status->user->profile_image_url_https;
		}

	}
	return($postarray);
}

function twitter_checknotification($a, $uid, $own_id, $top_item, $postarray) {

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
			dbesc("twitter::".$own_id)
		);

	if(!count($own_user))
		return;

	// Is it me from twitter?
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
				'link'             => $a->get_baseurl() . '/display/' . $user[0]['nickname'] . '/' . $top_item,
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

function twitter_fetchhometimeline($a, $uid) {
	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');
	$create_user = get_pconfig($uid, 'twitter', 'create_user');

	logger("twitter_fetchhometimeline: Fetching for user ".$uid, LOGGER_DEBUG);

	require_once('library/twitteroauth.php');
	require_once('include/items.php');

	$connection = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);

	$own_contact = twitter_fetch_own_contact($a, $uid);

	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d LIMIT 1",
		intval($own_contact),
		intval($uid));

	if(count($r)) {
		$own_id = $r[0]["nick"];
	} else {
		logger("twitter_fetchhometimeline: Own twitter contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if(count($r)) {
		$self = $r[0];
	} else {
		logger("twitter_fetchhometimeline: Own contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$u = q("SELECT * FROM user WHERE uid = %d LIMIT 1",
		intval($uid));
	if(!count($u)) {
		logger("twitter_fetchhometimeline: Own user not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

	$parameters = array("exclude_replies" => false, "trim_user" => false, "contributor_details" => true, "include_rts" => true);
	//$parameters["count"] = 200;


	// Fetching timeline
	$lastid  = get_pconfig($uid, 'twitter', 'lasthometimelineid');

	$first_time = ($lastid == "");

	if ($lastid <> "")
		$parameters["since_id"] = $lastid;

	$items = $connection->get('statuses/home_timeline', $parameters);

	if (!is_array($items)) {
		logger("twitter_fetchhometimeline: Error fetching home timeline: ".print_r($items, true), LOGGER_DEBUG);
		return;
	}

        $posts = array_reverse($items);

	logger("twitter_fetchhometimeline: Fetching timeline for user ".$uid." ".sizeof($posts)." items", LOGGER_DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid)
				$lastid = $post->id_str;

			if ($first_time)
				continue;

			$postarray = twitter_createpost($a, $uid, $post, $self, $create_user, true);

			if (trim($postarray['body']) == "")
				continue;

			$item = item_store($postarray);

			logger('twitter_fetchhometimeline: User '.$self["nick"].' posted home timeline item '.$item);

			if ($item != 0)
				twitter_checknotification($a, $uid, $own_id, $item, $postarray);

		}
	}
	set_pconfig($uid, 'twitter', 'lasthometimelineid', $lastid);

	// Fetching mentions
	$lastid  = get_pconfig($uid, 'twitter', 'lastmentionid');

	$first_time = ($lastid == "");

	if ($lastid <> "")
		$parameters["since_id"] = $lastid;

	$items = $connection->get('statuses/mentions_timeline', $parameters);

	if (!is_array($items)) {
		logger("twitter_fetchhometimeline: Error fetching mentions: ".print_r($items, true), LOGGER_DEBUG);
		return;
	}

        $posts = array_reverse($items);

	logger("twitter_fetchhometimeline: Fetching mentions for user ".$uid." ".sizeof($posts)." items", LOGGER_DEBUG);

	if (count($posts)) {
		foreach ($posts as $post) {
			if ($post->id_str > $lastid)
				$lastid = $post->id_str;

			if ($first_time)
				continue;

			$postarray = twitter_createpost($a, $uid, $post, $self, false, false);

			if (trim($postarray['body']) == "")
				continue;

			$item = item_store($postarray);

			logger('twitter_fetchhometimeline: User '.$self["nick"].' posted mention timeline item '.$item);

			if ($item == 0) {
				$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
					dbesc($postarray['uri']),
					intval($uid)
				);
				if (count($r))
					$item = $r[0]['id'];
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
			                'link'         => $a->get_baseurl() . '/display/' . $u[0]['nickname'] . '/' . $item,
			                'source_name'  => $postarray['author-name'],
			                'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
			                'verb'         => ACTIVITY_TAG,
			                'otype'        => 'item'
			        ));
			}
		}
	}

	set_pconfig($uid, 'twitter', 'lastmentionid', $lastid);
}

function twitter_original_url($url, $depth=1, $fetchbody = false) {
        if ($depth > 10)
                return($url);

        $siteinfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);

	if ($fetchbody)
	        curl_setopt($ch, CURLOPT_NOBODY, 0);
	else
	        curl_setopt($ch, CURLOPT_NOBODY, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0');

        $header = curl_exec($ch);
        $curl_info = @curl_getinfo($ch);
        $http_code = $curl_info['http_code'];
        curl_close($ch);

        if ((($curl_info['http_code'] == "301") OR ($curl_info['http_code'] == "302"))
                AND (($curl_info['redirect_url'] != "") OR ($curl_info['location'] != ""))) {
                if ($curl_info['redirect_url'] != "")
                        return(twitter_original_url($curl_info['redirect_url'], ++$depth, $fetchbody));
                else
                        return(twitter_original_url($curl_info['location'], ++$depth, $fetchbody));
        }

        $pos = strpos($header, "\r\n\r\n");

        if ($pos)
                $body = trim(substr($header, $pos));
        else
                $body = $header;

	if (trim($body) == "")
		return(twitter_original_url($url, ++$depth, true));

        $doc = new DOMDocument();
        @$doc->loadHTML($body);

        $xpath = new DomXPath($doc);

        $list = $xpath->query("//meta[@content]");
        foreach ($list as $node) {
                $attr = array();
                if ($node->attributes->length)
                        foreach ($node->attributes as $attribute)
                                $attr[$attribute->name] = $attribute->value;

                if (@$attr["http-equiv"] == 'refresh') {
                        $path = $attr["content"];
                        $pathinfo = explode(";", $path);
                        $content = "";
                        foreach ($pathinfo AS $value)
                                if (substr(strtolower($value), 0, 4) == "url=")
                                        return(twitter_original_url(substr($value, 4), ++$depth));
                }
        }

        return($url);
}

function twitter_siteinfo($url) {
	require_once("mod/parse_url.php");

	$data = parseurl_getsiteinfo($url);

	if (!is_string($data["text"]) AND (sizeof($data["images"]) == 0) AND ($data["title"] == $url))
		return("");

	if (is_string($data["title"]))
		$text .= "[bookmark=".$url."]".trim($data["title"])."[/bookmark]\n";

	if (sizeof($data["images"]) > 0) {
		$imagedata = $data["images"][0];
		$text .= '[img='.$imagedata["width"].'x'.$imagedata["height"].']'.$imagedata["src"].'[/img]' . "\n";
	}

	if (is_string($data["text"]))
		$text .= "[quote]".$data["text"]."[/quote]";

	return($text);

}

function twitter_convertmsg($a, $body, $no_tags = false) {

	$links = preg_match_all("/([^\]\='".'"'."]|^)(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)/ism", $body,$matches,PREG_SET_ORDER);

	$footer = "";

	if ($links) {
		foreach ($matches AS $match) {
			$expanded_url = twitter_original_url($match[2]);

			// To-Do:
			// Twitlonger

			if (strstr($expanded_url, "//www.youtube.com/"))
				$body = str_replace($match[2], "\n[youtube]".$expanded_url."[/youtube]\n", $body);
			elseif (strstr($expanded_url, "//player.vimeo.com/"))
				$body = str_replace($match[2], "\n[vimeo]".$expanded_url."[/vimeo]\n", $body);
			elseif (strstr($expanded_url, "//instagram.com"))
				$body = str_replace($match[2], "\n[url]".$expanded_url."[/url]\n", $body);
			else {
				$img_str = fetch_url($expanded_url, true, $redirects, 4);

				$tempfile = tempnam(get_config("system","temppath"), "cache");
				file_put_contents($tempfile, $img_str);
				$mime = image_type_to_mime_type(exif_imagetype($tempfile));
				unlink($tempfile);

				if (substr($mime, 0, 6) == "image/")
					$body = str_replace($match[2], "[img]".$expanded_url."[/img]", $body);
				else {

					//if ($footer == "")
					$footer = "\n\n".twitter_siteinfo($expanded_url);
					$footerlink = "[url=".$expanded_url."]".$expanded_url."[/url]";

					$body = str_replace($match[2], $footerlink, $body);
				}
			}
		}

		if (($footerlink != "") AND (trim($footer) != "")) {
			$removedlink = trim(str_replace($footerlink, "", $body));

			if (strstr($body, $removedlink))
				$body = $removedlink;

			$body .= $footer;
		}
	}

	if ($no_tags)
		return(array("body" => $body, $tags => ""));

	$str_tags = '';

        $tags = get_tags($body);

        if(count($tags)) {
                foreach($tags as $tag) {
			if (strstr(trim($tag), " "))
				continue;

                        if(strpos($tag,'#') === 0) {
                                if(strpos($tag,'[url='))
                                        continue;

                                // don't link tags that are already embedded in links

                                if(preg_match('/\[(.*?)' . preg_quote($tag,'/') . '(.*?)\]/',$body))
                                        continue;
                                if(preg_match('/\[(.*?)\]\((.*?)' . preg_quote($tag,'/') . '(.*?)\)/',$body))
                                        continue;

                                $basetag = str_replace('_',' ',substr($tag,1));
                                $body = str_replace($tag,'#[url=' . $a->get_baseurl() . '/search?tag=' . rawurlencode($basetag) . ']' . $basetag . '[/url]',$body);
                                if(strlen($str_tags))
                                        $str_tags .= ',';
                                $str_tags .= '#[url=' . $a->get_baseurl() . '/search?tag=' . rawurlencode($basetag) . ']' . $basetag . '[/url]';
                                continue;
                        } elseif(strpos($tag,'@') === 0) {
                                $basetag = substr($tag,1);
                                $body = str_replace($tag,'@[url=https://twitter.com/' . rawurlencode($basetag) . ']' . $basetag . '[/url]',$body);
			}

                }
        }

        $cnt = preg_match_all('/@\[url=(.*?)\[\/url\]/ism',$body,$matches,PREG_SET_ORDER);
        if($cnt) {
                foreach($matches as $mtch) {
                        if(strlen($str_tags))
                                $str_tags .= ',';
                        $str_tags .= '@[url=' . $mtch[1] . '[/url]';
                }
        }

	return(array("body"=>$body, "tags"=>$str_tags));

}

function twitter_fetch_own_contact($a, $uid) {
	$ckey    = get_config('twitter', 'consumerkey');
	$csecret = get_config('twitter', 'consumersecret');
	$otoken  = get_pconfig($uid, 'twitter', 'oauthtoken');
	$osecret = get_pconfig($uid, 'twitter', 'oauthsecret');

	$own_id = get_pconfig($uid, 'twitter', 'own_id');

	$contact_id = 0;

	if ($own_id == "") {
		require_once('library/twitteroauth.php');

		$connection = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);

		// Fetching user data
		$user = $connection->get('account/verify_credentials');

		set_pconfig($uid, 'twitter', 'own_id', $user->id_str);

		$contact_id = twitter_fetch_contact($uid, $user, true);

	} else {
	        $r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
        	        intval($uid), dbesc("twitter::".$own_id));
		if(count($r))
			$contact_id = $r[0]["id"];
	}

	return($contact_id);
}
?>
