<?php

/**
 * Name: G+ Post
 * Description: Posts to a Google+ page with the help of Hootsuite
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function gpluspost_install() {
	register_hook('post_local',           'addon/gpluspost/gpluspost.php', 'gpluspost_post_local');
	register_hook('notifier_normal',      'addon/gpluspost/gpluspost.php', 'gpluspost_send');
	register_hook('jot_networks',         'addon/gpluspost/gpluspost.php', 'gpluspost_jot_nets');
	register_hook('connector_settings',      'addon/gpluspost/gpluspost.php', 'gpluspost_settings');
	register_hook('connector_settings_post', 'addon/gpluspost/gpluspost.php', 'gpluspost_settings_post');
        register_hook('queue_predeliver', 'addon/gpluspost/gpluspost.php', 'gpluspost_queue_hook');
}


function gpluspost_uninstall() {
	unregister_hook('post_local',       'addon/gpluspost/gpluspost.php', 'gpluspost_post_local');
	unregister_hook('notifier_normal',  'addon/gpluspost/gpluspost.php', 'gpluspost_send');
	unregister_hook('jot_networks',     'addon/gpluspost/gpluspost.php', 'gpluspost_jot_nets');
	unregister_hook('connector_settings',      'addon/gpluspost/gpluspost.php', 'gpluspost_settings');
	unregister_hook('connector_settings_post', 'addon/gpluspost/gpluspost.php', 'gpluspost_settings_post');
        unregister_hook('queue_predeliver', 'addon/gpluspost/gpluspost.php', 'gpluspost_queue_hook');
}

function gpluspost_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$post = get_pconfig(local_user(),'gpluspost','post');
	if(intval($post) == 1) {
		$defpost = get_pconfig(local_user(),'gpluspost','post_by_default');
		$selected = ((intval($defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="gpluspost_enable"' . $selected . ' value="1" /> '
			. t('Post to Google+') . '</div>';
    }
}

function gpluspost_nextscripts() {
	$a = get_app();
	return file_exists($a->get_basepath()."/addon/gpluspost/postToGooglePlus.php");
}

function gpluspost_settings(&$a,&$s) {

	if(! local_user())
		return;

	$result = q("SELECT `installed` FROM `addon` WHERE `name` = 'fromgplus' AND `installed`");
	$fromgplus_enabled = count($result) > 0;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/gpluspost/gpluspost.css' . '" media="all" />' . "\r\n";

	$enabled = get_pconfig(local_user(),'gpluspost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = get_pconfig(local_user(),'gpluspost','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$noloop_enabled = get_pconfig(local_user(),'gpluspost','no_loop_prevention');
	$noloop_checked = (($noloop_enabled) ? ' checked="checked" ' : '');

	$skip_enabled = get_pconfig(local_user(),'gpluspost','skip_without_link');
	$skip_checked = (($skip_enabled) ? ' checked="checked" ' : '');

	$mirror_enable_checked = (intval(get_pconfig(local_user(),'fromgplus','enable')) ? ' checked="checked"' : '');
	$mirror_account = get_pconfig(local_user(),'fromgplus','account');

	$username = get_pconfig(local_user(), 'gpluspost', 'username');
	$password = get_pconfig(local_user(), 'gpluspost', 'password');
	$page = get_pconfig(local_user(), 'gpluspost', 'page');

	if ($fromgplus_enabled)
		$title = "Google+ Export/Mirror";
	else
		$title = "Google+ Export";

	$s .= '<span id="settings_gpluspost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_gpluspost_expanded\'); openClose(\'settings_gpluspost_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/googleplus.png" /><h3 class="connector">'. t($title).'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_gpluspost_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_gpluspost_expanded\'); openClose(\'settings_gpluspost_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/googleplus.png" /><h3 class="connector">'. t($title).'</h3>';
	$s .= '</span>';
	$s .= '<div id="gpluspost-enable-wrapper">';

	$s .= '<label id="gpluspost-enable-label" for="gpluspost-checkbox">' . t('Enable Google+ Post Plugin') . '</label>';
	$s .= '<input id="gpluspost-checkbox" type="checkbox" name="gpluspost" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	if (gpluspost_nextscripts()) {
		/*
		// To-Do: Option to check the credentials if requested
		if (($username != "") AND ($password != "")) {
			require_once("addon/googleplus/postToGooglePlus.php");
			$loginError = doConnectToGooglePlus2($username, $password);
			if ($loginError)
				$s .= '<p>Login Error. Please enter the correct credentials.</p>';
		}*/

		$s .= '<div id="gpluspost-username-wrapper">';
		$s .= '<label id="gpluspost-username-label" for="gpluspost-username">' . t('Google+ username') . '</label>';
		$s .= '<input id="gpluspost-username" type="text" name="username" value="' . $username . '" />';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="gpluspost-password-wrapper">';
		$s .= '<label id="gpluspost-password-label" for="gpluspost-password">' . t('Google+ password') . '</label>';
		$s .= '<input id="gpluspost-password" type="password" name="password" value="' . $password . '" />';
		$s .= '</div><div class="clear"></div>';

		$s .= '<div id="gpluspost-page-wrapper">';
		$s .= '<label id="gpluspost-page-label" for="gpluspost-page">' . t('Google+ page number') . '</label>';
		$s .= '<input id="gpluspost-page" type="text" name="page" value="' . $page . '" />';
		$s .= '</div><div class="clear"></div>';
	}

	$s .= '<div id="gpluspost-bydefault-wrapper">';
	$s .= '<label id="gpluspost-bydefault-label" for="gpluspost-bydefault">' . t('Post to Google+ by default') . '</label>';
	$s .= '<input id="gpluspost-bydefault" type="checkbox" name="gpluspost_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="gpluspost-noloopprevention-wrapper">';
	$s .= '<label id="gpluspost-noloopprevention-label" for="gpluspost-noloopprevention">' . t('Do not prevent posting loops') . '</label>';
	$s .= '<input id="gpluspost-noloopprevention" type="checkbox" name="gpluspost_noloopprevention" value="1" ' . $noloop_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	if (!gpluspost_nextscripts()) {
		$s .= '<div id="gpluspost-skipwithoutlink-wrapper">';
		$s .= '<label id="gpluspost-skipwithoutlink-label" for="gpluspost-skipwithoutlink">' . t('Skip messages without links') . '</label>';
		$s .= '<input id="gpluspost-skipwithoutlink" type="checkbox" name="gpluspost_skipwithoutlink" value="1" ' . $skip_checked . '/>';
		$s .= '</div><div class="clear"></div>';
	}

	if ($fromgplus_enabled) {
		$s .= '<div id="gpluspost-mirror-wrapper">';
		$s .= '<label id="gpluspost-mirror-label" for="gpluspost-mirror">'.t('Mirror all public posts').'</label>';
		$s .= '<input id="gpluspost-mirror" type="checkbox" name="fromgplus-enable" value="1"'.$mirror_enable_checked.' />';
		$s .= '</div><div class="clear"></div>';
		$s .= '<div id="gpluspost-mirroraccount-wrapper">';
		$s .= '<label id="gpluspost-account-label" for="gpluspost-account">'.t('Mirror Google Account ID').' </label>';
		$s .= '<input id="gpluspost-account" type="text" name="fromgplus-account" value="'.$mirror_account.'" />';
		$s .= '</div><div class="clear"></div>';
	}

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="gpluspost-submit" name="gpluspost-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';

	if (gpluspost_nextscripts()) {
		$s .= "<p>If the plugin doesn't work or if it stopped, please login to Google+, then unlock your account by following this <a href='https://www.google.com/accounts/UnlockCaptcha'>link</a>. ";
		$s .= 'At this page please click on "Continue". Then your posts should arrive in several minutes.</p>';
	} else {
		$s .= '<p>Register an account at <a href="https://hootsuite.com">Hootsuite</a>, add your G+ page and add the feed-url there.<br />';
		$s .= 'Feed-url: '.$a->get_baseurl().'/gpluspost/'.urlencode($a->user["nickname"]).'</p>';
	}
	$s .= '</div>';
}

