<?php
/**
 * Name: Facebook Sync
 * Description: Synchronizes the Facebook Newsfeed
 * Version: 0.0.1 alpha
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

/* To-Do
FBSync:
- B: Threading for incoming comments
- C: Receiving likes for comments

FBPost:
- A: Posts to pages currently have the page as sender - not the user
- B: Sending likes for comments
- C: Threading for sent comments
*/

require_once("addon/fbpost/fbpost.php");

define('FBSYNC_DEFAULT_POLL_INTERVAL', 5); // given in minutes

function fbsync_install() {
	register_hook('connector_settings',      'addon/fbsync/fbsync.php', 'fbsync_settings');
	register_hook('connector_settings_post', 'addon/fbsync/fbsync.php', 'fbsync_settings_post');
	register_hook('cron', 'addon/fbsync/fbsync.php', 'fbsync_cron');
	register_hook('follow', 'addon/fbsync/fbsync.php', 'fbsync_follow');
}

function fbsync_uninstall() {
	unregister_hook('connector_settings',      'addon/fbsync/fbsync.php', 'fbsync_settings');
	unregister_hook('connector_settings_post', 'addon/fbsync/fbsync.php', 'fbsync_settings_post');
	unregister_hook('cron', 'addon/fbsync/fbsync.php', 'fbsync_cron');
	unregister_hook('follow', 'addon/fbsync/fbsync.php', 'fbsync_follow');
}

function fbsync_follow($a, &$contact) {

	logger("fbsync_follow: Check if contact is facebook contact. ".$contact["url"], LOGGER_DEBUG);

	if (!strstr($contact["url"], "://www.facebook.com") AND !strstr($contact["url"], "://facebook.com") AND !strstr($contact["url"], "@facebook.com"))
		return;

	// contact seems to be a facebook contact, so continue
	$nickname = preg_replace("=https?://.*facebook.com/([\w.]*).*=ism", "$1", $contact["url"]);
	$nickname = str_replace("@facebook.com", "", $nickname);

	$uid = $a->user["uid"];

	$access_token = get_pconfig($uid,'facebook','access_token');

	$fql = array(
			"profile" => "SELECT id, pic_square, url, username, name FROM profile WHERE username = '$nickname'",
			"avatar" => "SELECT url FROM square_profile_pic WHERE id IN (SELECT id FROM #profile) AND size = 256");

	$url = "https://graph.facebook.com/fql?q=".urlencode(json_encode($fql))."&access_token=".$access_token;

	$feed = fetch_url($url);
	$data = json_decode($feed);

	$id = 0;

	logger("fbsync_follow: Query id for nickname ".$nickname, LOGGER_DEBUG);

	if (!is_array($data->data))
		return;

	$contactdata = new stdClass;

	foreach($data->data AS $query) {
		switch ($query->name) {
			case "profile":
				$contactdata->id =  number_format($query->fql_result_set[0]->id, 0, '', '');
				$contactdata->pic_square = $query->fql_result_set[0]->pic_square;
				$contactdata->url = $query->fql_result_set[0]->url;
				$contactdata->username = $query->fql_result_set[0]->username;
				$contactdata->name = $query->fql_result_set[0]->name;
				break;

			case "avatar":
				$contactdata->pic_square = $query->fql_result_set[0]->url;
				break;
		}
	}

	logger("fbsync_follow: Got contact for nickname ".$nickname." ".print_r($contactdata, true), LOGGER_DEBUG);

	// Create contact
	fbsync_fetch_contact($uid, $contactdata, true);

	$r = q("SELECT name,nick,url,addr,batch,notify,poll,request,confirm,poco,photo,priority,network,alias,pubkey
		FROM `contact` WHERE `uid` = %d AND `alias` = '%s'",
				intval($uid),
				dbesc("facebook::".$contactdata->id));
	if (count($r))
		$contact["contact"] = $r[0];
}


