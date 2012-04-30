<?php
/**
 * Name: Facebook Connector
 * Version: 1.3
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *         Tobias Hößl <https://github.com/CatoTH/>
 */

/**
 * Installing the Friendica/Facebook connector
 *
 * Detailed instructions how to use this plugin can be found at
 * https://github.com/friendica/friendica/wiki/How-to:-Friendica%E2%80%99s-Facebook-connector
 *
 * Vidoes and embeds will not be posted if there is no other content. Links 
 * and images will be converted to a format suitable for the Facebook API and 
 * long posts truncated - with a link to view the full post. 
 *
 * Facebook contacts will not be able to view private photos, as they are not able to
 * authenticate to your site to establish identity. We will address this 
 * in a future release.
 */
 
 /** TODO
 * - Implement a method for the administrator to delete all configuration data the plugin has created,
 *   e.g. the app_access_token
 */

// Size of maximum post length increased
// see http://www.facebook.com/schrep/posts/203969696349811
// define('FACEBOOK_MAXPOSTLEN', 420);
define('FACEBOOK_MAXPOSTLEN', 63206);
define('FACEBOOK_SESSION_ERR_NOTIFICATION_INTERVAL', 259200); // 3 days
define('FACEBOOK_DEFAULT_POLL_INTERVAL', 60); // given in minutes
define('FACEBOOK_MIN_POLL_INTERVAL', 5);
define('FACEBOOK_RTU_ERR_MAIL_AFTER_MINUTES', 180); // 3 hours

require_once('include/security.php');

function facebook_install() {
	register_hook('post_local',       'addon/facebook/facebook.php', 'facebook_post_local');
	register_hook('notifier_normal',  'addon/facebook/facebook.php', 'facebook_post_hook');
	register_hook('jot_networks',     'addon/facebook/facebook.php', 'facebook_jot_nets');
	register_hook('connector_settings',  'addon/facebook/facebook.php', 'facebook_plugin_settings');
	register_hook('cron',             'addon/facebook/facebook.php', 'facebook_cron');
	register_hook('enotify',          'addon/facebook/facebook.php', 'facebook_enotify');
	register_hook('queue_predeliver', 'addon/facebook/facebook.php', 'fb_queue_hook');
}


function facebook_uninstall() {
	unregister_hook('post_local',       'addon/facebook/facebook.php', 'facebook_post_local');
	unregister_hook('notifier_normal',  'addon/facebook/facebook.php', 'facebook_post_hook');
	unregister_hook('jot_networks',     'addon/facebook/facebook.php', 'facebook_jot_nets');
	unregister_hook('connector_settings',  'addon/facebook/facebook.php', 'facebook_plugin_settings');
	unregister_hook('cron',             'addon/facebook/facebook.php', 'facebook_cron');
	unregister_hook('enotify',          'addon/facebook/facebook.php', 'facebook_enotify');
	unregister_hook('queue_predeliver', 'addon/facebook/facebook.php', 'fb_queue_hook');

	// hook moved
	unregister_hook('post_local_end',  'addon/facebook/facebook.php', 'facebook_post_hook');
	unregister_hook('plugin_settings',  'addon/facebook/facebook.php', 'facebook_plugin_settings');
}


/* declare the facebook_module function so that /facebook url requests will land here */

function facebook_module() {}



// If a->argv[1] is a nickname, this is a callback from Facebook oauth requests.
// If $_REQUEST["realtime_cb"] is set, this is a callback from the Real-Time Updates API

/**
 * @param App $a
 */
function facebook_init(&$a) {

	if (x($_REQUEST, "realtime_cb") && x($_REQUEST, "realtime_cb")) {
		logger("facebook_init: Facebook Real-Time callback called", LOGGER_DEBUG);
		
		if (x($_REQUEST, "hub_verify_token")) {
			// this is the verification callback while registering for real time updates
			
			$verify_token = get_config('facebook', 'cb_verify_token');
			if ($verify_token != $_REQUEST["hub_verify_token"]) {
				logger('facebook_init: Wrong Facebook Callback Verifier - expected ' . $verify_token . ', got ' . $_REQUEST["hub_verify_token"]);
				return;
			}
			
			if (x($_REQUEST, "hub_challenge")) {
				logger('facebook_init: Answering Challenge: ' . $_REQUEST["hub_challenge"], LOGGER_DATA);
				echo $_REQUEST["hub_challenge"];
				die();
			}
		}
		
		require_once('include/items.php');
		
		// this is a status update
		$content = file_get_contents("php://input");
		if (is_numeric($content)) $content = file_get_contents("php://input");
		$js = json_decode($content);
		logger(print_r($js, true), LOGGER_DATA);
		
		if (!isset($js->object) || $js->object != "user" || !isset($js->entry)) {
			logger('facebook_init: Could not parse Real-Time Update data', LOGGER_DEBUG);
			return;
		}
		
		$affected_users = array("feed" => array(), "friends" => array());
		
		foreach ($js->entry as $entry) {
			$fbuser = $entry->uid;
			foreach ($entry->changed_fields as $field) {
				if (!isset($affected_users[$field])) {
					logger('facebook_init: Unknown field "' . $field . '"');
					continue;
				}
				if (in_array($fbuser, $affected_users[$field])) continue;
				
				$r = q("SELECT `uid` FROM `pconfig` WHERE `cat` = 'facebook' AND `k` = 'self_id' AND `v` = '%s' LIMIT 1", dbesc($fbuser));
				if(! count($r))
					continue;
				$uid = $r[0]['uid'];
				
				$access_token = get_pconfig($uid,'facebook','access_token');
				if(! $access_token)
					return;
				
				switch ($field) {
					case "feed":
						logger('facebook_init: FB-User ' . $fbuser . ' / feed', LOGGER_DEBUG);
						
						if(! get_pconfig($uid,'facebook','no_wall')) {
							$private_wall = intval(get_pconfig($uid,'facebook','private_wall'));
							$s = fetch_url('https://graph.facebook.com/me/feed?access_token=' . $access_token);
							if($s) {
								$j = json_decode($s);
								if (isset($j->data)) {
									logger('facebook_init: wall: ' . print_r($j,true), LOGGER_DATA);
									fb_consume_stream($uid,$j,($private_wall) ? false : true);
								} else {
									logger('facebook_init: wall: got no data from Facebook: ' . print_r($j,true), LOGGER_NORMAL);
								}
							}
						}
						
					break;
					case "friends":
						logger('facebook_init: FB-User ' . $fbuser . ' / friends', LOGGER_DEBUG);
						
						fb_get_friends($uid, false);
						set_pconfig($uid,'facebook','friend_check',time());
					break;
					default:
						logger('facebook_init: Unknown callback field for ' . $fbuser, LOGGER_NORMAL);
				}
				$affected_users[$field][] = $fbuser;
			}
		}
	}

	
	if($a->argc != 2)
		return;
	$nick = $a->argv[1];
	if(strlen($nick))
		$r = q("SELECT `uid` FROM `user` WHERE `nickname` = '%s' LIMIT 1",
				dbesc($nick)
		);
	if(!(isset($r) && count($r)))
		return;

	$uid           = $r[0]['uid'];
	$auth_code     = (x($_GET, 'code') ? $_GET['code'] : '');
	$error         = (x($_GET, 'error_description') ? $_GET['error_description'] : '');


	if($error)
		logger('facebook_init: Error: ' . $error);

	if($auth_code && $uid) {

		$appid = get_config('facebook','appid');
		$appsecret = get_config('facebook', 'appsecret');

		$x = fetch_url('https://graph.facebook.com/oauth/access_token?client_id='
			. $appid . '&client_secret=' . $appsecret . '&redirect_uri='
			. urlencode($a->get_baseurl() . '/facebook/' . $nick) 
			. '&code=' . $auth_code);

		logger('facebook_init: returned access token: ' . $x, LOGGER_DATA);

		if(strpos($x,'access_token=') !== false) {
			$token = str_replace('access_token=', '', $x);
 			if(strpos($token,'&') !== false)
				$token = substr($token,0,strpos($token,'&'));
			set_pconfig($uid,'facebook','access_token',$token);
			set_pconfig($uid,'facebook','post','1');
			if(get_pconfig($uid,'facebook','no_linking') === false)
				set_pconfig($uid,'facebook','no_linking',1);
			fb_get_self($uid);
			fb_get_friends($uid, true);
			fb_consume_all($uid);

		}

	}

}


/**
 * @param int $uid
 */
function fb_get_self($uid) {
	$access_token = get_pconfig($uid,'facebook','access_token');
	if(! $access_token)
		return;
	$s = fetch_url('https://graph.facebook.com/me/?access_token=' . $access_token);
	if($s) {
		$j = json_decode($s);
		set_pconfig($uid,'facebook','self_id',(string) $j->id);
	}
}

/**
 * @param int $uid
 * @param string $access_token
 * @param array $persons
 */
function fb_get_friends_sync_new($uid, $access_token, $persons) {
    $persons_todo = array();
    foreach ($persons as $person) {
        $link = 'http://facebook.com/profile.php?id=' . $person->id;

        $r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `url` = '%s' LIMIT 1",
            intval($uid),
            dbesc($link)
        );

        if (count($r) == 0) {
            logger('fb_get_friends: new contact found: ' . $link, LOGGER_DEBUG);
            $persons_todo[] = $person;
        }

        if (count($persons_todo) > 0) fb_get_friends_sync_full($uid, $access_token, $persons_todo);
    }
}

