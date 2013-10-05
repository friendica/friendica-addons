<?php

/**
 * Name: App.net Post
 * Description: Posts to app.net with the help of ifttt.com
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function appnetpost_install() {
	register_hook('post_local',           'addon/appnetpost/appnetpost.php', 'appnetpost_post_local');
	register_hook('notifier_normal',      'addon/appnetpost/appnetpost.php', 'appnetpost_send');
	register_hook('jot_networks',         'addon/appnetpost/appnetpost.php', 'appnetpost_jot_nets');
	register_hook('connector_settings',      'addon/appnetpost/appnetpost.php', 'appnetpost_settings');
	register_hook('connector_settings_post', 'addon/appnetpost/appnetpost.php', 'appnetpost_settings_post');
}


function appnetpost_uninstall() {
	unregister_hook('post_local',       'addon/appnetpost/appnetpost.php', 'appnetpost_post_local');
	unregister_hook('notifier_normal',  'addon/appnetpost/appnetpost.php', 'appnetpost_send');
	unregister_hook('jot_networks',     'addon/appnetpost/appnetpost.php', 'appnetpost_jot_nets');
	unregister_hook('connector_settings',      'addon/appnetpost/appnetpost.php', 'appnetpost_settings');
	unregister_hook('connector_settings_post', 'addon/appnetpost/appnetpost.php', 'appnetpost_settings_post');
}

function appnetpost_jot_nets(&$a,&$b) {
	if(! local_user())
		return;

	$post = get_pconfig(local_user(),'appnetpost','post');
	if(intval($post) == 1) {
		$defpost = get_pconfig(local_user(),'appnetpost','post_by_default');
		$selected = ((intval($defpost) == 1) ? ' checked="checked" ' : '');
		$b .= '<div class="profile-jot-net"><input type="checkbox" name="appnetpost_enable"' . $selected . ' value="1" /> '
			. t('Post to app.net') . '</div>';
    }
}

function appnetpost_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/appnetpost/appnetpost.css' . '" media="all" />' . "\r\n";

	$enabled = get_pconfig(local_user(),'appnetpost','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');

	$def_enabled = get_pconfig(local_user(),'appnetpost','post_by_default');
	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('App.net Post Settings') . '</h3>';
	$s .= '<div id="appnetpost-enable-wrapper">';
	$s .= '<label id="appnetpost-enable-label" for="appnetpost-checkbox">' . t('Enable App.net Post Plugin') . '</label>';
	$s .= '<input id="appnetpost-checkbox" type="checkbox" name="appnetpost" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="appnetpost-bydefault-wrapper">';
	$s .= '<label id="appnetpost-bydefault-label" for="appnetpost-bydefault">' . t('Post to App.net by default') . '</label>';
	$s .= '<input id="appnetpost-bydefault" type="checkbox" name="appnetpost_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="appnetpost-submit" name="appnetpost-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
	$s .= '<p>Register an account at <a href="https://ifttt.com">IFTTT</a> and create a recipe with the following values:';
	$s .= '<ul><li>If: New feed item (via RSS)</li>';
	$s .= '<li>Then: Post an update (via app.net)</li>';
	$s .= '<li>Feed URL: '.$a->get_baseurl().'/appnetpost/'.urlencode($a->user["nickname"]).'</li>';
	$s .= '<li>Message: {{EntryContent}}</li>';
	$s .= '<li>Original URL: {{EntryUrl}}</li></ul></div>';
}

function appnetpost_settings_post(&$a,&$b) {

	if(x($_POST,'appnetpost-submit')) {
		set_pconfig(local_user(),'appnetpost','post',intval($_POST['appnetpost']));
		set_pconfig(local_user(),'appnetpost','post_by_default',intval($_POST['appnetpost_bydefault']));
	}
}

function appnetpost_post_local(&$a,&$b) {

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

	$post   = intval(get_pconfig(local_user(),'appnetpost','post'));

	$enable = (($post && x($_REQUEST,'appnetpost_enable')) ? intval($_REQUEST['appnetpost_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'appnetpost','post_by_default')))
		$enable = 1;

	if(!$enable)
		return;

	if(strlen($b['postopts']))
		$b['postopts'] .= ',';

	$b['postopts'] .= 'gplus';
}

function appnetpost_send(&$a,&$b) {

	logger('appnetpost_send: invoked for post '.$b['id']." ".$b['app']);

	if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
		return;

	if(! strstr($b['postopts'],'gplus'))
		return;

	if($b['parent'] != $b['id'])
		return;

	$itemlist = get_pconfig($b["uid"],'appnetpost','itemlist');
	$items = explode(",", $itemlist);

	$i = 0;
	$newitems = array($b['id']);
	foreach ($items AS $item)
		if ($i++ < 9)
			$newitems[] = $item;

	$itemlist = implode(",", $newitems);

	logger('appnetpost_send: new itemlist: '.$itemlist." for uid ".$b["uid"]);

	set_pconfig($b["uid"],'appnetpost','itemlist', $itemlist);
}

function appnetpost_module() {}

function appnetpost_init() {
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
		echo "\t".'<link rel="self" href="'.$a->get_baseurl().'/appnetpost/'.$nick.'"/>'."\n";
	} else
		echo "\t".'<link rel="self" href="'.$a->get_baseurl().'/appnetpost"/>'."\n";
	echo "\t<id>".$a->get_baseurl()."/</id>\n";
	echo "\t".'<link rel="alternate" type="text/html" href="'.$a->get_baseurl().'"/>'."\n";
	echo "\t<updated>".date("c")."</updated>\n"; // To-Do
	// <rights>Copyright ... </rights>
	echo "\t".'<generator uri="'.$a->get_baseurl().'">'.$a->config['sitename'].'</generator>'."\n";

	if ($uid != 0) {
		$itemlist = get_pconfig($uid,'appnetpost','itemlist');
		$items = explode(",", $itemlist);

		foreach ($items AS $item)
			appnetpost_feeditem($item, $uid);
	} else {
		$items = q("SELECT `id` FROM `item` FORCE INDEX (`received`) WHERE `item`.`visible` = 1 AND `item`.`deleted` = 0 and `item`.`moderated` = 0 AND `item`.`allow_cid` = ''  AND `item`.`allow_gid` = '' AND `item`.`deny_cid`  = '' AND `item`.`deny_gid`  = '' AND `item`.`private` = 0 AND `item`.`wall` = 1 AND `item`.`id` = `item`.`parent` ORDER BY `received` DESC LIMIT 10");
		foreach ($items AS $item)
			appnetpost_feeditem($item["id"], $uid);
	}
	echo "</feed>\n";
	killme();
}

function appnetpost_original_url($url, $depth=1) {

	if ($depth > 10)
		return($url);

	$siteinfo = array();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_USERAGENT,'Opera/9.64(Windows NT 5.1; U; de) Presto/2.1.1');

	$header = curl_exec($ch);
	$curl_info = @curl_getinfo($ch);
	$http_code = $curl_info['http_code'];
	curl_close($ch);

	if ((($curl_info['http_code'] == "301") OR ($curl_info['http_code'] == "302"))
		AND (($curl_info['redirect_url'] != "") OR ($curl_info['location'] != ""))) {
		if ($curl_info['redirect_url'] != "")
			return(appnetpost_original_url($curl_info['redirect_url'], ++$depth));
		else
			return(appnetpost_original_url($curl_info['location'], ++$depth));
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
					return(appnetpost_original_url(substr($value, 4), ++$depth));
		}
	}

	return($url);
}

if (! function_exists( 'short_link' )) {
function short_link($url) {
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
	} else {
		// setup a cascade of shortening services
		// try to get a short link from these services
		// in the order ur1.ca, trim, id.gd, tinyurl
		$slinky->set_cascade( array( new Slinky_UR1ca(), new Slinky_Trim(), new Slinky_IsGd(), new Slinky_TinyURL() ) );
	}
	return $slinky->short();
} };

function appnetpost_feeditem($pid, $uid) {
	global $a;

	require_once('include/bbcode.php');
	require_once("include/html2plain.php");

	$items = q("SELECT `uri`, `plink`, `author-link`, `author-name`, `created`, `edited`, `id`, `title`, `body` from `item` WHERE id=%d", intval($pid));
	foreach ($items AS $item) {

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

		// Fetching the title and add all lines
		if ($item["title"] != "")
			$title = $item["title"];

		$lines = explode("\n", $msg);
		foreach ($lines AS $line)
			$title .= "\n".$line;

		$max_char = 256;

		$origlink = $msglink;

		if (strlen($msglink) > 20)
			$msglink = short_link($msglink);

		$title = trim(str_replace($origlink, $msglink, $title));

		if (strlen(trim($title." ".$msglink)) > $max_char) {
			$title = substr($title, 0, $max_char - (strlen($msglink)));
			$lastchar = substr($title, -1);
			$title = substr($title, 0, -1);
			$pos = strrpos($title, "\n");
			if ($pos > 0)
				$title = substr($title, 0, $pos);
			else if ($lastchar != "\n")
			$title = substr($title, 0, -3)."...";
		}

		if (!strstr($title, $msglink))
			$title = trim($title." ".$msglink);
		else
			$title = trim($title);

		if ($title == "")
			continue;

		//$origlink = appnetpost_original_url($origlink);

		$html = nl2br($title);

		$origlink = $item["plink"];
		$origlink = htmlspecialchars(html_entity_decode($origlink));

		$title = str_replace("&", "&amp;", $title);
		//$html = str_replace("&", "&amp;", $html);

		echo "\t".'<entry xmlns="http://www.w3.org/2005/Atom">'."\n";
		echo "\t\t".'<title type="html" xml:space="preserve"><![CDATA['.$title."]]></title>\n";
		echo "\t\t".'<link rel="alternate" type="text/html" href="'.$origlink.'" />'."\n";
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