function fbsync_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/fbsync/fbsync.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = get_pconfig(local_user(),'fbsync','sync');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	$def_enabled = get_pconfig(local_user(),'fbsync','create_user');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_fbsync_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_fbsync_expanded\'); openClose(\'settings_fbsync_inflated\');">';
	$s .= '<h3>' . t('Facebook Import Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_fbsync_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_fbsync_expanded\'); openClose(\'settings_fbsync_inflated\');">';
	$s .= '<h3>' . t('Facebook Import Settings') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="fbsync-enable-wrapper">';
	$s .= '<label id="fbsync-enable-label" for="fbsync-checkbox">' . t('Import Facebook newsfeed') . '</label>';
	$s .= '<input id="fbsync-checkbox" type="checkbox" name="fbsync" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="fbsync-create_user-wrapper">';
	$s .= '<label id="fbsync-create_user-label" for="fbsync-create_user">' . t('Automatically create contacts') . '</label>';
	$s .= '<input id="fbsync-create_user" type="checkbox" name="create_user" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="fbsync-submit" name="fbsync-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}

function fbsync_settings_post(&$a,&$b) {

	if(x($_POST,'fbsync-submit')) {
		set_pconfig(local_user(),'fbsync','sync',intval($_POST['fbsync']));
		set_pconfig(local_user(),'fbsync','create_user',intval($_POST['create_user']));
	}
}

function fbsync_cron($a,$b) {
	$last = get_config('fbsync','last_poll');

	$poll_interval = intval(get_config('fbsync','poll_interval'));
	if(! $poll_interval)
		$poll_interval = FBSYNC_DEFAULT_POLL_INTERVAL;

	if($last) {
		$next = $last + ($poll_interval * 60);
		if($next > time()) {
			logger('fbsync_cron: poll intervall not reached');
			return;
		}
	}
	logger('fbsync_cron: cron_start');

	$r = q("SELECT * FROM `pconfig` WHERE `cat` = 'fbsync' AND `k` = 'sync' AND `v` = '1' ORDER BY RAND()");
	if(count($r)) {
		foreach($r as $rr) {
			fbsync_get_self($rr['uid']);

			logger('fbsync_cron: importing timeline from user '.$rr['uid']);
			fbsync_fetchfeed($a, $rr['uid']);
		}
	}

	logger('fbsync: cron_end');

	set_config('fbsync','last_poll', time());
}

function fbsync_createpost($a, $uid, $self, $contacts, $applications, $post, $create_user) {

	require_once("include/oembed.php");

	// check if it was already imported
	$r = q("SELECT * FROM `item` WHERE `uid` = %d AND `uri` = '%s' LIMIT 1",
		intval($uid),
		dbesc('fb::'.$post->post_id)
	);
	if(count($r))
		return;

	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;

	$postarray['verb'] = ACTIVITY_POST;
	$postarray['network'] =  dbesc(NETWORK_FACEBOOK);

	$postarray['uri'] = "fb::".$post->post_id;
	$postarray['thr-parent'] = $postarray['uri'];
	$postarray['parent-uri'] = $postarray['uri'];
	$postarray['plink'] = $post->permalink;

	$postarray['author-name'] = $contacts[$post->actor_id]->name;
	$postarray['author-link'] = $contacts[$post->actor_id]->url;
	$postarray['author-avatar'] = $contacts[$post->actor_id]->pic_square;

	$postarray['owner-name'] = $contacts[$post->source_id]->name;
	$postarray['owner-link'] = $contacts[$post->source_id]->url;
	$postarray['owner-avatar'] = $contacts[$post->source_id]->pic_square;

	$contact_id = 0;

	if (($post->parent_post_id != "") AND ($post->actor_id == $post->source_id)) {
		$pos = strpos($post->parent_post_id, "_");

		if ($pos != 0) {
			$user_id = substr($post->parent_post_id, 0, $pos);

			$userdata = fbsync_fetchuser($a, $uid, $user_id);

			$contact_id = $userdata["contact-id"];

			$postarray['contact-id'] = $contact_id;

			if (array_key_exists("name", $userdata) AND ($userdata["name"] != "") AND !link_compare($userdata["link"], $postarray['author-link'])) {
				$postarray['owner-name'] = $userdata["name"];
				$postarray['owner-link'] = $userdata["link"];
				$postarray['owner-avatar'] = $userdata["avatar"];

				if (!intval(get_config('system','wall-to-wall_share'))) {

					$prebody = "[share author='".$postarray['author-name'].
						"' profile='".$postarray['author-link'].
						"' avatar='".$postarray['author-avatar']."']";

					$postarray['author-name'] = $postarray['owner-name'];
					$postarray['author-link'] = $postarray['owner-link'];
					$postarray['author-avatar'] = $postarray['owner-avatar'];
				}
			}
		}
	}

	if ($contact_id == 0) {
		$contact_id = fbsync_fetch_contact($uid, $contacts[$post->source_id], $create_user);

		if (($contact_id <= 0) AND !$create_user) {
			logger('fbsync_createpost: No matching contact found. Post not imported '.print_r($post, true), LOGGER_DEBUG);
			return;
		} elseif ($contact_id == 0) {
			// This case should never happen
			logger('fbsync_createpost: No matching contact found. Using own id. (Should never happen) '.print_r($post, true), LOGGER_DEBUG);
			$contact_id = $self[0]["id"];
		}

		$postarray['contact-id'] = $contact_id;
	}

	$postarray["body"] = (isset($post->message) ? escape_tags($post->message) : '');

	$msgdata = fbsync_convertmsg($a, $postarray["body"]);

	$postarray["body"] = $msgdata["body"];
	$postarray["tag"] = $msgdata["tags"];

	$content = "";
	$type = "";

	if (isset($post->attachment->name) and isset($post->attachment->href)) {
		$oembed_data = oembed_fetch_url($post->attachment->href);
		$type = $oembed_data->type;
		$content = "[bookmark=".$post->attachment->href."]".$post->attachment->name."[/bookmark]";
	} elseif (isset($post->attachment->name) AND ($post->attachment->name != ""))
		$content = "[b]" . $post->attachment->name."[/b]";

	$quote = "";
	if(isset($post->attachment->description) and ($post->attachment->fb_object_type != "photo"))
		$quote = $post->attachment->description;

	if(isset($post->attachment->caption) and ($post->attachment->fb_object_type == "photo"))
		$quote = $post->attachment->caption;

	if ($quote.$post->attachment->href.$content.$postarray["body"] == "")
		return;

	if (isset($post->attachment->media) // AND !strstr($post->attachment->href, "://www.youtube.com/")
		//AND !strstr($post->attachment->href, "://youtu.be/")
		//AND !strstr($post->attachment->href, ".vimeo.com/"))
		AND (($type == "") OR ($type == "link"))) {
		foreach ($post->attachment->media AS $media) {
			//$media->photo->owner = number_format($media->photo->owner, 0, '', '');
			//if ($media->photo->owner != '') {
			//	$postarray['author-name'] = $contacts[$media->photo->owner]->name;
			//	$postarray['author-link'] = $contacts[$media->photo->owner]->url;
			//	$postarray['author-avatar'] = $contacts[$media->photo->owner]->pic_square;
			//}

			if (isset($media->type))
				$type = $media->type;

			if(isset($media->src) && isset($media->href) AND ($media->src != "") AND ($media->href != ""))
				$content .= "\n".'[url='.$media->href.'][img]'.fpost_cleanpicture($media->src).'[/img][/url]';
			else {
				if (isset($media->src) AND ($media->src != ""))
					$content .= "\n".'[img]'.fpost_cleanpicture($media->src).'[/img]';

				// if just a link, it may be a wall photo - check
				if(isset($post->link))
					$content .= fbpost_get_photo($media->href);
			}
		}
	}

	if ($content)
		$postarray["body"] .= "\n\n";

	if ($type)
		$postarray["body"] .= "[class=type-".$type."]";

	if ($content)
		$postarray["body"] .= $content;

	if ($quote)
		$postarray["body"] .= "\n[quote]".trim($quote)."[/quote]";

	if ($type)
		$postarray["body"] .= "[/class]";

	$postarray["body"] = trim($postarray["body"]);

	if (trim($postarray["body"]) == "")
		return;

	if ($prebody != "")
		$postarray["body"] = $prebody.$postarray["body"]."[/share]";

	$postarray['created'] = datetime_convert('UTC','UTC',date("c", $post->created_time));
	$postarray['edited'] = datetime_convert('UTC','UTC',date("c", $post->updated_time));

	$postarray['app'] = $applications[$post->app_id]->display_name;

	if ($postarray['app'] == "")
		$postarray['app'] = "Facebook";

	if(isset($post->privacy) && $post->privacy->value !== '') {
		$postarray['private'] = 1;
		$postarray['allow_cid'] = '<' . $self[0]['id'] . '>';
	}

	/*
	$postarray["location"] = $post->place->name;
	postarray["coord"] = $post->geo->coordinates[0]." ".$post->geo->coordinates[1];
	*/

	//$types = array(46, 80, 237, 247, 308);
	//if (!in_array($post->type, $types))
	//	$postarray["body"] = "Type: ".$post->type."\n".$postarray["body"];
	//print_r($post);
	//print_r($postarray);

	$item = item_store($postarray);
	logger('fbsync_createpost: User '.$self[0]["nick"].' posted feed item '.$item, LOGGER_DEBUG);
}

function fbsync_createcomment($a, $uid, $self_id, $self, $user, $contacts, $applications, $comment) {

	// check if it was already imported
	$r = q("SELECT `uri` FROM `item` WHERE `uid` = %d AND `uri` = '%s' LIMIT 1",
		intval($uid),
		dbesc('fb::'.$comment->id)
	);
	if(count($r))
		return;

	// check if it was an own post (separate posting for performance reasons)
	$r = q("SELECT `uri` FROM `item` WHERE `uid` = %d AND `extid` = '%s' LIMIT 1",
		intval($uid),
		dbesc('fb::'.$comment->id)
	);
	if(count($r))
		return;

	$parent_uri = "";

	// Fetch the parent uri (Checking if the parent exists)
	$r = q("SELECT `uri` FROM `item` WHERE `uid` = %d AND `uri` = '%s' LIMIT 1",
		intval($uid),
		dbesc('fb::'.$comment->post_id)
	);
	if(count($r))
		$parent_uri = $r[0]["uri"];

	// check if it is a reply to an own post (separate posting for performance reasons)
	$r = q("SELECT `uri` FROM `item` WHERE `uid` = %d AND `extid` = '%s' LIMIT 1",
		intval($uid),
		dbesc('fb::'.$comment->post_id)
	);
	if(count($r))
		$parent_uri = $r[0]["uri"];

	// No parent? Then quit
	if ($parent_uri == "")
		return;

	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;

	$postarray['verb'] = ACTIVITY_POST;
	$postarray['network'] =  dbesc(NETWORK_FACEBOOK);

	$postarray['uri'] = "fb::".$comment->id;
	$postarray['thr-parent'] = $parent_uri;
	$postarray['parent-uri'] = $parent_uri;
	//$postarray['plink'] = $comment->permalink;

	$contact_id = fbsync_fetch_contact($uid, $contacts[$comment->fromid], array(), false);

	if ($contact_id == -1) {
		logger('fbsync_createcomment: Contact was blocked. Comment not imported '.print_r($comment, true), LOGGER_DEBUG);
		return;
	}

	if ($contact_id <= 0)
		$contact_id = $self[0]["id"];

	if ($comment->fromid != $self_id) {
		$postarray['contact-id'] = $contact_id;
		$postarray['owner-name'] = $contacts[$comment->fromid]->name;
		$postarray['owner-link'] = $contacts[$comment->fromid]->url;
		$postarray['owner-avatar'] = $contacts[$comment->fromid]->pic_square;
	} else {
		$postarray['contact-id'] = $self[0]["id"];
		$postarray['owner-name'] = $self[0]["name"];
		$postarray['owner-link'] = $self[0]["url"];
		$postarray['owner-avatar'] = $self[0]["photo"];
	}

	$postarray['author-name'] = $postarray['owner-name'];
	$postarray['author-link'] = $postarray['owner-link'];
	$postarray['author-avatar'] = $postarray['owner-avatar'];

	$msgdata = fbsync_convertmsg($a, $comment->text);

	$postarray["body"] = $msgdata["body"];
	$postarray["tag"] = $msgdata["tags"];

	$postarray['created'] = datetime_convert('UTC','UTC',date("c", $comment->time));
	$postarray['edited'] = datetime_convert('UTC','UTC',date("c", $comment->time));

	$postarray['app'] = $applications[$comment->app_id]->display_name;

	if ($postarray['app'] == "")
		$postarray['app'] = "Facebook";

	if (trim($postarray["body"]) == "")
		return;

	$item = item_store($postarray);
	logger('fbsync_createcomment: User '.$self[0]["nick"].' posted comment '.$item, LOGGER_DEBUG);

	if ($item == 0)
		return;

	$myconv = q("SELECT `author-link`, `author-avatar`, `parent` FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `parent` != 0 AND `deleted` = 0",
		dbesc($postarray['parent-uri']),
		intval($uid)
	);

	if(count($myconv)) {
		$importer_url = $a->get_baseurl() . '/profile/' . $user[0]['nickname'];

	        $own_contact = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
        	        intval($uid), dbesc("facebook::".$self_id));

		if (!count($own_contact))
			return;

		foreach($myconv as $conv) {

			// now if we find a match, it means we're in this conversation
			if(!link_compare($conv['author-link'],$importer_url) AND !link_compare($conv['author-link'],$own_contact[0]["url"]))
				continue;

			require_once('include/enotify.php');

			$conv_parent = $conv['parent'];

			$notifyarr = array(
					'type'         => NOTIFY_COMMENT,
					'notify_flags' => $user[0]['notify-flags'],
					'language'     => $user[0]['language'],
					'to_name'      => $user[0]['username'],
					'to_email'     => $user[0]['email'],
					'uid'          => $user[0]['uid'],
					'item'         => $postarray,
					'link'             => $a->get_baseurl() . '/display/' . $user[0]['nickname'] . '/' . $item,
					'source_name'  => $postarray['author-name'],
					'source_link'  => $postarray['author-link'],
					'source_photo' => $postarray['author-avatar'],
					'verb'         => ACTIVITY_POST,
					'otype'        => 'item',
					'parent'       => $conv_parent,
			);

			notification($notifyarr);

			// only send one notification
			break;
		}
	}
}