function gpluspost_settings_post(&$a,&$b) {

	if(x($_POST,'gpluspost-submit')) {
		set_pconfig(local_user(),'gpluspost','post',intval($_POST['gpluspost']));
		set_pconfig(local_user(),'gpluspost','post_by_default',intval($_POST['gpluspost_bydefault']));
		set_pconfig(local_user(),'gpluspost','no_loop_prevention',intval($_POST['gpluspost_noloopprevention']));

		if (!gpluspost_nextscripts()) {
			set_pconfig(local_user(),'gpluspost','skip_without_link',intval($_POST['gpluspost_skipwithoutlink']));
		} else {
			set_pconfig(local_user(),'gpluspost','username',trim($_POST['username']));
			set_pconfig(local_user(),'gpluspost','password',trim($_POST['password']));
			set_pconfig(local_user(),'gpluspost','page',trim($_POST['page']));
		}

		$result = q("SELECT `installed` FROM `addon` WHERE `name` = 'fromgplus' AND `installed`");
		if (count($result) > 0) {
			set_pconfig(local_user(),'fromgplus','account',trim($_POST['fromgplus-account']));
			$enable = ((x($_POST,'fromgplus-enable')) ? intval($_POST['fromgplus-enable']) : 0);
			set_pconfig(local_user(),'fromgplus','enable', $enable);
		}
	}
}