/**
 * @param int $uid
 * @param object $contact
 */
function fb_get_friends_sync_parsecontact($uid, $contact) {
    $contact->link = 'http://facebook.com/profile.php?id=' . $contact->id;

    // If its a page then set the first name from the username
    if (!$contact->first_name and $contact->username)
        $contact->first_name = $contact->username;

    // check if we already have a contact

    $r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `url` = '%s' LIMIT 1",
        intval($uid),
        dbesc($contact->link)
    );

    if(count($r)) {

        // check that we have all the photos, this has been known to fail on occasion

        if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro'])) {
            require_once("Photo.php");

            $photos = import_profile_photo('https://graph.facebook.com/' . $contact->id . '/picture', $uid, $r[0]['id']);

            q("UPDATE `contact` SET `photo` = '%s',
                                        `thumb` = '%s',
                                        `micro` = '%s',
                                        `name-date` = '%s',
                                        `uri-date` = '%s',
                                        `avatar-date` = '%s'
                                        WHERE `id` = %d LIMIT 1
                                ",
                dbesc($photos[0]),
                dbesc($photos[1]),
                dbesc($photos[2]),
                dbesc(datetime_convert()),
                dbesc(datetime_convert()),
                dbesc(datetime_convert()),
                intval($r[0]['id'])
            );
        }
        return;
    }
    else {

        // create contact record
        q("INSERT INTO `contact` ( `uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
                                `name`, `nick`, `photo`, `network`, `rel`, `priority`,
                                `writable`, `blocked`, `readonly`, `pending` )
                                VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0 ) ",
            intval($uid),
            dbesc(datetime_convert()),
            dbesc($contact->link),
            dbesc(normalise_link($contact->link)),
            dbesc(''),
            dbesc(''),
            dbesc($contact->id),
            dbesc('facebook ' . $contact->id),
            dbesc($contact->name),
            dbesc(($contact->nickname) ? $contact->nickname : strtolower($contact->first_name)),
            dbesc('https://graph.facebook.com/' . $contact->id . '/picture'),
            dbesc(NETWORK_FACEBOOK),
            intval(CONTACT_IS_FRIEND),
            intval(1),
            intval(1)
        );
    }

    $r = q("SELECT * FROM `contact` WHERE `url` = '%s' AND `uid` = %d LIMIT 1",
        dbesc($contact->link),
        intval($uid)
    );

    if(! count($r)) {
        return;
    }

    $contact_id  = $r[0]['id'];

    require_once("Photo.php");

    $photos = import_profile_photo($r[0]['photo'],$uid,$contact_id);

    q("UPDATE `contact` SET `photo` = '%s',
                        `thumb` = '%s',
                        `micro` = '%s',
                        `name-date` = '%s',
                        `uri-date` = '%s',
                        `avatar-date` = '%s'
                        WHERE `id` = %d LIMIT 1
                ",
        dbesc($photos[0]),
        dbesc($photos[1]),
        dbesc($photos[2]),
        dbesc(datetime_convert()),
        dbesc(datetime_convert()),
        dbesc(datetime_convert()),
        intval($contact_id)
    );
}

/**
 * @param int $uid
 * @param string $access_token
 * @param array $persons
 */
function fb_get_friends_sync_full($uid, $access_token, $persons) {
    if (count($persons) == 0) return;
    $nums = Ceil(count($persons) / 50);
    for ($i = 0; $i < $nums; $i++) {
        $batch_request = array();
        for ($j = $i * 50; $j < ($i+1) * 50 && $j < count($persons); $j++) $batch_request[] = array('method'=>'GET', 'relative_url'=>$persons[$j]->id);
        $s = post_url('https://graph.facebook.com/', array('access_token' => $access_token, 'batch' => json_encode($batch_request)));
        if($s) {
            $results = json_decode($s);
            logger('fb_get_friends: info: ' . print_r($results,true), LOGGER_DATA);
            foreach ($results as $contact) {
                if ($contact->code != 200) logger('fb_get_friends: not found: ' . print_r($contact,true), LOGGER_DEBUG);
                else fb_get_friends_sync_parsecontact($uid, json_decode($contact->body));
            }
        }
    }
}



// if $fullsync is true, only new contacts are searched for

/**
 * @param int $uid
 * @param bool $fullsync
 */
function fb_get_friends($uid, $fullsync = true) {

	$r = q("SELECT `uid` FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
		intval($uid)
	);
	if(! count($r))
		return;

	$access_token = get_pconfig($uid,'facebook','access_token');

	$no_linking = get_pconfig($uid,'facebook','no_linking');
	if($no_linking)
		return;

	if(! $access_token)
		return;
	$s = fetch_url('https://graph.facebook.com/me/friends?access_token=' . $access_token);
	if($s) {
		logger('facebook: fb_get_friends: ' . $s, LOGGER_DATA);
		$j = json_decode($s);
		logger('facebook: fb_get_friends: json: ' . print_r($j,true), LOGGER_DATA);
		if(! $j->data)
			return;

	    $persons_todo = array();
        foreach($j->data as $person) $persons_todo[] = $person;

        if ($fullsync)
            fb_get_friends_sync_full($uid, $access_token, $persons_todo);
        else
            fb_get_friends_sync_new($uid, $access_token, $persons_todo);
	}
}

// This is the POST method to the facebook settings page
// Content is posted to Facebook in the function facebook_post_hook() 

/**
 * @param App $a
 */
function facebook_post(&$a) {

	$uid = local_user();
	if($uid){

		$value = ((x($_POST,'post_by_default')) ? intval($_POST['post_by_default']) : 0);
		set_pconfig($uid,'facebook','post_by_default', $value);

		$no_linking = get_pconfig($uid,'facebook','no_linking');

		$no_wall = ((x($_POST,'facebook_no_wall')) ? intval($_POST['facebook_no_wall']) : 0);
		set_pconfig($uid,'facebook','no_wall',$no_wall);

		$private_wall = ((x($_POST,'facebook_private_wall')) ? intval($_POST['facebook_private_wall']) : 0);
		set_pconfig($uid,'facebook','private_wall',$private_wall);
	

		set_pconfig($uid,'facebook','blocked_apps',escape_tags(trim($_POST['blocked_apps'])));

		$linkvalue = ((x($_POST,'facebook_linking')) ? intval($_POST['facebook_linking']) : 0);
		set_pconfig($uid,'facebook','no_linking', (($linkvalue) ? 0 : 1));

		// FB linkage was allowed but has just been turned off - remove all FB contacts and posts

		if((! intval($no_linking)) && (! intval($linkvalue))) {
			$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `network` = '%s' ",
				intval($uid),
				dbesc(NETWORK_FACEBOOK)
			);
			if(count($r)) {
				require_once('include/Contact.php');
				foreach($r as $rr)
					contact_remove($rr['id']);
			}
		}
		elseif(intval($no_linking) && intval($linkvalue)) {
			// FB linkage is now allowed - import stuff.
			fb_get_self($uid);
			fb_get_friends($uid, true);
			fb_consume_all($uid);
		}

		info( t('Settings updated.') . EOL);
	} 

	return;		
}

// Facebook settings form

/**
 * @param App $a
 * @return string
 */