function fbsync_createlike($a, $uid, $self_id, $self, $contacts, $like) {

	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc("fb::".$like->post_id),
				intval($uid)
		);

        if (count($r))
                $orig_post = $r[0];
	else
		return;

	// If we posted the like locally, it will be found with our url, not the FB url.

	$second_url = (($like->user_id == $self_id) ? $self[0]["url"] : $contacts[$like->user_id]->url);

	$r = q("SELECT * FROM `item` WHERE `parent-uri` = '%s' AND `uid` = %d AND `verb` = '%s'
		AND (`author-link` = '%s' OR `author-link` = '%s') LIMIT 1",
		dbesc($orig_post["uri"]),
		intval($uid),
		dbesc(ACTIVITY_LIKE),
		dbesc($contacts[$like->user_id]->url),
		dbesc($second_url)
	);

	if (count($r))
		return;

	$contact_id = fbsync_fetch_contact($uid, $contacts[$like->user_id], array(), false);

	if ($contact_id <= 0)
		$contact_id = $self[0]["id"];

	$likedata = array();
        $likedata['parent'] = $orig_post['id'];
        $likedata['verb'] = ACTIVITY_LIKE;
	$likedate['network'] =  dbesc(NETWORK_FACEBOOK);
        $likedata['gravity'] = 3;
        $likedata['uid'] = $uid;
        $likedata['wall'] = 0;
        $likedata['uri'] = item_new_uri($a->get_baseurl(), $uid);
        $likedata['parent-uri'] = $orig_post["uri"];
        $likedata['app'] = "Facebook";

	if ($like->user_id != $self_id) {
		$likedata['contact-id'] = $contact_id;
		$likedata['author-name'] = $contacts[$like->user_id]->name;
		$likedata['author-link'] = $contacts[$like->user_id]->url;
		$likedata['author-avatar'] = $contacts[$like->user_id]->pic_square;
	} else {
		$likedata['contact-id'] = $self[0]["id"];
		$likedata['author-name'] = $self[0]["name"];
		$likedata['author-link'] = $self[0]["url"];
		$likedata['author-avatar'] = $self[0]["photo"];
	}

	$author  = '[url=' . $likedata['author-link'] . ']' . $likedata['author-name'] . '[/url]';

	$objauthor =  '[url=' . $orig_post['author-link'] . ']' . $orig_post['author-name'] . '[/url]';
	$post_type = t('status');

	$plink = '[url=' . $orig_post['plink'] . ']' . $post_type . '[/url]';
        $likedata['object-type'] = ACTIVITY_OBJ_NOTE;

        $likedata['body'] = sprintf( t('%1$s likes %2$s\'s %3$s'), $author, $objauthor, $plink);

        $likedata['object'] = '<object><type>' . ACTIVITY_OBJ_NOTE . '</type><local>1</local>' .
                '<id>' . $orig_post['uri'] . '</id><link>' . xmlify('<link rel="alternate" type="text/html" href="' . xmlify($orig_post['plink']) . '" />') . '</link><title>' . $orig_post['title'] . '</title><content>' . $orig_post['body'] . '</content></object>';


	$r = q("SELECT * FROM `item` WHERE `parent-uri` = '%s' AND `author-link` = '%s' AND `verb` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($likedata['parent-uri']),
				dbesc($likedata['author-link']),
				dbesc(ACTIVITY_LIKE),
				intval($uid)
		);

        if (count($r))
		return;

	$item = item_store($likedata);
	logger('fbsync_createlike: liked item '.$item.'. User '.$self[0]["nick"], LOGGER_DEBUG);
}

