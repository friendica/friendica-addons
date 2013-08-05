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
	logger("installed twitter");
}


function twitter_uninstall() {
	unregister_hook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	unregister_hook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	unregister_hook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	unregister_hook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	unregister_hook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	unregister_hook('cron', 'addon/twitter/twitter.php', 'twitter_cron');

	// old setting - remove only
	unregister_hook('post_local_end', 'addon/twitter/twitter.php', 'twitter_post_hook');
	unregister_hook('plugin_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	unregister_hook('plugin_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');

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
	if (!x($_POST,'twitter-submit')) return;
	
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

	$s .= '<div class="settings-block">';
	$s .= '<h3>'. t('Twitter Posting Settings') .'</h3>';

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
            $s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
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

                        $s .= '<label id="twitter-mirror-label" for="twitter-mirror">'.t('Mirror all posts from twitter that are no replies or retweets').'</label>';
                        $s .= '<input id="twitter-mirror" type="checkbox" name="twitter-mirror" value="1" '. $mirrorchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-shortening-label" for="twitter-shortening">'.t('Shortening method that optimizes the tweet').'</label>';
                        $s .= '<input id="twitter-shortening" type="checkbox" name="twitter-shortening" value="1" '. $shorteningchecked . '/>';
			$s .= '<div class="clear"></div>';

                        $s .= '<label id="twitter-sendtaglinks-label" for="twitter-sendtaglinks">'.t('Send linked #-tags and @-names to Twitter').'</label>';
                        $s .= '<input id="twitter-sendtaglinks" type="checkbox" name="twitter-sendtaglinks" value="1" '. $linkschecked . '/>';
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="twitter-disconnect-wrapper">';
                        $s .= '<label id="twitter-disconnect-label" for="twitter-disconnect">'. t('Clear OAuth configuration') .'</label>';
                        $s .= '<input id="twitter-disconnect" type="checkbox" name="twitter-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . t('Submit') . '" /></div>'; 
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

function twitter_shortenmsg($b) {
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
	//$msg = trim($b['title']." \n\n".html2plain($html, 0, true));
	$msg = trim(html2plain($html, 0, true));
	$msg = html_entity_decode($msg,ENT_QUOTES,'UTF-8');

	// Removing multiple newlines
	while (strpos($msg, "\n\n\n") !== false)
		$msg = str_replace("\n\n\n", "\n\n", $msg);

	// Removing multiple spaces
	while (strpos($msg, "  ") !== false)
		$msg = str_replace("  ", " ", $msg);

	$origmsg = $msg;

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
	if ((strlen(trim($origmsg)) <= $max_char) AND ($msglink == ""))
		return(array("msg"=>trim($origmsg), "image"=>""));

	// If the message is short enough and contains a picture then post the picture as well
	if ((strlen(trim($origmsg)) <= ($max_char - 20)) AND strpos($origmsg, $msglink))
		return(array("msg"=>trim($origmsg), "image"=>$image));

	// If the message is short enough and the link exists in the original message don't modify it as well
	// -3 because of the bad shortener of twitter
	if ((strlen(trim($origmsg)) <= ($max_char - 3)) AND strpos($origmsg, $msglink))
		return(array("msg"=>trim($origmsg), "image"=>""));

	// Preserve the unshortened link
	$orig_link = $msglink;

	//if (strlen($msglink) > 20)
	//	$msglink = short_link($msglink);
	//
	//if (strlen(trim($msg." ".$msglink)) > ($max_char - 3)) {
	//	$msg = substr($msg, 0, ($max_char - 3) - (strlen($msglink)));

	// Just replace the message link with a 15 character long string
	// Twitter shortens it anyway to this length
	if (trim($msglink) <> '')
		$msglink = "123456789012345";

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
	//$msg = str_replace("\n", " ", $msg);

	// Removing multiple spaces - again
	while (strpos($msg, "  ") !== false)
		$msg = str_replace("  ", " ", $msg);

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
		return(array("msg"=>trim($msg), "image"=>$orig_link));
	else if (($image != $orig_link) AND ($image != "") AND (strlen($msg."\n".$msglink) <= ($max_char - 20)))
		return(array("msg"=>trim($msg."\n".$orig_link), "image"=>$image));
	else
		return(array("msg"=>trim($msg."\n".$orig_link), "image"=>""));
}

function twitter_post_hook(&$a,&$b) {

	/**
	 * Post to Twitter
	 */

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

	if(! strstr($b['postopts'],'twitter'))
		return;

	if($b['parent'] != $b['id'])
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
			$mime = image_type_to_mime_type(exif_imagetype($tempfile));
			unlink($tempfile);

			$filename = "upload";

			$result = $tweet->post('statuses/update_with_media', array('media[]' => "{$img_str};type=".$mime.";filename={$filename}" , 'status' => $msg));

			logger('twitter_post_with_media send, result: ' . print_r($result, true), LOGGER_DEBUG);
			if ($result->errors OR $result->error) {
				logger('Send to Twitter failed: "' . $result->errors . '"');
				// Workaround: Remove the picture link so that the post can be reposted without it
				$image = "";
			}
		}

		if(strlen($msg) and ($image == "")) {
			$result = $tweet->post('statuses/update', array('status' => $msg));
			logger('twitter_post send, result: ' . print_r($result, true), LOGGER_DEBUG);
			if ($result->errors OR $result->error)
				logger('Send to Twitter failed: "' . $result->errors . '"');
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
		'$submit' => t('Submit'),
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

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'twitter' AND `k` = 'mirror_posts' AND `v` = '1' ORDER BY RAND() ");
	if(count($r)) {
		foreach($r as $rr) {
			logger('twitter: fetching for user '.$rr['uid']);
			twitter_fetchtimeline($a, $rr['uid']);
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

	require_once('library/twitteroauth.php');
	$connection = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);

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

			$_REQUEST["body"] = $post->text;
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

			require_once('mod/item.php');
			item_post($a);

                }
            }
	}
	set_pconfig($uid, 'twitter', 'lastid', $lastid);
}