function facebook_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return '';
	}

	if($a->argc > 1 && $a->argv[1] === 'remove') {
		del_pconfig(local_user(),'facebook','post');
		info( t('Facebook disabled') . EOL);
	}

	if($a->argc > 1 && $a->argv[1] === 'friends') {
		fb_get_friends(local_user(), true);
		info( t('Updating contacts') . EOL);
	}

	$o = '';
	
	$fb_installed = false;
	if (get_pconfig(local_user(),'facebook','post')) {
		$access_token = get_pconfig(local_user(),'facebook','access_token');
		if ($access_token) {
			$s = fetch_url('https://graph.facebook.com/me/feed?access_token=' . $access_token);
			if($s) {
				$j = json_decode($s);
				if (isset($j->data)) $fb_installed = true;
			}
		}
	}
	
	$appid = get_config('facebook','appid');

	if(! $appid) {
		notice( t('Facebook API key is missing.') . EOL);
		return '';
	}

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'
		. $a->get_baseurl() . '/addon/facebook/facebook.css' . '" media="all" />' . "\r\n";

	$o .= '<h3>' . t('Facebook Connect') . '</h3>';

	if(! $fb_installed) { 
		$o .= '<div id="facebook-enable-wrapper">';

		$o .= '<a href="https://www.facebook.com/dialog/oauth?client_id=' . $appid . '&redirect_uri=' 
			. $a->get_baseurl() . '/facebook/' . $a->user['nickname'] . '&scope=publish_stream,read_stream,offline_access">' . t('Install Facebook connector for this account.') . '</a>';
		$o .= '</div>';
	}

	if($fb_installed) {
		$o .= '<div id="facebook-disable-wrapper">';

		$o .= '<a href="' . $a->get_baseurl() . '/facebook/remove' . '">' . t('Remove Facebook connector') . '</a></div>';

		$o .= '<div id="facebook-enable-wrapper">';

		$o .= '<a href="https://www.facebook.com/dialog/oauth?client_id=' . $appid . '&redirect_uri=' 
			. $a->get_baseurl() . '/facebook/' . $a->user['nickname'] . '&scope=publish_stream,read_stream,offline_access">' . t('Re-authenticate [This is necessary whenever your Facebook password is changed.]') . '</a>';
		$o .= '</div>';
	
		$o .= '<div id="facebook-post-default-form">';
		$o .= '<form action="facebook" method="post" >';
		$post_by_default = get_pconfig(local_user(),'facebook','post_by_default');
		$checked = (($post_by_default) ? ' checked="checked" ' : '');
		$o .= '<input type="checkbox" name="post_by_default" value="1"' . $checked . '/>' . ' ' . t('Post to Facebook by default') . EOL;

		$no_linking = get_pconfig(local_user(),'facebook','no_linking');
		$checked = (($no_linking) ? '' : ' checked="checked" ');
		$o .= '<input type="checkbox" name="facebook_linking" value="1"' . $checked . '/>' . ' ' . t('Link all your Facebook friends and conversations on this website') . EOL ;

		$o .= '<p>' . t('Facebook conversations consist of your <em>profile wall</em> and your friend <em>stream</em>.');
		$o .= ' ' . t('On this website, your Facebook friend stream is only visible to you.');
		$o .= ' ' . t('The following settings determine the privacy of your Facebook profile wall on this website.') . '</p>';

		$private_wall = get_pconfig(local_user(),'facebook','private_wall');
		$checked = (($private_wall) ? ' checked="checked" ' : '');
		$o .= '<input type="checkbox" name="facebook_private_wall" value="1"' . $checked . '/>' . ' ' . t('On this website your Facebook profile wall conversations will only be visible to you') . EOL ;


		$no_wall = get_pconfig(local_user(),'facebook','no_wall');
		$checked = (($no_wall) ? ' checked="checked" ' : '');
		$o .= '<input type="checkbox" name="facebook_no_wall" value="1"' . $checked . '/>' . ' ' . t('Do not import your Facebook profile wall conversations') . EOL ;

		$o .= '<p>' . t('If you choose to link conversations and leave both of these boxes unchecked, your Facebook profile wall will be merged with your profile wall on this website and your privacy settings on this website will be used to determine who may see the conversations.') . '</p>';


		$blocked_apps = get_pconfig(local_user(),'facebook','blocked_apps');

		$o .= '<div><label id="blocked-apps-label" for="blocked-apps">' . t('Comma separated applications to ignore') . ' </label></div>';
    	$o .= '<div><textarea id="blocked-apps" name="blocked_apps" >' . htmlspecialchars($blocked_apps) . '</textarea></div>';

		$o .= '<input type="submit" name="submit" value="' . t('Submit') . '" /></form></div>';
	}

	return $o;
}


/**
 * @param App $a
 * @param null|object $b
 * @return mixed
 */
function facebook_cron($a,$b) {

	$last = get_config('facebook','last_poll');
	
	$poll_interval = intval(get_config('facebook','poll_interval'));
	if(! $poll_interval)
		$poll_interval = FACEBOOK_DEFAULT_POLL_INTERVAL;

	if($last) {
		$next = $last + ($poll_interval * 60);
		if($next > time()) 
			return;
	}

	logger('facebook_cron');


	// Find the FB users on this site and randomize in case one of them
	// uses an obscene amount of memory. It may kill this queue run
	// but hopefully we'll get a few others through on each run. 

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'facebook' AND `k` = 'post' AND `v` = '1' ORDER BY RAND() ");
	if(count($r)) {
		foreach($r as $rr) {
			if(get_pconfig($rr['uid'],'facebook','no_linking'))
				continue;
			$ab = intval(get_config('system','account_abandon_days'));
			if($ab > 0) {
				$z = q("SELECT `uid` FROM `user` WHERE `uid` = %d AND `login_date` > UTC_TIMESTAMP() - INTERVAL %d DAY LIMIT 1",
					intval($rr['uid']),
					intval($ab)
				);
				if(! count($z))
					continue;
			}

			// check for new friends once a day
			$last_friend_check = get_pconfig($rr['uid'],'facebook','friend_check');
			if($last_friend_check) 
				$next_friend_check = $last_friend_check + 86400;
			else
			    $next_friend_check = 0;
			if($next_friend_check <= time()) {
				fb_get_friends($rr['uid'], true);
				set_pconfig($rr['uid'],'facebook','friend_check',time());
			}
			fb_consume_all($rr['uid']);
		}
	}
	
	if (get_config('facebook', 'realtime_active') == 1) {
		if (!facebook_check_realtime_active()) {
			
			logger('facebook_cron: Facebook is not sending Real-Time Updates any more, although it is supposed to. Trying to fix it...', LOGGER_NORMAL);
			facebook_subscription_add_users();
			
			if (facebook_check_realtime_active()) 
				logger('facebook_cron: Successful', LOGGER_NORMAL);
			else {
				logger('facebook_cron: Failed', LOGGER_NORMAL);

				$first_err = get_config('facebook', 'realtime_first_err');
				if (!$first_err) {
					$first_err = time();
					set_config('facebook', 'realtime_first_err', $first_err);
				}
				$first_err_ago = (time() - $first_err);

				if(strlen($a->config['admin_email']) && !get_config('facebook', 'realtime_err_mailsent') && $first_err_ago > (FACEBOOK_RTU_ERR_MAIL_AFTER_MINUTES * 60)) {
					mail($a->config['admin_email'], t('Problems with Facebook Real-Time Updates'),
						"Hi!\n\nThere's a problem with the Facebook Real-Time Updates that cannot be solved automatically. Maybe a permission issue?\n\nPlease try to re-activate it on " . $a->config["system"]["url"] . "/admin/plugins/facebook\n\nThis e-mail will only be sent once.",
						'From: ' . t('Administrator') . '@' . $_SERVER['SERVER_NAME'] . "\n"
						. 'Content-type: text/plain; charset=UTF-8' . "\n"
						. 'Content-transfer-encoding: 8bit'
					);
					
					set_config('facebook', 'realtime_err_mailsent', 1);
				}
			}
		} else { // !facebook_check_realtime_active()
			del_config('facebook', 'realtime_err_mailsent');
			del_config('facebook', 'realtime_first_err');
		}
	}
	
	set_config('facebook','last_poll', time());

}


/**
 * @param App $a
 * @param null|object $b
 */
function facebook_plugin_settings(&$a,&$b) {

	$b .= '<div class="settings-block">';
	$b .= '<h3>' . t('Facebook') . '</h3>';
	$b .= '<a href="facebook">' . t('Facebook Connector Settings') . '</a><br />';
	$b .= '</div>';

}


/**
 * @param App $a
 * @param null|object $o
 */
function facebook_plugin_admin(&$a, &$o){


	$o = '<input type="hidden" name="form_security_token" value="' . get_form_security_token("fbsave") . '">';
	
	$o .= '<h4>' . t('Facebook API Key') . '</h4>';
	
	$appid  = get_config('facebook', 'appid'  );
	$appsecret = get_config('facebook', 'appsecret' );
	$poll_interval = get_config('facebook', 'poll_interval' );
	$sync_comments = get_config('facebook', 'sync_comments' );
	if (!$poll_interval) $poll_interval = FACEBOOK_DEFAULT_POLL_INTERVAL;
	
	$ret1 = q("SELECT `v` FROM `config` WHERE `cat` = 'facebook' AND `k` = 'appid' LIMIT 1");
	$ret2 = q("SELECT `v` FROM `config` WHERE `cat` = 'facebook' AND `k` = 'appsecret' LIMIT 1");
	if ((count($ret1) > 0 && $ret1[0]['v'] != $appid) || (count($ret2) > 0 && $ret2[0]['v'] != $appsecret)) $o .= t('Error: it appears that you have specified the App-ID and -Secret in your .htconfig.php file. As long as they are specified there, they cannot be set using this form.<br><br>');
	
	$working_connection = false;
	if ($appid && $appsecret) {
		$subs = facebook_subscriptions_get();
		if ($subs === null) $o .= t('Error: the given API Key seems to be incorrect (the application access token could not be retrieved).') . '<br>';
		elseif (is_array($subs)) {
			$o .= t('The given API Key seems to work correctly.') . '<br>';
			$working_connection = true;
		} else $o .= t('The correctness of the API Key could not be detected. Somthing strange\'s going on.') . '<br>';
	}
	
	$o .= '<label for="fb_appid">' . t('App-ID / API-Key') . '</label><input id="fb_appid" name="appid" type="text" value="' . escape_tags($appid ? $appid : "") . '"><br style="clear: both;">';
	$o .= '<label for="fb_appsecret">' . t('Application secret') . '</label><input id="fb_appsecret" name="appsecret" type="text" value="' . escape_tags($appsecret ? $appsecret : "") . '"><br style="clear: both;">';
	$o .= '<label for="fb_poll_interval">' . sprintf(t('Polling Interval in minutes (minimum %1$s minutes)'), FACEBOOK_MIN_POLL_INTERVAL) . '</label><input name="poll_interval" id="fb_poll_interval" type="number" min="' . FACEBOOK_MIN_POLL_INTERVAL . '" value="' . $poll_interval . '"><br style="clear: both;">';
	$o .= '<label for="fb_sync_comments">' . t('Synchronize comments (no comments on Facebook are missed, at the cost of increased system load)') . '</label><input name="sync_comments" id="fb_sync_comments" type="checkbox" ' . ($sync_comments ? 'checked' : '') . '><br style="clear: both;">';
	$o .= '<input type="submit" name="fb_save_keys" value="' . t('Save') . '">';
	
	if ($working_connection) {
		$o .= '<h4>' . t('Real-Time Updates') . '</h4>';
		
		$activated = facebook_check_realtime_active();
		if ($activated) {
			$o .= t('Real-Time Updates are activated.') . '<br><br>';
			$o .= '<input type="submit" name="real_time_deactivate" value="' . t('Deactivate Real-Time Updates') . '">';
		} else {
			$o .= t('Real-Time Updates not activated.') . '<br><input type="submit" name="real_time_activate" value="' . t('Activate Real-Time Updates') . '">';
		}
	}
}