function gpluspost_post_local(&$a,&$b) {

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

	$post   = intval(get_pconfig(local_user(),'gpluspost','post'));

	$enable = (($post && x($_REQUEST,'gpluspost_enable')) ? intval($_REQUEST['gpluspost_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'gpluspost','post_by_default')))
		$enable = 1;

	if(!$enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'gplus';
}

function gpluspost_send(&$a,&$b) {

	logger('gpluspost_send: invoked for post '.$b['id']." ".$b['app']);

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'gplus'))
		return;

	if($b['parent'] != $b['id'])
		return;

	// if post comes from Google+ don't send it back
	if (!get_pconfig($b["uid"],'gpluspost','no_loop_prevention') and ($b['app'] == "Google+"))
                return;

	// Always do the export via RSS-Feed (even if NextScripts is enabled), since it doesn't hurt
	$itemlist = get_pconfig($b["uid"],'gpluspost','itemlist');
	$items = explode(",", $itemlist);

	$i = 0;
	$newitems = array($b['id']);
	foreach ($items AS $item)
		if ($i++ < 9)
			$newitems[] = $item;

	$itemlist = implode(",", $newitems);

	logger('gpluspost_send: new itemlist: '.$itemlist." for uid ".$b["uid"]);

	set_pconfig($b["uid"],'gpluspost','itemlist', $itemlist);

	// Posting via NextScripts
	if (gpluspost_nextscripts()) {
		$username = get_pconfig($b['uid'],'gpluspost','username');
	        $password = get_pconfig($b['uid'],'gpluspost','password');
	        $page = get_pconfig($b['uid'],'gpluspost','page');

	        $success = false;

	        if($username && $password) {
	                require_once("addon/gpluspost/postToGooglePlus.php");

	                $data = gpluspost_createmsg($b);

	                logger('gpluspost_send: data: '.print_r($data, true), LOGGER_DEBUG);

	                $loginError = doConnectToGooglePlus2($username, $password);
	                if (!$loginError) {
	                        if ($data["link"] != "")
        	                        $lnk = doGetGoogleUrlInfo2($data["link"]);
                	        elseif ($data["image"] != "")
                        	        $lnk = array('img'=>$data["image"]);
	                        else
        	                        $lnk = "";

                	        // Send a special blank to identify the post through the "fromgplus" addon
                        	$blank = html_entity_decode("&#x00A0;", ENT_QUOTES, 'UTF-8');

	                        doPostToGooglePlus2($data["msg"].$blank, $lnk, $page);

        	                $success = true;

	                        logger('gpluspost_send: '.$b['uid'].' success', LOGGER_DEBUG);
        	        } else
                	        logger('gpluspost_send: '.$b['uid'].' failed '.$loginError, LOGGER_DEBUG);

	                if (!$success) {
        	                logger('gpluspost_send: requeueing '.$b['uid'], LOGGER_DEBUG);

                	        $r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $b['uid']);
                        	if (count($r))
                                	$a->contact = $r[0]["id"];

	                        $s = serialize(array('url' => $url, 'item' => $b['id'], 'post' => $data));
        	                require_once('include/queue_fn.php');
                	        add_to_queue($a->contact,NETWORK_GPLUS,$s);
                        	notice(t('Google+ post failed. Queued for retry.').EOL);
	                }
	        } else
	                        logger('gpluspost_send: '.$b['uid'].' missing username or password', LOGGER_DEBUG);
	}

}

function gpluspost_createmsg($b) {
        require_once("include/bbcode.php");
        require_once("include/html2plain.php");

        $b['body'] = bb_CleanPictureLinks($b['body']);

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
                $body = "*".$b["title"]."*\n\n".$body;

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

                $pos2 = strpos($body2, "[/bookmark]");
                if ($pos2)
                        $body2 = substr($body2, $pos2 + 11);

                $body = $body1.$body2;
        }

        // Add some newlines so that the message could be cut better
        $body = str_replace(array("[quote", "[bookmark", "[/bookmark]", "[/quote]"),
                                array("\n[quote", "\n[bookmark", "[/bookmark]\n", "[/quote]\n"), $body);

        // remove the recycle signs and the names since they aren't helpful on twitter
        // recycle 1
        $recycle = html_entity_decode("&#x2672; ", ENT_QUOTES, 'UTF-8');
        $body = preg_replace( '/'.$recycle.'\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', "\n", $body);

        // remove the share element
        //$body = preg_replace("/\[share(.*?)\](.*?)\[\/share\]/ism","\n\n$2\n\n",$body);

        $body = preg_replace("(\[b\](.*?)\[\/b\])ism",'*$1*',$body);
        $body = preg_replace("(\[i\](.*?)\[\/i\])ism",'_$1_',$body);
        $body = preg_replace("(\[s\](.*?)\[\/s\])ism",'-$1-',$body);

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

        // Removing multiple spaces - again
        while (strpos($msg, "  ") !== false)
                $msg = str_replace("  ", " ", $msg);

        if ($msglink != "") {
                // Looking if the link points to an image
                $img_str = fetch_url($msglink);

                $tempfile = tempnam(get_config("system","temppath"), "cache");
                file_put_contents($tempfile, $img_str);
                $mime = image_type_to_mime_type(exif_imagetype($tempfile));
                unlink($tempfile);
        } else
                $mime = "";

        if (($image == $msglink) OR (substr($mime, 0, 6) == "image/"))
                return(array("msg"=>trim($msg), "link"=>"", "image"=>$msglink));
        else
                return(array("msg"=>trim($msg), "link"=>$msglink, "image"=>""));
}