function fbsync_fetch_contact($uid, $contact, $create_user) {

	// Check if the unique contact is existing
	// To-Do: only update once a while
	$r = q("SELECT id FROM unique_contacts WHERE url='%s' LIMIT 1",
		dbesc(normalise_link($contact->url)));

	if (count($r) == 0)
		q("INSERT INTO unique_contacts (url, name, nick, avatar) VALUES ('%s', '%s', '%s', '%s')",
			dbesc(normalise_link($contact->url)),
                        dbesc($contact->name),
                        dbesc($contact->username),
			dbesc($contact->pic_square));
	else
		q("UPDATE unique_contacts SET name = '%s', nick = '%s', avatar = '%s' WHERE url = '%s'",
                        dbesc($contact->name),
                        dbesc($contact->username),
			dbesc($contact->pic_square),
			dbesc(normalise_link($contact->url)));

        $r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
                intval($uid), dbesc("facebook::".$contact->id));

        if(!count($r) AND !$create_user)
                return(0);

        if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
                logger("fbsync_fetch_contact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
                return(-1);
        }

	$avatarpicture = $contact->pic_square;

        if(!count($r)) {
                // create contact record
                q("INSERT INTO `contact` (`uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
                                        `name`, `nick`, `photo`, `network`, `rel`, `priority`,
                                        `writable`, `blocked`, `readonly`, `pending`)
                                        VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0)",
                        intval($uid),
                        dbesc(datetime_convert()),
                        dbesc($contact->url),
                        dbesc(normalise_link($contact->url)),
                        dbesc($contact->username."@facebook.com"),
                        dbesc("facebook::".$contact->id),
                        dbesc(''),
                        dbesc("facebook::".$contact->id),
                        dbesc($contact->name),
                        dbesc($contact->username),
                        dbesc($avatarpicture),
                        dbesc(NETWORK_FACEBOOK),
                        intval(CONTACT_IS_FRIEND),
                        intval(1),
                        intval(1)
                );

                $r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
                        dbesc("facebook::".$contact->id),
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

                $photos = import_profile_photo($avatarpicture,$uid,$contact_id);

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
                // update profile photos once every 12 hours as we have no notification of when they change.
                $update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

                // check that we have all the photos, this has been known to fail on occasion
                if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {

                        logger("fbsync_fetch_contact: Updating contact ".$contact->username, LOGGER_DEBUG);

                        require_once("Photo.php");

                        $photos = import_profile_photo($avatarpicture, $uid, $r[0]['id']);

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
                                dbesc($contact->url),
                                dbesc(normalise_link($contact->url)),
                                dbesc($contact->username."@facebook.com"),
                                dbesc($contact->name),
                                dbesc($contact->username),
                                intval($r[0]['id'])
                        );
                }
        }
        return($r[0]["id"]);
}