/**
 * @param App $a
 */

function facebook_plugin_admin_post(&$a){
	check_form_security_token_redirectOnErr('/admin/plugins/facebook', 'fbsave');
	
	if (x($_REQUEST,'fb_save_keys')) {
		set_config('facebook', 'appid', $_REQUEST['appid']);
		set_config('facebook', 'appsecret', $_REQUEST['appsecret']);
		$poll_interval = IntVal($_REQUEST['poll_interval']);
		if ($poll_interval >= FACEBOOK_MIN_POLL_INTERVAL) set_config('facebook', 'poll_interval', $poll_interval);
		set_config('facebook', 'sync_comments', (x($_REQUEST, 'sync_comments') ? 1 : 0));
		del_config('facebook', 'app_access_token');
		info(t('The new values have been saved.'));
	}
	if (x($_REQUEST,'real_time_activate')) {
		facebook_subscription_add_users();
	}
	if (x($_REQUEST,'real_time_deactivate')) {
		facebook_subscription_del_users();
	}
}

/**
 * @param App $a
 * @param object $b
 * @return mixed
 */
function facebook_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$fb_post = get_pconfig(local_user(),'facebook','post');
	if(intval($fb_post) == 1) {
		$fb_defpost = get_pconfig(local_user(),'facebook','post_by_default');
		$selected = ((intval($fb_defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="facebook_enable"' . $selected . ' value="1" /> ' 
			. t('Post to Facebook') . '</div>';	
	}
}


/**
 * @param App $a
 * @param object $b
 * @return mixed
 */
function facebook_post_hook(&$a,&$b) {


	if($b['deleted'] || ($b['created'] !== $b['edited']))
		return;

	/**
	 * Post to Facebook stream
	 */

	require_once('include/group.php');
	require_once('include/html2plain.php');

	logger('Facebook post');

	$reply = false;
	$likes = false;

	$deny_arr = array();
	$allow_arr = array();

	$toplevel = (($b['id'] == $b['parent']) ? true : false);


	$linking = ((get_pconfig($b['uid'],'facebook','no_linking')) ? 0 : 1);

	if((! $toplevel) && ($linking)) {
		$r = q("SELECT * FROM `item` WHERE `id` = %d AND `uid` = %d LIMIT 1",
			intval($b['parent']),
			intval($b['uid'])
		);
		if(count($r) && substr($r[0]['uri'],0,4) === 'fb::')
			$reply = substr($r[0]['uri'],4);
		elseif(count($r) && substr($r[0]['extid'],0,4) === 'fb::')
			$reply = substr($r[0]['extid'],4);
		else
			return;

		$u = q("SELECT * FROM user where uid = %d limit 1",
			intval($b['uid'])
		);
		if(! count($u))
			return;

		// only accept comments from the item owner. Other contacts are unknown to FB.
 
		if(! link_compare($b['author-link'], $a->get_baseurl() . '/profile/' . $u[0]['nickname']))
			return;
		

		logger('facebook reply id=' . $reply);
	}

	if(strstr($b['postopts'],'facebook') || ($b['private']) || ($reply)) {

		if($b['private'] && $reply === false) {
			$allow_people = expand_acl($b['allow_cid']);
			$allow_groups = expand_groups(expand_acl($b['allow_gid']));
			$deny_people  = expand_acl($b['deny_cid']);
			$deny_groups  = expand_groups(expand_acl($b['deny_gid']));

			$recipients = array_unique(array_merge($allow_people,$allow_groups));
			$deny = array_unique(array_merge($deny_people,$deny_groups));

			$allow_str = dbesc(implode(', ',$recipients));
			if($allow_str) {
				$r = q("SELECT `notify` FROM `contact` WHERE `id` IN ( $allow_str ) AND `network` = 'face'"); 
				if(count($r))
					foreach($r as $rr)
						$allow_arr[] = $rr['notify'];
			}

			$deny_str = dbesc(implode(', ',$deny));
			if($deny_str) {
				$r = q("SELECT `notify` FROM `contact` WHERE `id` IN ( $deny_str ) AND `network` = 'face'"); 
				if(count($r))
					foreach($r as $rr)
						$deny_arr[] = $rr['notify'];
			}

			if(count($deny_arr) && (! count($allow_arr))) {

				// One or more FB folks were denied access but nobody on FB was specifically allowed access.
				// This might cause the post to be open to public on Facebook, but only to selected members
				// on another network. Since this could potentially leak a post to somebody who was denied, 
				// we will skip posting it to Facebook with a slightly vague but relevant message that will 
				// hopefully lead somebody to this code comment for a better explanation of what went wrong.

				notice( t('Post to Facebook cancelled because of multi-network access permission conflict.') . EOL);
				return;
			}


			// if it's a private message but no Facebook members are allowed or denied, skip Facebook post

			if((! count($allow_arr)) && (! count($deny_arr)))
				return;
		}

		if($b['verb'] == ACTIVITY_LIKE)
			$likes = true;				


		$appid  = get_config('facebook', 'appid'  );
		$secret = get_config('facebook', 'appsecret' );

		if($appid && $secret) {

			logger('facebook: have appid+secret');

			$fb_token  = get_pconfig($b['uid'],'facebook','access_token');


			// post to facebook if it's a public post and we've ticked the 'post to Facebook' box, 
			// or it's a private message with facebook participants
			// or it's a reply or likes action to an existing facebook post			

			if($fb_token && ($toplevel || $b['private'] || $reply)) {
				logger('facebook: able to post');
				require_once('library/facebook.php');
				require_once('include/bbcode.php');	

				$msg = $b['body'];

				logger('Facebook post: original msg=' . $msg, LOGGER_DATA);

				// make links readable before we strip the code

				// unless it's a dislike - just send the text as a comment

				// if($b['verb'] == ACTIVITY_DISLIKE)
				//	$msg = trim(strip_tags(bbcode($msg)));

				// Old code
				/*$search_str = $a->get_baseurl() . '/search';

				if(preg_match("/\[url=(.*?)\](.*?)\[\/url\]/is",$msg,$matches)) {

					// don't use hashtags for message link

					if(strpos($matches[2],$search_str) === false) {
						$link = $matches[1];
						if(substr($matches[2],0,5) != '[img]')
							$linkname = $matches[2];
					}
				}

				// strip tag links to avoid link clutter, this really should be 
				// configurable because we're losing information

				$msg = preg_replace("/\#\[url=(.*?)\](.*?)\[\/url\]/is",'#$2',$msg);

				// provide the link separately for normal links
				$msg = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/is",'$2 $1',$msg);

				if(preg_match("/\[img\](.*?)\[\/img\]/is",$msg,$matches))
					$image = $matches[1];

				$msg = preg_replace("/\[img\](.*?)\[\/img\]/is", t('Image: ') . '$1', $msg);

				if((strpos($link,z_root()) !== false) && (! $image))
					$image = $a->get_baseurl() . '/images/friendica-64.jpg';

				$msg = trim(strip_tags(bbcode($msg)));*/

				// New code

				// Looking for the first image
				$image = '';
				if(preg_match("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/is",$b['body'],$matches))
					$image = $matches[3];

				if ($image == '')
					if(preg_match("/\[img\](.*?)\[\/img\]/is",$b['body'],$matches))
						$image = $matches[1];

				// Checking for a bookmark element
				$body = $b['body'];
				if (strpos($body, "[bookmark") !== false) {
					// splitting the text in two parts:
					// before and after the bookmark
					$pos = strpos($body, "[bookmark");
					$body1 = substr($body, 0, $pos);
					$body2 = substr($body, $pos);

					// Removing the bookmark and all quotes after the bookmark
					// they are mostly only the content after the bookmark.
					$body2 = preg_replace("/\[bookmark\=([^\]]*)\](.*?)\[\/bookmark\]/ism",'',$body2);
					$body2 = preg_replace("/\[quote\=([^\]]*)\](.*?)\[\/quote\]/ism",'',$body2);
					$body2 = preg_replace("/\[quote\](.*?)\[\/quote\]/ism",'',$body2);

					$body = $body1.$body2;
				}

				// At first convert the text to html
				$html = bbcode($body);

				// Then convert it to plain text
				$msg = trim($b['title']." \n\n".html2plain($html, 0, true));
				$msg = html_entity_decode($msg,ENT_QUOTES,'UTF-8');

				// Removing multiple newlines
				while (strpos($msg, "\n\n\n") !== false)
					$msg = str_replace("\n\n\n", "\n\n", $msg);

				// add any attachments as text urls
				$arr = explode(',',$b['attach']);

				if(count($arr)) {
					$msg .= "\n";
        				foreach($arr as $r) {
            					$matches = false;
						$cnt = preg_match('|\[attach\]href=\"(.*?)\" size=\"(.*?)\" type=\"(.*?)\" title=\"(.*?)\"\[\/attach\]|',$r,$matches);
						if($cnt) {
							$msg .= "\n".$matches[1];
						}
					}
				}

				$link = '';
				$linkname = '';
				// look for bookmark-bbcode and handle it with priority
				if(preg_match("/\[bookmark\=([^\]]*)\](.*?)\[\/bookmark\]/is",$b['body'],$matches)) {
					$link = $matches[1];
					$linkname = $matches[2];
				}

				// If there is no bookmark element then take the first link
				if ($link == '') {
					$links = collecturls($html);
					if (sizeof($links) > 0) {
						reset($links);
						$link = current($links);
					}
				}

				// Remove trailing and leading spaces
				$msg = trim($msg);

				// Since facebook increased the maxpostlen massively this never should happen again :)
				if (strlen($msg) > FACEBOOK_MAXPOSTLEN) {
					require_once('library/slinky.php');

					$display_url = $b['plink'];

					$slinky = new Slinky( $display_url );
					// setup a cascade of shortening services
					// try to get a short link from these services
					// in the order ur1.ca, trim, id.gd, tinyurl
					$slinky->set_cascade( array( new Slinky_UR1ca(), new Slinky_Trim(), new Slinky_IsGd(), new Slinky_TinyURL() ) );
					$shortlink = $slinky->short();
					// the new message will be shortened such that "... $shortlink"
					// will fit into the character limit
					$msg = substr($msg, 0, FACEBOOK_MAXPOSTLEN - strlen($shortlink) - 4);
					$msg .= '... ' . $shortlink;
				}

				// Fallback - if message is empty
				if(!strlen($msg))
					$msg = $link;

				if(!strlen($msg))
					$msg = $image;

				if(!strlen($msg))
					$msg = $linkname;

				// If there is nothing to post then exit
				if(!strlen($msg))
					return;

				logger('Facebook post: msg=' . $msg, LOGGER_DATA);

				if($likes) { 
					$postvars = array('access_token' => $fb_token);
				}
				else {
					$postvars = array(
						'access_token' => $fb_token, 
						'message' => $msg
					);
					if(isset($image)) {
						$postvars['picture'] = $image;
						//$postvars['type'] = "photo";
					}
					if(isset($link)) {
						$postvars['link'] = $link;
						//$postvars['type'] = "link";
					}
					if(isset($linkname))
						$postvars['name'] = $linkname;
				}

				if(($b['private']) && ($toplevel)) {
					$postvars['privacy'] = '{"value": "CUSTOM", "friends": "SOME_FRIENDS"';
					if(count($allow_arr))
						$postvars['privacy'] .= ',"allow": "' . implode(',',$allow_arr) . '"';
					if(count($deny_arr))
						$postvars['privacy'] .= ',"deny": "' . implode(',',$deny_arr) . '"';
					$postvars['privacy'] .= '}';

				}

				if($reply) {
					$url = 'https://graph.facebook.com/' . $reply . '/' . (($likes) ? 'likes' : 'comments');
				} else if (($link != "")  or ($image != "") or ($b['title'] == '') or (strlen($msg) < 500)) { 
					$url = 'https://graph.facebook.com/me/feed';
					if($b['plink'])
						$postvars['actions'] = '{"name": "' . t('View on Friendica') . '", "link": "' .  $b['plink'] . '"}';
				} else {
					// if its only a message and a subject and the message is larger than 500 characters then post it as note
					$postvars = array(
						'access_token' => $fb_token, 
						'message' => bbcode($b['body']),
						'subject' => $b['title'],
					);
					$url = 'https://graph.facebook.com/me/notes';
				}

				logger('facebook: post to ' . $url);
				logger('facebook: postvars: ' . print_r($postvars,true));

				// "test_mode" prevents anything from actually being posted.
				// Otherwise, let's do it.

				if(! get_config('facebook','test_mode')) {
					$x = post_url($url, $postvars);
					logger('Facebook post returns: ' . $x, LOGGER_DEBUG);

					$retj = json_decode($x);
					if($retj->id) {
						q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d LIMIT 1",
							dbesc('fb::' . $retj->id),
							intval($b['id'])
						);
					}
					else {
						if(! $likes) {
							$s = serialize(array('url' => $url, 'item' => $b['id'], 'post' => $postvars));
							require_once('include/queue_fn.php');
							add_to_queue($a->contact,NETWORK_FACEBOOK,$s);
							notice( t('Facebook post failed. Queued for retry.') . EOL);
						}
						
						if (isset($retj->error) && $retj->error->type == "OAuthException" && $retj->error->code == 190) {
							logger('Facebook session has expired due to changed password.', LOGGER_DEBUG);
							
							$last_notification = get_pconfig($b['uid'], 'facebook', 'session_expired_mailsent');
							if (!$last_notification || $last_notification < (time() - FACEBOOK_SESSION_ERR_NOTIFICATION_INTERVAL)) {
								require_once('include/enotify.php');
							
								$r = q("SELECT * FROM `user` WHERE `uid` = %d LIMIT 1", intval($b['uid']) );
								notification(array(
									'uid' => $b['uid'],
									'type' => NOTIFY_SYSTEM,
									'system_type' => 'facebook_connection_invalid',
									'language'     => $r[0]['language'],
									'to_name'      => $r[0]['username'],
									'to_email'     => $r[0]['email'],
									'source_name'  => t('Administrator'),
									'source_link'  => $a->config["system"]["url"],
									'source_photo' => $a->config["system"]["url"] . '/images/person-80.jpg',
								));
								
								set_pconfig($b['uid'], 'facebook', 'session_expired_mailsent', time());
							} else logger('Facebook: No notification, as the last one was sent on ' . $last_notification, LOGGER_DEBUG);
						}
					}
				}
			}
		}
	}
}

/**
 * @param App $app
 * @param object $data
 */
function facebook_enotify(&$app, &$data) {
	if (x($data, 'params') && $data['params']['type'] == NOTIFY_SYSTEM && x($data['params'], 'system_type') && $data['params']['system_type'] == 'facebook_connection_invalid') {
		$data['itemlink'] = '/facebook';
		$data['epreamble'] = $data['preamble'] = t('Your Facebook connection became invalid. Please Re-authenticate.');
		$data['subject'] = t('Facebook connection became invalid');
		$data['body'] = sprintf( t("Hi %1\$s,\n\nThe connection between your accounts on %2\$s and Facebook became invalid. This usually happens after you change your Facebook-password. To enable the connection again, you have to %3\$sre-authenticate the Facebook-connector%4\$s."), $data['params']['to_name'], "[url=" . $app->config["system"]["url"] . "]" . $app->config["sitename"] . "[/url]", "[url=" . $app->config["system"]["url"] . "/facebook]", "[/url]");
	}
}

/**
 * @param App $a
 * @param object $b
 */
function facebook_post_local(&$a,&$b) {

	// Figure out if Facebook posting is enabled for this post and file it in 'postopts'
	// where we will discover it during background delivery.

	// This can only be triggered by a local user posting to their own wall.

	if((local_user()) && (local_user() == $b['uid'])) {

		$fb_post   = intval(get_pconfig(local_user(),'facebook','post'));
		$fb_enable = (($fb_post && x($_REQUEST,'facebook_enable')) ? intval($_REQUEST['facebook_enable']) : 0);

		// if API is used, default to the chosen settings
		if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'facebook','post_by_default')))
			$fb_enable = 1;

		if(! $fb_enable)
			return;

		if(strlen($b['postopts']))
			$b['postopts'] .= ',';
		$b['postopts'] .= 'facebook';
	}
}