function gpluspost_queue_hook(&$a,&$b) {

        $qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
                dbesc(NETWORK_GPLUS)
        );
        if(! count($qi))
                return;

        require_once('include/queue_fn.php');

        foreach($qi as $x) {
                if($x['network'] !== NETWORK_GPLUS)
                        continue;

                logger('gpluspost_queue: run');

                $r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid` 
                        WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
                        intval($x['cid'])
                );
                if(! count($r))
                        continue;

                $userdata = $r[0];

                //logger('gpluspost_queue: fetching userdata '.print_r($userdata, true));

                $username = get_pconfig($userdata['uid'],'gpluspost','username');
                $password = get_pconfig($userdata['uid'],'gpluspost','password');
                $page = get_pconfig($userdata['uid'],'gpluspost','page');

                $success = false;

                if($username && $password) {
                        require_once("addon/googleplus/postToGooglePlus.php");

                        logger('gpluspost_queue: able to post for user '.$username);

                        $z = unserialize($x['content']);

                        $data = $z['post'];
                        // $z['url']

                        logger('gpluspost_send: data: '.print_r($data, true), LOGGER_DATA);

                        $loginError = doConnectToGooglePlus2($username, $password);
                        if (!$loginError) {
                                if ($data["link"] != "")
                                        $lnk = doGetGoogleUrlInfo2($data["link"]);
                                elseif ($data["image"] != "")
                                        $lnk = array('img'=>$data["image"]);
                                else
                                        $lnk = "";

                                // Send a special blank to identify the post through the "fromgplus" addon
                                $blank = html_entity_decode("&#x00A0;", ENT_QUOTES, 'UTF-8');

                                doPostToGooglePlus2($data["msg"].$blank, $lnk, $page);

                                logger('gpluspost_queue: send '.$userdata['uid'].' success', LOGGER_DEBUG);

                                $success = true;

                                remove_queue_item($x['id']);
                        } else
                                logger('gpluspost_queue: send '.$userdata['uid'].' failed '.$loginError, LOGGER_DEBUG);
                } else
                        logger('gpluspost_queue: send '.$userdata['uid'].' missing username or password', LOGGER_DEBUG);

                if (!$success) {
                        logger('gpluspost_queue: delayed');
                        update_queue_time($x['id']);
                }
        }
}

function gpluspost_module() {}

function gpluspost_init() {
	global $a, $_SERVER;

	$uid = 0;

	if (isset($a->argv[1])) {
		$uid = (int)$a->argv[1];
		if ($uid == 0) {
			$contacts = q("SELECT `username`, `uid` FROM `user` WHERE `nickname` = '%s' LIMIT 1", dbesc($a->argv[1]));
			if ($contacts) {
				$uid = $contacts[0]["uid"];
				$nick = $a->argv[1];
			}
		} else {
			$contacts = q("SELECT `username` FROM `user` WHERE `uid`=%d LIMIT 1", intval($uid));
			$nick = $uid;
		}
	}

	header("content-type: application/atom+xml");
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
	echo "\t".'<title type="html"><![CDATA['.$a->config['sitename'].']]></title>'."\n";
	if ($uid != 0) {
		echo "\t".'<subtitle type="html"><![CDATA['.$contacts[0]["username"]."]]></subtitle>\n";
		echo "\t".'<link rel="self" href="'.$a->get_baseurl().'/gpluspost/'.$nick.'"/>'."\n";
	} else
		echo "\t".'<link rel="self" href="'.$a->get_baseurl().'/gpluspost"/>'."\n";
	echo "\t<id>".$a->get_baseurl()."/</id>\n";
	echo "\t".'<link rel="alternate" type="text/html" href="'.$a->get_baseurl().'"/>'."\n";
	echo "\t<updated>".date("c")."</updated>\n"; // To-Do
	// <rights>Copyright ... </rights>
	echo "\t".'<generator uri="'.$a->get_baseurl().'">'.$a->config['sitename'].'</generator>'."\n";

	if ($uid != 0) {
		$itemlist = get_pconfig($uid,'gpluspost','itemlist');
		$items = explode(",", $itemlist);

		foreach ($items AS $item)
			gpluspost_feeditem($item, $uid);
	} else {
		$items = q("SELECT `id` FROM `item` FORCE INDEX (`received`) WHERE `item`.`visible` = 1 AND `item`.`deleted` = 0 and `item`.`moderated` = 0 AND `item`.`allow_cid` = ''  AND `item`.`allow_gid` = '' AND `item`.`deny_cid`  = '' AND `item`.`deny_gid`  = '' AND `item`.`private` = 0 AND `item`.`wall` = 1 AND `item`.`id` = `item`.`parent` ORDER BY `received` DESC LIMIT 10");
		foreach ($items AS $item)
			gpluspost_feeditem($item["id"], $uid);
	}
	echo "</feed>\n";
	killme();
}

function gpluspost_feeditem($pid, $uid) {
	global $a;

	require_once('include/api.php');
	require_once('include/bbcode.php');
	require_once("include/html2plain.php");
	require_once("include/network.php");

	$skipwithoutlink = get_pconfig($uid,'gpluspost','skip_without_link');

	$items = q("SELECT `uri`, `plink`, `author-link`, `author-name`, `created`, `edited`, `id`, `title`, `body` from `item` WHERE id=%d", intval($pid));
	foreach ($items AS $item) {

		$item['body'] = bb_CleanPictureLinks($item['body']);

		$item['body'] = bb_remove_share_information($item['body'], true);

	        if ($item["title"] != "")
        	        $item['body'] = "*".$item["title"]."*\n\n".$item['body'];

		// Looking for the first image
		$image = '';
		if(preg_match("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/is",$item['body'],$matches))
			$image = $matches[3];

		if ($image == '')
			if(preg_match("/\[img\](.*?)\[\/img\]/is",$item['body'],$matches))
				$image = $matches[1];

		$multipleimages = (strpos($item['body'], "[img") != strrpos($item['body'], "[img"));

		// When saved into the database the content is sent through htmlspecialchars
		// That means that we have to decode all image-urls
		$image = htmlspecialchars_decode($image);

		$link = '';
		// look for bookmark-bbcode and handle it with priority
		if(preg_match("/\[bookmark\=([^\]]*)\](.*?)\[\/bookmark\]/is",$item['body'],$matches))
			$link = $matches[1];

		$multiplelinks = (strpos($item['body'], "[bookmark") != strrpos($item['body'], "[bookmark"));

		$body = $item['body'];

	        $body = preg_replace("(\[b\](.*?)\[\/b\])ism",'*$1*',$body);
	        $body = preg_replace("(\[i\](.*?)\[\/i\])ism",'_$1_',$body);
	        $body = preg_replace("(\[s\](.*?)\[\/s\])ism",'-$1-',$body);

		// At first convert the text to html
		$html = bbcode(api_clean_plain_items($body), false, false, 2);

		// Then convert it to plain text
		$msg = trim(html2plain($html, 0, true));
		$msg = html_entity_decode($msg,ENT_QUOTES,'UTF-8');

		// If there is no bookmark element then take the first link
		if ($link == '') {
			$links = collecturls($html);
			if (sizeof($links) > 0) {
				reset($links);
				$link = current($links);
			}
			$multiplelinks = (sizeof($links) > 1);

			if ($multiplelinks) {
				$html2 = bbcode($msg, false, false);
				$links2 = collecturls($html2);
				if (sizeof($links2) > 0) {
					reset($links2);
					$link = current($links2);
					$multiplelinks = (sizeof($links2) > 1);
				}
			}
		}

		$msglink = "";
		if ($multiplelinks)
			$msglink = $item["plink"];
		else if ($link != "")
			$msglink = $link;
		else if ($multipleimages)
			$msglink = $item["plink"];
		else if ($image != "")
			$msglink = $image;

		if (($msglink == "") AND $skipwithoutlink)
			continue;
		else if ($msglink == "")
			$msglink = $item["plink"];

		// Fetching the title - or the first line
		if ($item["title"] != "")
			$title = $item["title"];
		else {
			$lines = explode("\n", $msg);
			$title = $lines[0];
		}

		//if ($image != $msglink)
		//	$html = trim(str_replace($msglink, "", $html));

		$title = trim(str_replace($msglink, "", $title));

		$msglink = original_url($msglink);

		if ($uid == 0)
			$title = $item["author-name"].": ".$title;

		$msglink = htmlspecialchars(html_entity_decode($msglink));

		if (strpos($msg, $msglink) == 0)
			$msg .= "\n".$msglink;

		$msg = nl2br($msg);

		$title = str_replace("&", "&amp;", $title);
		//$html = str_replace("&", "&amp;", $html);

		echo "\t".'<entry xmlns="http://www.w3.org/2005/Atom">'."\n";
		echo "\t\t".'<title type="html" xml:space="preserve"><![CDATA['.$title."]]></title>\n";
		echo "\t\t".'<link rel="alternate" type="text/html" href="'.$msglink.'" />'."\n";
		// <link rel="enclosure" type="audio/mpeg" length="1337" href="http://example.org/audio/ph34r_my_podcast.mp3"/>
		echo "\t\t<id>".$item["uri"]."</id>\n";
		echo "\t\t<updated>".date("c", strtotime($item["edited"]))."</updated>\n";
		echo "\t\t<published>".date("c", strtotime($item["created"]))."</published>\n";
		echo "\t\t<author>\n\t\t\t<name><![CDATA[".$item["author-name"]."]]></name>\n";
		echo "\t\t\t<uri>".$item["author-link"]."</uri>\n\t\t</author>\n";
		//echo '<content type="image/png" src="http://media.example.org/the_beach.png"/>';
		echo "\t\t".'<content type="html" xml:space="preserve" xml:base="'.$item["plink"].'"><![CDATA['.$msg."]]></content>\n";
		echo "\t</entry>\n";
	}
}
