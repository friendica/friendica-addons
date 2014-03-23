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
}


function gpluspost_uninstall() {
	unregister_hook('post_local',       'addon/gpluspost/gpluspost.php', 'gpluspost_post_local');
	unregister_hook('notifier_normal',  'addon/gpluspost/gpluspost.php', 'gpluspost_send');
	unregister_hook('jot_networks',     'addon/gpluspost/gpluspost.php', 'gpluspost_jot_nets');
	unregister_hook('connector_settings',      'addon/gpluspost/gpluspost.php', 'gpluspost_settings');
	unregister_hook('connector_settings_post', 'addon/gpluspost/gpluspost.php', 'gpluspost_settings_post');
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

function gpluspost_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/gpluspost/gpluspost.css' . '" media="all" />' . "\r\n";

	$enabled = get_pconfig(local_user(),'gpluspost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$def_enabled = get_pconfig(local_user(),'gpluspost','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$noloop_enabled = get_pconfig(local_user(),'gpluspost','no_loop_prevention');
	$noloop_checked = (($noloop_enabled) ? ' checked="checked" ' : '');

	$skip_enabled = get_pconfig(local_user(),'gpluspost','skip_without_link');
	$skip_checked = (($skip_enabled) ? ' checked="checked" ' : '');

	$s .= '<span id="settings_gpluspost_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_gpluspost_expanded\'); openClose(\'settings_gpluspost_inflated\');">';
	$s .= '<h3>' . t('Google+') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_gpluspost_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_gpluspost_expanded\'); openClose(\'settings_gpluspost_inflated\');">';
	$s .= '<h3>' . t('Google+') . '</h3>';
	$s .= '</span>';

	$s .= '<div id="gpluspost-enable-wrapper">';
	$s .= '<label id="gpluspost-enable-label" for="gpluspost-checkbox">' . t('Enable Google+ Post Plugin') . '</label>';
	$s .= '<input id="gpluspost-checkbox" type="checkbox" name="gpluspost" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="gpluspost-bydefault-wrapper">';
	$s .= '<label id="gpluspost-bydefault-label" for="gpluspost-bydefault">' . t('Post to Google+ by default') . '</label>';
	$s .= '<input id="gpluspost-bydefault" type="checkbox" name="gpluspost_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="gpluspost-noloopprevention-wrapper">';
	$s .= '<label id="gpluspost-noloopprevention-label" for="gpluspost-noloopprevention">' . t('Do not prevent posting loops') . '</label>';
	$s .= '<input id="gpluspost-noloopprevention" type="checkbox" name="gpluspost_noloopprevention" value="1" ' . $noloop_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="gpluspost-skipwithoutlink-wrapper">';
	$s .= '<label id="gpluspost-skipwithoutlink-label" for="gpluspost-skipwithoutlink">' . t('Skip messages without links') . '</label>';
	$s .= '<input id="gpluspost-skipwithoutlink" type="checkbox" name="gpluspost_skipwithoutlink" value="1" ' . $skip_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="gpluspost-submit" name="gpluspost-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
	$s .= '<p>Register an account at <a href="https://hootsuite.com">Hootsuite</a>, add your G+ page and add the feed-url there.<br />';
	$s .= 'Feed-url: '.$a->get_baseurl().'/gpluspost/'.urlencode($a->user["nickname"]).'</p></div>';
}

function gpluspost_settings_post(&$a,&$b) {

	if(x($_POST,'gpluspost-submit')) {
		set_pconfig(local_user(),'gpluspost','post',intval($_POST['gpluspost']));
		set_pconfig(local_user(),'gpluspost','post_by_default',intval($_POST['gpluspost_bydefault']));
		set_pconfig(local_user(),'gpluspost','no_loop_prevention',intval($_POST['gpluspost_noloopprevention']));
		set_pconfig(local_user(),'gpluspost','skip_without_link',intval($_POST['gpluspost_skipwithoutlink']));
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

function gpluspost_original_url($url, $depth=1) {

	if ($depth > 10)
		return($url);

	$siteinfo = array();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0');

	$header = curl_exec($ch);
	$curl_info = @curl_getinfo($ch);
	$http_code = $curl_info['http_code'];
	curl_close($ch);

	if ((($curl_info['http_code'] == "301") OR ($curl_info['http_code'] == "302"))
		AND (($curl_info['redirect_url'] != "") OR ($curl_info['location'] != ""))) {
		if ($curl_info['redirect_url'] != "")
			return(gpluspost_original_url($curl_info['redirect_url'], ++$depth));
		else
			return(gpluspost_original_url($curl_info['location'], ++$depth));
	}

	$pos = strpos($header, "\r\n\r\n");

	if ($pos)
		$body = trim(substr($header, $pos));
	else
		$body = $header;

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
					return(gpluspost_original_url(substr($value, 4), ++$depth));
		}
	}

	return($url);
}

function gpluspost_feeditem($pid, $uid) {
	global $a;

	require_once('include/bbcode.php');
	require_once("include/html2plain.php");

	$skipwithoutlink = get_pconfig($uid,'gpluspost','skip_without_link');

	$items = q("SELECT `uri`, `plink`, `author-link`, `author-name`, `created`, `edited`, `id`, `title`, `body` from `item` WHERE id=%d", intval($pid));
	foreach ($items AS $item) {

		$item['body'] = bb_CleanPictureLinks($item['body']);

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

		// At first convert the text to html
		$html = bbcode($body, false, false, 2);

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

		$msglink = gpluspost_original_url($msglink);

		if ($uid == 0)
			$title = $item["author-name"].": ".$title;

		$msglink = htmlspecialchars(html_entity_decode($msglink));

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
		echo "\t\t".'<content type="html" xml:space="preserve" xml:base="'.$item["plink"].'"><![CDATA['.$html."]]></content>\n";
		echo "\t</entry>\n";
	}
}