/**
 * @param App $a
 * @param object $b
 */
function fb_queue_hook(&$a,&$b) {

	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		dbesc(NETWORK_FACEBOOK)
	);
	if(! count($qi))
		return;

	require_once('include/queue_fn.php');

	foreach($qi as $x) {
		if($x['network'] !== NETWORK_FACEBOOK)
			continue;

		logger('facebook_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid` 
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);
		if(! count($r))
			continue;

		$user = $r[0];

		$appid  = get_config('facebook', 'appid'  );
		$secret = get_config('facebook', 'appsecret' );

		if($appid && $secret) {
			$fb_post   = intval(get_pconfig($user['uid'],'facebook','post'));
			$fb_token  = get_pconfig($user['uid'],'facebook','access_token');

			if($fb_post && $fb_token) {
				logger('facebook_queue: able to post');
				require_once('library/facebook.php');

				$z = unserialize($x['content']);
				$item = $z['item'];
				$j = post_url($z['url'],$z['post']);

				$retj = json_decode($j);
				if($retj->id) {
					q("UPDATE `item` SET `extid` = '%s' WHERE `id` = %d LIMIT 1",
						dbesc('fb::' . $retj->id),
						intval($item)
					);
					logger('facebook_queue: success: ' . $j); 
					remove_queue_item($x['id']);
				}
				else {
					logger('facebook_queue: failed: ' . $j);
					update_queue_time($x['id']);
				}
			}
		}
	}
}

/**
 * @param string $access_token
 * @param int $since
 * @return object
 */
