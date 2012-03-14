<?php
/**
 * Name: Twitter Connector
 * Description: Relay public postings to a connected Twitter account
 * Version: 1.0.2
 * Author: Tobias Diekershoff <https://diekershoff.homeunix.net/friendika/profile/tobias>
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
 *
 *     Documentation: http://diekershoff.homeunix.net/redmine/wiki/friendikaplugin/Twitter_Plugin
 */

function twitter_install() {
	//  we need some hooks, for the configuration and for sending tweets
	register_hook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	register_hook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	register_hook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	register_hook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	register_hook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');
	logger("installed twitter");
}


function twitter_uninstall() {
	unregister_hook('connector_settings', 'addon/twitter/twitter.php', 'twitter_settings'); 
	unregister_hook('connector_settings_post', 'addon/twitter/twitter.php', 'twitter_settings_post');
	unregister_hook('post_local', 'addon/twitter/twitter.php', 'twitter_post_local');
	unregister_hook('notifier_normal', 'addon/twitter/twitter.php', 'twitter_post_hook');
	unregister_hook('jot_networks', 'addon/twitter/twitter.php', 'twitter_jot_nets');

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
		del_pconfig( local_user(), 'twitter', 'consumerkey'  );
		del_pconfig( local_user(), 'twitter', 'consumersecret' );
                del_pconfig( local_user(), 'twitter', 'oauthtoken'  );  
                del_pconfig( local_user(), 'twitter', 'oauthsecret'  );  
                del_pconfig( local_user(), 'twitter', 'post' );
                del_pconfig( local_user(), 'twitter', 'post_by_default' );
	} else {
	if (isset($_POST['twitter-pin'])) {
		//  if the user supplied us with a PIN from Twitter, let the magic of OAuth happen
		logger('got a Twitter PIN');
		require_once('library/twitteroauth.php');
		$ckey    = get_config('twitter', 'consumerkey'  );
		$csecret = get_config('twitter', 'consumersecret' );
		//  the token and secret for which the PIN was generated were hidden in the settings
		//  form as token and token2, we need a new connection to Twitter using these token
		//  and secret to request a Access Token with the PIN
		$connection = new TwitterOAuth($ckey, $csecret, $_POST['twitter-token'], $_POST['twitter-token2']);
		$token   = $connection->getAccessToken( $_POST['twitter-pin'] );
		//  ok, now that we have the Access Token, save them in the user config
 		set_pconfig(local_user(),'twitter', 'oauthtoken',  $token['oauth_token']);
		set_pconfig(local_user(),'twitter', 'oauthsecret', $token['oauth_token_secret']);
                set_pconfig(local_user(),'twitter', 'post', 1);
                //  reload the Addon Settings page, if we don't do it see Bug #42
                goaway($a->get_baseurl().'/settings/connectors');
	} else {
		//  if no PIN is supplied in the POST variables, the user has changed the setting
		//  to post a tweet for every new __public__ posting to the wall
		set_pconfig(local_user(),'twitter','post',intval($_POST['twitter-enable']));
                set_pconfig(local_user(),'twitter','post_by_default',intval($_POST['twitter-default']));
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
			$s .= '</div><div class="clear"></div>';

			$s .= '<div id="twitter-disconnect-wrapper">';
                        $s .= '<label id="twitter-disconnect-label" for="twitter-disconnect">'. t('Clear OAuth configuration') .'</label>';
                        $s .= '<input id="twitter-disconnect" type="checkbox" name="twitter-disconnect" value="1" />';
			$s .= '</div><div class="clear"></div>';
			$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="twitter-submit" class="settings-submit" value="' . t('Submit') . '" /></div>'; 
		}
	}
        $s .= '</div><div class="clear"></div></div>';
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

	logger('twitter post invoked');


	load_pconfig($b['uid'], 'twitter');

	$ckey    = get_config('twitter', 'consumerkey'  );
	$csecret = get_config('twitter', 'consumersecret' );
	$otoken  = get_pconfig($b['uid'], 'twitter', 'oauthtoken'  );
	$osecret = get_pconfig($b['uid'], 'twitter', 'oauthsecret' );

	if($ckey && $csecret && $otoken && $osecret) {
		logger('twitter: we have customer key and oauth stuff, going to send.', LOGGER_DEBUG);

		require_once('library/twitteroauth.php');
		require_once('include/bbcode.php');	
		$tweet = new TwitterOAuth($ckey,$csecret,$otoken,$osecret);
                // in theory max char is 140 but T. uses t.co to make links 
                // longer so we give them 10 characters extra
		$max_char = 130; // max. length for a tweet
                // we will only work with up to two times the length of the dent 
                // we can later send to Twitter. This way we can "gain" some 
                // information during shortening of potential links but do not 
                // shorten all the links in a 200000 character long essay.
                $tmp = substr($b['body'], 0, 2*$max_char);
                // if [url=bla][img]blub.png[/img][/url] get blub.png
                $tmp = preg_replace( '/\[url\=(https?\:\/\/[a-zA-Z0-9\:\/\-\?\&\;\.\=\_\~\#\%\$\!\+\,]+)\]\[img\](\\w+.*?)\\[\\/img\]\\[\\/url\]/i', '$2', $tmp);
                // preserve links to images, videos and audios
                $tmp = preg_replace( '/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism', '$3', $tmp);
                $tmp = preg_replace( '/\[\\/?img(\\s+.*?\]|\])/i', '', $tmp);
                $tmp = preg_replace( '/\[\\/?video(\\s+.*?\]|\])/i', '', $tmp);
                $tmp = preg_replace( '/\[\\/?youtube(\\s+.*?\]|\])/i', '', $tmp);
                $tmp = preg_replace( '/\[\\/?vimeo(\\s+.*?\]|\])/i', '', $tmp);
                $tmp = preg_replace( '/\[\\/?audio(\\s+.*?\]|\])/i', '', $tmp);
                // if a #tag is linked, don't send the [url] over to SN
                //   this is commented out by default as it means backlinks
                //   to friendica, if you don't like this feel free to
                //   uncomment the following line
//                $tmp = preg_replace( '/#\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '#$2', $tmp);
                // preserve links to webpages
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
		$msg = strip_tags(bbcode($tmp));
		// quotes not working - let's try this
		$msg = html_entity_decode($msg);
		if (( strlen($msg) > $max_char) && $max_char > 0) {
			$shortlink = short_link( $b['plink'] );
			// the new message will be shortened such that "... $shortlink"
			// will fit into the character limit
			$msg = substr($msg, 0, $max_char-strlen($shortlink)-4);
			$msg .= '... ' . $shortlink;
		}
		// and now tweet it :-)
		if(strlen($msg)) {
			$result = $tweet->post('statuses/update', array('status' => $msg));
			logger('twitter_post send' , LOGGER_DEBUG);
		}
	}
}

function twitter_plugin_admin_post(&$a){
	$consumerkey	=	((x($_POST,'consumerkey'))		? notags(trim($_POST['consumerkey']))	: '');
	$consumersecret	=	((x($_POST,'consumersecret'))	? notags(trim($_POST['consumersecret'])): '');
	set_config('twitter','consumerkey',$consumerkey);
	set_config('twitter','consumersecret',$consumersecret);
	info( t('Settings updated.'). EOL );
}
function twitter_plugin_admin(&$a, &$o){
	$t = file_get_contents( dirname(__file__). "/admin.tpl" );
	$o = replace_macros($t, array(
		'$submit' => t('Submit'),
								// name, label, value, help, [extra values]
		'$consumerkey' => array('consumerkey', t('Consumer key'),  get_config('twitter', 'consumerkey' ), ''),
		'$consumersecret' => array('consumersecret', t('Consumer secret'),  get_config('twitter', 'consumersecret' ), '')
	));
}