function fbsync_get_self($uid) {
	$access_token = get_pconfig($uid,'facebook','access_token');
	if(! $access_token)
		return;
	$s = fetch_url('https://graph.facebook.com/me/?access_token=' . $access_token);
	if($s) {
		$j = json_decode($s);
		set_pconfig($uid,'fbsync','self_id',(string) $j->id);
	}
}

function fbsync_convertmsg($a, $body) {
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

function fbsync_fetchuser($a, $uid, $id) {
	$access_token = get_pconfig($uid,'facebook','access_token');
	$self_id = get_pconfig($uid,'fbsync','self_id');

	$user = array();

	$contact = q("SELECT `id`, `name`, `url`, `photo`  FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid), dbesc("facebook::".$id));

	if (count($contact)) {
		$user["contact-id"] = $contact[0]["id"];
		$user["name"] = $contact[0]["name"];
		$user["link"] = $contact[0]["url"];
		$user["avatar"] = $contact[0]["photo"];

		return($user);
	}

	$own_contact = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid), dbesc("facebook::".$self_id));

	if (!count($own_contact))
		return($user);

	$fql = "SELECT name, url, pic_square FROM profile WHERE id = ".$id;

	$url = "https://graph.facebook.com/fql?q=".urlencode($fql)."&access_token=".$access_token;

	$feed = fetch_url($url);
	$data = json_decode($feed);

	if (is_array($data->data)) {
		$user["contact-id"] = $own_contact[0]["id"];
		$user["name"] = $data->data[0]->name;
		$user["link"] = $data->data[0]->url;
		$user["avatar"] = $data->data[0]->pic_square;
	}
	return($user);
}