function fb_get_timeline($access_token, &$since) {

    $entries = new stdClass();
	$entries->data = array();
	$newest = 0;

	$url = 'https://graph.facebook.com/me/home?access_token='.$access_token;

	if ($since != 0)
		$url .= "&since=".$since;

	do {
		$s = fetch_url($url);
		$j = json_decode($s);
		$oldestdate = time();
		if (isset($j->data))
			foreach ($j->data as $entry) {
				$created = strtotime($entry->created_time);

				if ($newest < $created)
					$newest = $created;

				if ($created >= $since)
					$entries->data[] = $entry;

				if ($created <= $oldestdate)
					$oldestdate = $created;
			}
		else
			break;

		$url = (isset($j->paging) && isset($j->paging->next) ? $j->paging->next : '');

	} while (($oldestdate > $since) and ($since != 0) and ($url != ''));

	if ($newest > $since)
		$since = $newest;

	return($entries);
}

/**
 * @param int $uid
 */
function fb_consume_all($uid) {

	require_once('include/items.php');

	$access_token = get_pconfig($uid,'facebook','access_token');
	if(! $access_token)
		return;
	
	if(! get_pconfig($uid,'facebook','no_wall')) {
		$private_wall = intval(get_pconfig($uid,'facebook','private_wall'));
		$s = fetch_url('https://graph.facebook.com/me/feed?access_token=' . $access_token);
		if($s) {
			$j = json_decode($s);
			if (isset($j->data)) {
				logger('fb_consume_stream: wall: ' . print_r($j,true), LOGGER_DATA);
				fb_consume_stream($uid,$j,($private_wall) ? false : true);
			} else {
				logger('fb_consume_stream: wall: got no data from Facebook: ' . print_r($j,true), LOGGER_NORMAL);
			}
		}
	}
	// Get the last date
	$lastdate = get_pconfig($uid,'facebook','lastdate');
	// fetch all items since the last date
	$j = fb_get_timeline($access_token, $lastdate);
	if (isset($j->data)) {
		logger('fb_consume_stream: feed: ' . print_r($j,true), LOGGER_DATA);
		fb_consume_stream($uid,$j,false);

		// Write back the last date
		set_pconfig($uid,'facebook','lastdate', $lastdate);
	} else
		logger('fb_consume_stream: feed: got no data from Facebook: ' . print_r($j,true), LOGGER_NORMAL);
}

/**
 * @param int $uid
 * @param string $link
 * @return string
 */
function fb_get_photo($uid,$link) {
	$access_token = get_pconfig($uid,'facebook','access_token');
	if(! $access_token || (! stristr($link,'facebook.com/photo.php')))
		return "";
		//return "\n" . '[url=' . $link . ']' . t('link') . '[/url]';
	$ret = preg_match('/fbid=([0-9]*)/',$link,$match);
	if($ret)
		$photo_id = $match[1];
	else
	    return "";
	$x = fetch_url('https://graph.facebook.com/' . $photo_id . '?access_token=' . $access_token);
	$j = json_decode($x);
	if($j->picture)
		return "\n\n" . '[url=' . $link . '][img]' . $j->picture . '[/img][/url]';
	//else
	//	return "\n" . '[url=' . $link . ']' . t('link') . '[/url]';
	return "";
}


/**
 * @param App $a
 * @param array $user
 * @param array $self
 * @param string $fb_id
 * @param bool $wall
 * @param array $orig_post
 * @param object $cmnt
 */
function fb_consume_comment(&$a, &$user, &$self, $fb_id, $wall, &$orig_post, &$cmnt) {

    if(! $orig_post)
        return;

    $top_item = $orig_post['id'];
    $uid = IntVal($user[0]['uid']);

    $r = q("SELECT * FROM `item` WHERE `uid` = %d AND ( `uri` = '%s' OR `extid` = '%s' ) LIMIT 1",
        intval($uid),
        dbesc('fb::' . $cmnt->id),
        dbesc('fb::' . $cmnt->id)
    );
    if(count($r))
        return;

    $cmntdata = array();
    $cmntdata['parent'] = $top_item;
    $cmntdata['verb'] = ACTIVITY_POST;
    $cmntdata['gravity'] = 6;
    $cmntdata['uid'] = $uid;
    $cmntdata['wall'] = (($wall) ? 1 : 0);
    $cmntdata['uri'] = 'fb::' . $cmnt->id;
    $cmntdata['parent-uri'] = $orig_post['uri'];
    if($cmnt->from->id == $fb_id) {
        $cmntdata['contact-id'] = $self[0]['id'];
    }
    else {
        $r = q("SELECT * FROM `contact` WHERE `notify` = '%s' AND `uid` = %d LIMIT 1",
            dbesc($cmnt->from->id),
            intval($uid)
        );
        if(count($r)) {
            $cmntdata['contact-id'] = $r[0]['id'];
            if($r[0]['blocked'] || $r[0]['readonly'])
                return;
        }
    }
    if(! x($cmntdata,'contact-id'))
        $cmntdata['contact-id'] = $orig_post['contact-id'];

    $cmntdata['app'] = 'facebook';
    $cmntdata['created'] = datetime_convert('UTC','UTC',$cmnt->created_time);
    $cmntdata['edited']  = datetime_convert('UTC','UTC',$cmnt->created_time);
    $cmntdata['verb'] = ACTIVITY_POST;
    $cmntdata['author-name'] = $cmnt->from->name;
    $cmntdata['author-link'] = 'http://facebook.com/profile.php?id=' . $cmnt->from->id;
    $cmntdata['author-avatar'] = 'https://graph.facebook.com/' . $cmnt->from->id . '/picture';
    $cmntdata['body'] = $cmnt->message;
    $item = item_store($cmntdata);

    $myconv = q("SELECT `author-link`, `author-avatar`, `parent` FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `parent` != 0 AND `deleted` = 0",
        dbesc($orig_post['uri']),
        intval($uid)
    );

    if(count($myconv)) {
        $importer_url = $a->get_baseurl() . '/profile/' . $user[0]['nickname'];

        foreach($myconv as $conv) {

            // now if we find a match, it means we're in this conversation

            if(! link_compare($conv['author-link'],$importer_url))
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
                'item'         => $cmntdata,
                'link'		   => $a->get_baseurl() . '/display/' . $user[0]['nickname'] . '/' . $item,
                'source_name'  => $cmntdata['author-name'],
                'source_link'  => $cmntdata['author-link'],
                'source_photo' => $cmntdata['author-avatar'],
                'verb'         => ACTIVITY_POST,
                'otype'        => 'item',
                'parent'       => $conv_parent,
            ));

            // only send one notification
            break;
        }
    }
}


/**
 * @param App $a
 * @param array $user
 * @param array $self
 * @param string $fb_id
 * @param bool $wall
 * @param array $orig_post
 * @param object $likes
 */
function fb_consume_like(&$a, &$user, &$self, $fb_id, $wall, &$orig_post, &$likes) {

    $top_item = $orig_post['id'];
    $uid = IntVal($user[0]['uid']);

    if(! $orig_post)
        return;

    // If we posted the like locally, it will be found with our url, not the FB url.

    $second_url = (($likes->id == $fb_id) ? $self[0]['url'] : 'http://facebook.com/profile.php?id=' . $likes->id);

    $r = q("SELECT * FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `verb` = '%s'
    	AND ( `author-link` = '%s' OR `author-link` = '%s' ) LIMIT 1",
        dbesc($orig_post['uri']),
        intval($uid),
        dbesc(ACTIVITY_LIKE),
        dbesc('http://facebook.com/profile.php?id=' . $likes->id),
        dbesc($second_url)
    );

    if(count($r))
        return;

    $likedata = array();
    $likedata['parent'] = $top_item;
    $likedata['verb'] = ACTIVITY_LIKE;
    $likedata['gravity'] = 3;
    $likedata['uid'] = $uid;
    $likedata['wall'] = (($wall) ? 1 : 0);
    $likedata['uri'] = item_new_uri($a->get_baseurl(), $uid);
    $likedata['parent-uri'] = $orig_post['uri'];
    if($likes->id == $fb_id)
        $likedata['contact-id'] = $self[0]['id'];
    else {
        $r = q("SELECT * FROM `contact` WHERE `notify` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
            dbesc($likes->id),
            intval($uid)
        );
        if(count($r))
            $likedata['contact-id'] = $r[0]['id'];
    }
    if(! x($likedata,'contact-id'))
        $likedata['contact-id'] = $orig_post['contact-id'];

    $likedata['app'] = 'facebook';
    $likedata['verb'] = ACTIVITY_LIKE;
    $likedata['author-name'] = $likes->name;
    $likedata['author-link'] = 'http://facebook.com/profile.php?id=' . $likes->id;
    $likedata['author-avatar'] = 'https://graph.facebook.com/' . $likes->id . '/picture';

    $author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';
    $objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
    $post_type = t('status');
    $plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
    $likedata['object-type'] = ACTIVITY_OBJ_NOTE;

    $likedata['body'] = sprintf( t('%1$s likes %2$s\'s %3$s'), $author, $objauthor, $plink);
    $likedata['object'] = '<object><type>' . ACTIVITY_OBJ_NOTE . '</type><local>1</local>' .
        '<id>' . $orig_post['uri'] . '</id><link>' . xmlify('<link rel="alternate" type="text/html" href="' . xmlify($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';

    item_store($likedata);
}

/**
 * @param App $a
 * @param array $user
 * @param object $entry
 * @param array $self
 * @param string $fb_id
 * @param bool $wall
 * @param array $orig_post
 */
function fb_consume_status(&$a, &$user, &$entry, &$self, $fb_id, $wall, &$orig_post) {
    $uid = IntVal($user[0]['uid']);
    $access_token = get_pconfig($uid, 'facebook', 'access_token');

    $s = fetch_url('https://graph.facebook.com/' . $entry->id . '?access_token=' . $access_token);
    if($s) {
        $j = json_decode($s);
        if (isset($j->comments) && isset($j->comments->data))
            foreach ($j->comments->data as $cmnt)
                fb_consume_comment($a, $user, $self, $fb_id, $wall, $orig_post, $cmnt);

        if (isset($j->likes) && isset($j->likes->data) && isset($j->likes->count)) {
            if (count($j->likes->data) == $j->likes->count) {
                foreach ($j->likes->data as $likers) fb_consume_like($a, $user, $self, $fb_id, $wall, $orig_post, $likers);
            } else {
                $t = fetch_url('https://graph.facebook.com/' . $entry->id . '/likes?access_token=' . $access_token);
                if ($t) {
                    $k = json_decode($t);
                    if (isset($k->data))
                        foreach ($k->data as $likers)
                            fb_consume_like($a, $user, $self, $fb_id, $wall, $orig_post, $likers);
                }
            }
        }
    }
}

/**
 * @param int $uid
 * @param object $j
 * @param bool $wall
 */
function fb_consume_stream($uid,$j,$wall = false) {

	$a = get_app();

	$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
		intval($uid)
	);
	if(! count($user))
		return;

	// $my_local_url = $a->get_baseurl() . '/profile/' . $user[0]['nickname'];

	$no_linking = get_pconfig($uid,'facebook','no_linking');
	if($no_linking)
		return;

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid)
	);

	$blocked_apps = get_pconfig($uid,'facebook','blocked_apps');
	$blocked_apps_arr = explode(',',$blocked_apps);

	$sync_comments = get_config('facebook', 'sync_comments');

    /** @var string $self_id  */
	$self_id = get_pconfig($uid,'facebook','self_id');
	if(! count($j->data) || (! strlen($self_id)))
		return;

    $top_item = 0;

    foreach($j->data as $entry) {
		logger('fb_consume: entry: ' . print_r($entry,true), LOGGER_DATA);
		$datarray = array();

		$r = q("SELECT * FROM `item` WHERE ( `uri` = '%s' OR `extid` = '%s') AND `uid` = %d LIMIT 1",
				dbesc('fb::' . $entry->id),
				dbesc('fb::' . $entry->id),
				intval($uid)
		);
		if(count($r)) {
			$orig_post = $r[0];
			$top_item = $r[0]['id'];
		}
		else {
			$orig_post = null;
		}

		if(! $orig_post) {
			$datarray['gravity'] = 0;
			$datarray['uid'] = $uid;
			$datarray['wall'] = (($wall) ? 1 : 0);
			$datarray['uri'] = $datarray['parent-uri'] = 'fb::' . $entry->id;
			$from = $entry->from;
			if($from->id == $self_id)
				$datarray['contact-id'] = $self[0]['id'];
			else {
				// Looking if user is known - if not he is added
				$access_token = get_pconfig($uid, 'facebook', 'access_token');
				fb_get_friends_sync_new($uid, $access_token, array($from));

				$r = q("SELECT * FROM `contact` WHERE `notify` = '%s' AND `uid` = %d AND `blocked` = 0 AND `readonly` = 0 LIMIT 1",
					dbesc($from->id),
					intval($uid)
				);
				if(count($r))
					$datarray['contact-id'] = $r[0]['id'];
			}

			// don't store post if we don't have a contact
			if(! x($datarray,'contact-id')) {
				logger('facebook: no contact '.$from->name.' '.$from->id.'. post ignored');
				continue;
			}

			$datarray['verb'] = ACTIVITY_POST;
			if($wall) {
				$datarray['owner-name'] = $self[0]['name'];
				$datarray['owner-link'] = $self[0]['url'];
				$datarray['owner-avatar'] = $self[0]['thumb'];
			}
			if(isset($entry->application) && isset($entry->application->name) && strlen($entry->application->name))
				$datarray['app'] = strip_tags($entry->application->name);
			else
				$datarray['app'] = 'facebook';

			$found_blocked = false;

			if(count($blocked_apps_arr)) {
				foreach($blocked_apps_arr as $bad_appl) {
					if(strlen(trim($bad_appl)) && (stristr($datarray['app'],trim($bad_appl)))) {
						$found_blocked = true;
					}
				}
			}
				
			if($found_blocked) {
				logger('facebook: blocking application: ' . $datarray['app']);
				continue;
			}

			$datarray['author-name'] = $from->name;
			$datarray['author-link'] = 'http://facebook.com/profile.php?id=' . $from->id;
			$datarray['author-avatar'] = 'https://graph.facebook.com/' . $from->id . '/picture';
			$datarray['plink'] = $datarray['author-link'] . '&v=wall&story_fbid=' . substr($entry->id,strpos($entry->id,'_') + 1);

			logger('facebook: post '.$entry->id.' from '.$from->name);

			$datarray['body'] = (isset($entry->message) ? escape_tags($entry->message) : '');

			if(isset($entry->name) and isset($entry->link))
				$datarray['body'] .= "\n\n[bookmark=".$entry->link."]".$entry->name."[/bookmark]";
			elseif (isset($entry->name))
				$datarray['body'] .= "\n\n[b]" . $entry->name."[/b]";

			if(isset($entry->caption)) {
				if(!isset($entry->name) and isset($entry->link))
					$datarray['body'] .= "\n\n[bookmark=".$entry->link."]".$entry->caption."[/bookmark]";
				else
					$datarray['body'] .= "[i]" . $entry->caption."[/i]\n";
			}

			if(!isset($entry->caption) and !isset($entry->name)) {
				if (isset($entry->link))
					$datarray['body'] .= "\n[url]".$entry->link."[/url]\n";
				else
					$datarray['body'] .= "\n";
			}

			$quote = "";
			if(isset($entry->description))
				$quote = $entry->description;

			if (isset($entry->properties))
				foreach ($entry->properties as $property)
					$quote .= "\n".$property->name.": [url=".$property->href."]".$property->text."[/url]";

			if ($quote)
				$datarray['body'] .= "\n[quote]".$quote."[/quote]";

			// Only import the picture when the message is no video
			// oembed display a picture of the video as well 
			if ($entry->type != "video") {
				if(isset($entry->picture) && isset($entry->link)) {
					$datarray['body'] .= "\n" . '[url=' . $entry->link . '][img]'.$entry->picture.'[/img][/url]';	
				}
				else {
					if(isset($entry->picture))
						$datarray['body'] .= "\n" . '[img]' . $entry->picture . '[/img]';
					// if just a link, it may be a wall photo - check
					if(isset($entry->link))
						$datarray['body'] .= fb_get_photo($uid,$entry->link);
				}
			}

			if (($datarray['app'] == "Events") and isset($entry->actions))
				foreach ($entry->actions as $action)
					if ($action->name == "View")
						$datarray['body'] .= " [url=".$action->link."]".$entry->story."[/url]";

			// Just as a test - to see if these are the missing entries
			//if(trim($datarray['body']) == '')
			//	$datarray['body'] = $entry->story;

			// Adding the "story" text to see if there are useful data in it (testing)
			//if (($datarray['app'] != "Events") and $entry->story)
			//	$datarray['body'] .= "\n".$entry->story;

			if(trim($datarray['body']) == '') {
				logger('facebook: empty body '.$entry->id.' '.print_r($entry, true));
				continue;
			}

			$datarray['body'] .= "\n";

			if (isset($entry->icon))
				$datarray['body'] .= "[img]".$entry->icon."[/img] &nbsp; ";

			if (isset($entry->actions))
				foreach ($entry->actions as $action)
					if (($action->name != "Comment") and ($action->name != "Like"))
						$datarray['body'] .= "[url=".$action->link."]".$action->name."[/url] &nbsp; ";

			$datarray['body'] = trim($datarray['body']);

			//if(($datarray['body'] != '') and ($uid == 1))
			//	$datarray['body'] .= "[noparse]".print_r($entry, true)."[/noparse]";

            if (isset($entry->place)) {
			    if ($entry->place->name or $entry->place->location->street or
				    $entry->place->location->city or $entry->place->location->Denmark) {
				    $datarray['coord'] = '';
				    if ($entry->place->name)
					    $datarray['coord'] .= $entry->place->name;
				    if ($entry->place->location->street)
					    $datarray['coord'] .= $entry->place->location->street;
				    if ($entry->place->location->city)
					    $datarray['coord'] .= " ".$entry->place->location->city;
				    if ($entry->place->location->country)
					    $datarray['coord'] .= " ".$entry->place->location->country;
			    } else if ($entry->place->location->latitude and $entry->place->location->longitude)
				    $datarray['coord'] = substr($entry->place->location->latitude, 0, 8)
							.' '.substr($entry->place->location->longitude, 0, 8);
            }
			$datarray['created'] = datetime_convert('UTC','UTC',$entry->created_time);
			$datarray['edited'] = datetime_convert('UTC','UTC',$entry->updated_time);

			// If the entry has a privacy policy, we cannot assume who can or cannot see it,
			// as the identities are from a foreign system. Mark it as private to the owner.

			if(isset($entry->privacy) && $entry->privacy->value !== 'EVERYONE') {
				$datarray['private'] = 1;
				$datarray['allow_cid'] = '<' . $self[0]['id'] . '>';
			}

			$top_item = item_store($datarray);
			$r = q("SELECT * FROM `item` WHERE `id` = %d AND `uid` = %d LIMIT 1",
				intval($top_item),
				intval($uid)
			);
			if(count($r)) {
				$orig_post = $r[0];
				logger('fb: new top level item posted');
			}
		}

		/**  @var array $orig_post */

        $likers_num = (isset($entry->likes) && isset($entry->likes->count) ? IntVal($entry->likes->count) : 0 );
		if(isset($entry->likes) && isset($entry->likes->data))
			$likers = $entry->likes->data;
		else
			$likers = null;

        $comments_num = (isset($entry->comments) && isset($entry->comments->count) ? IntVal($entry->comments->count) : 0 );
		if(isset($entry->comments) && isset($entry->comments->data))
			$comments = $entry->comments->data;
		else
			$comments = null;

        $needs_sync = false;

        if(is_array($likers)) {
			foreach($likers as $likes) fb_consume_like($a, $user, $self, $self_id, $wall, $orig_post, $likes);
            if ($sync_comments) {
                $r = q("SELECT COUNT(*) likes FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `verb` = '%s' AND `parent-uri` != `uri`",
                    dbesc($orig_post['uri']),
                    intval($uid),
                    dbesc(ACTIVITY_LIKE)
                );
                if ($r[0]['likes'] < $likers_num) {
                    logger('fb_consume_stream: missing likes found for ' . $orig_post['uri'] . ' (we have ' . $r[0]['likes'] . ' of ' . $likers_num . '). Synchronizing...', LOGGER_DEBUG);
                    $needs_sync = true;
                }
            }
		}

		if(is_array($comments)) {
			foreach($comments as $cmnt) fb_consume_comment($a, $user, $self, $self_id, $wall, $orig_post, $cmnt);
			if ($sync_comments) {
			    $r = q("SELECT COUNT(*) comments FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `verb` = '%s' AND `parent-uri` != `uri`",
                    dbesc($orig_post['uri']),
                    intval($uid),
                    ACTIVITY_POST
                );
			    if ($r[0]['comments'] < $comments_num) {
                    logger('fb_consume_stream: missing comments found for ' . $orig_post['uri'] . ' (we have ' . $r[0]['comments'] . ' of ' . $comments_num . '). Synchronizing...', LOGGER_DEBUG);
                    $needs_sync = true;
                }
			}
		}

		if ($needs_sync) fb_consume_status($a, $user, $entry, $self, $self_id, $wall, $orig_post);
	}
}