function fbsync_fetchfeed($a, $uid) {
	$access_token = get_pconfig($uid,'facebook','access_token');
	$last_updated = get_pconfig($uid,'fbsync','last_updated');
	$self_id = get_pconfig($uid,'fbsync','self_id');

	$create_user = get_pconfig($uid, 'fbsync', 'create_user');
	$do_likes = get_config('fbsync', 'do_likes');

	$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid)
	);

	$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
		intval($uid)
	);
	if(! count($user))
		return;

	require_once('include/items.php');

	//if ($last_updated == "")
		$last_updated = 0;

	logger("fbsync_fetchfeed: fetching content for user ".$self_id);

	$fql = array(
		"posts" => "SELECT action_links, actor_id, app_data, app_id, attachment, attribution, comment_info, created_time, filter_key, like_info, message, message_tags, parent_post_id, permalink, place, post_id, privacy, share_count, share_info, source_id, subscribed, tagged_ids, type, updated_time, with_tags FROM stream where filter_key in (SELECT filter_key FROM stream_filter WHERE uid=me() AND type='newsfeed') AND updated_time > $last_updated ORDER BY updated_time DESC LIMIT 500",
		"comments" => "SELECT app_id, attachment, post_id, id, likes, fromid, time, text, text_tags, user_likes, likes FROM comment WHERE post_id IN (SELECT post_id FROM #posts) ORDER BY time DESC LIMIT 500",
		"profiles" => "SELECT id, name, username, url, pic_square FROM profile WHERE id IN (SELECT actor_id FROM #posts) OR id IN (SELECT fromid FROM #comments) OR id IN (SELECT source_id FROM #posts) LIMIT 500",
		"applications" => "SELECT app_id, display_name FROM application WHERE app_id IN (SELECT app_id FROM #posts) OR app_id IN (SELECT app_id FROM #comments) LIMIT 500",
		"avatars" => "SELECT id, real_size, size, url FROM square_profile_pic WHERE id IN (SELECT id FROM #profiles) AND size = 256 LIMIT 500");

	if ($do_likes) {
		$fql["likes"] = "SELECT post_id, user_id FROM like WHERE post_id IN (SELECT post_id FROM #posts)";
		$fql["profiles"] .= " OR id IN (SELECT user_id FROM #likes)";
	}

	$url = "https://graph.facebook.com/fql?q=".urlencode(json_encode($fql))."&access_token=".$access_token;

	$feed = fetch_url($url);

	$data = json_decode($feed);

	if (!is_array($data->data)) {
		logger("fbsync_fetchfeed: Error fetching data for user ".$uid.": ".print_r($data, true));
		return;
	}

	$posts = array();
	$comments = array();
	$likes = array();
	$profiles = array();
	$applications = array();
	$avatars = array();

	foreach($data->data AS $query) {
		switch ($query->name) {
			case "posts":
				$posts = array_reverse($query->fql_result_set);
				break;
			case "comments":
				$comments = $query->fql_result_set;
				break;
			case "likes":
				$likes = $query->fql_result_set;
				break;
			case "profiles":
				$profiles = $query->fql_result_set;
				break;
			case "applications":
				$applications = $query->fql_result_set;
				break;
			case "avatars":
				$avatars = $query->fql_result_set;
				break;
		}
	}

	$square_avatars = array();
	$contacts = array();
	$application_data = array();
	$post_data = array();
	$comment_data = array();

	foreach ($avatars AS $avatar) {
		$avatar->id = number_format($avatar->id, 0, '', '');
		$square_avatars[$avatar->id] = $avatar;
	}
	unset($avatars);

	foreach ($profiles AS $profile) {
		$profile->id = number_format($profile->id, 0, '', '');

		if ($square_avatars[$profile->id]->url != "")
			$profile->pic_square = $square_avatars[$profile->id]->url;

		$contacts[$profile->id] = $profile;
	}
	unset($profiles);
	unset($square_avatars);

	foreach ($applications AS $application) {
		$application->app_id = number_format($application->app_id, 0, '', '');
		$application_data[$application->app_id] = $application;
	}
	unset($applications);

	foreach ($posts AS $post) {
		$post->actor_id = number_format($post->actor_id, 0, '', '');
		$post->source_id = number_format($post->source_id, 0, '', '');
		$post->app_id = number_format($post->app_id, 0, '', '');
		$post_data[$post->post_id] = $post;
	}
	unset($posts);

	foreach($comments AS $comment) {
		$comment->fromid = number_format($comment->fromid, 0, '', '');
		$comment_data[$comment->id] = $comment;
	}
	unset($comments);

	foreach ($post_data AS $post) {
		if ($post->updated_time > $last_updated)
			$last_updated = $post->updated_time;

		fbsync_createpost($a, $uid, $self, $contacts, $application_data, $post, $create_user);
	}

	foreach ($comment_data AS $comment) {
		fbsync_createcomment($a, $uid, $self_id, $self, $user, $contacts, $application_data, $comment);
	}

	foreach($likes AS $like) {
		$like->user_id = number_format($like->user_id, 0, '', '');

		fbsync_createlike($a, $uid, $self_id, $self, $contacts, $like);
	}

	set_pconfig($uid,'fbsync','last_updated', $last_updated);
}
?>