/**
 * @return bool|string
 */
function fb_get_app_access_token() {
	
	$acc_token = get_config('facebook','app_access_token');
	
	if ($acc_token !== false) return $acc_token;
	
	$appid = get_config('facebook','appid');
	$appsecret = get_config('facebook', 'appsecret');
	
	if ($appid === false || $appsecret === false) {
		logger('fb_get_app_access_token: appid and/or appsecret not set', LOGGER_DEBUG);
		return false;
	}
	logger('https://graph.facebook.com/oauth/access_token?client_id=' . $appid . '&client_secret=' . $appsecret . '&grant_type=client_credentials', LOGGER_DATA);
	$x = fetch_url('https://graph.facebook.com/oauth/access_token?client_id=' . $appid . '&client_secret=' . $appsecret . '&grant_type=client_credentials');
	
	if(strpos($x,'access_token=') !== false) {
		logger('fb_get_app_access_token: returned access token: ' . $x, LOGGER_DATA);
	
		$token = str_replace('access_token=', '', $x);
 		if(strpos($token,'&') !== false)
			$token = substr($token,0,strpos($token,'&'));
		
		if ($token == "") {
			logger('fb_get_app_access_token: empty token: ' . $x, LOGGER_DEBUG);
			return false;
		}
		set_config('facebook','app_access_token',$token);
		return $token;
	} else {
		logger('fb_get_app_access_token: response did not contain an access_token: ' . $x, LOGGER_DATA);
		return false;
	}
}

function facebook_subscription_del_users() {
	$a = get_app();
	$access_token = fb_get_app_access_token();
	
	$url = "https://graph.facebook.com/" . get_config('facebook', 'appid'  ) . "/subscriptions?access_token=" . $access_token;
	facebook_delete_url($url);
	
	if (!facebook_check_realtime_active()) del_config('facebook', 'realtime_active');
}

/**
 * @param bool $second_try
 */
function facebook_subscription_add_users($second_try = false) {
	$a = get_app();
	$access_token = fb_get_app_access_token();
	
	$url = "https://graph.facebook.com/" . get_config('facebook', 'appid'  ) . "/subscriptions?access_token=" . $access_token;
	
	list($usec, $sec) = explode(" ", microtime());
	$verify_token = sha1($usec . $sec . rand(0, 999999999));
	set_config('facebook', 'cb_verify_token', $verify_token);
	
	$cb = $a->get_baseurl() . '/facebook/?realtime_cb=1';
	
	$j = post_url($url,array(
		"object" => "user",
		"fields" => "feed,friends",
		"callback_url" => $cb,
		"verify_token" => $verify_token,
	));
	del_config('facebook', 'cb_verify_token');
	
	if ($j) {
		$x = json_decode($j);
		logger("Facebook reponse: " . $j, LOGGER_DATA);
		if (isset($x->error)) {
			logger('facebook_subscription_add_users: got an error: ' . $j);
			if ($x->error->type == "OAuthException" && $x->error->code == 190) {
				del_config('facebook', 'app_access_token');
				if ($second_try === false) facebook_subscription_add_users(true);
			}
		} else {
			logger('facebook_subscription_add_users: sucessful');
			if (facebook_check_realtime_active()) set_config('facebook', 'realtime_active', 1);
		}
	};
}

/**
 * @return null|array
 */
function facebook_subscriptions_get() {
	
	$access_token = fb_get_app_access_token();
	if (!$access_token) return null;
	
	$url = "https://graph.facebook.com/" . get_config('facebook', 'appid'  ) . "/subscriptions?access_token=" . $access_token;
	$j = fetch_url($url);
	$ret = null;
	if ($j) {
		$x = json_decode($j);
		if (isset($x->data)) $ret = $x->data;
	}
	return $ret;
}


/**
 * @return bool
 */
function facebook_check_realtime_active() {
	$ret = facebook_subscriptions_get();
	if (is_null($ret)) return false;
	if (is_array($ret)) foreach ($ret as $re) if (is_object($re) && $re->object == "user") return true;
	return false;
}




// DELETE-request to $url

if(! function_exists('facebook_delete_url')) {
    /**
     * @param string $url
     * @param null|array $headers
     * @param int $redirects
     * @param int $timeout
     * @return bool|string
     */
    function facebook_delete_url($url,$headers = null, &$redirects = 0, $timeout = 0) {
	$a = get_app();
	$ch = curl_init($url);
	if(($redirects > 8) || (! $ch)) 
		return false;

	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($ch, CURLOPT_USERAGENT, "Friendica");

	if(intval($timeout)) {
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	}
	else {
		$curl_time = intval(get_config('system','curl_timeout'));
		curl_setopt($ch, CURLOPT_TIMEOUT, (($curl_time !== false) ? $curl_time : 60));
	}

	if(defined('LIGHTTPD')) {
		if(!is_array($headers)) {
			$headers = array('Expect:');
		} else {
			if(!in_array('Expect:', $headers)) {
				array_push($headers, 'Expect:');
			}
		}
	}
	if($headers)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$check_cert = get_config('system','verifyssl');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (($check_cert) ? true : false));
	$prx = get_config('system','proxy');
	if(strlen($prx)) {
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
		curl_setopt($ch, CURLOPT_PROXY, $prx);
		$prxusr = get_config('system','proxyuser');
		if(strlen($prxusr))
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $prxusr);
	}

	$a->set_curl_code(0);

	// don't let curl abort the entire application
	// if it throws any errors.

	$s = @curl_exec($ch);

	$base = $s;
	$curl_info = curl_getinfo($ch);
	$http_code = $curl_info['http_code'];

	$header = '';

	// Pull out multiple headers, e.g. proxy and continuation headers
	// allow for HTTP/2.x without fixing code

	while(preg_match('/^HTTP\/[1-2].+? [1-5][0-9][0-9]/',$base)) {
		$chunk = substr($base,0,strpos($base,"\r\n\r\n")+4);
		$header .= $chunk;
		$base = substr($base,strlen($chunk));
	}

	if($http_code == 301 || $http_code == 302 || $http_code == 303) {
        $matches = array();
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $url_parsed = @parse_url($url);
        if (isset($url_parsed)) {
            $redirects++;
            return facebook_delete_url($url,$headers,$redirects,$timeout);
        }
    }
	$a->set_curl_code($http_code);
	$body = substr($s,strlen($header));

	$a->set_curl_headers($header);

	curl_close($ch);
	return($body);
}}
