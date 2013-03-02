<?php

/**
 * Name: G+ Post
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function gpluspost_install() {
}


function gpluspost_uninstall() {
}

function gpluspost_module() {}

function gpluspost_init() {
	global $a, $_SERVER;

	$uid = 1;

	if (isset($a->argv[1])) {
		$uid = (int)$a->argv[1];
	}
	$pid = 317976;

	$contacts = q("SELECT `name` from contact where ID=%d LIMIT 1", intval($uid));

	header("content-type: application/atom+xml");
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
	echo "\t".'<title type="html"><![CDATA['.$a->config['sitename'].']]></title>'."\n";
	echo "\t".'<subtitle type="html"><![CDATA['.$contacts[0]["name"]."]]></subtitle>\n";
	echo "\t".'<link rel="self" href="'.$a->get_baseurl().'/gpluspost"/>'."\n";
	echo "\t<id>".$a->get_baseurl()."/</id>\n";
	echo "\t".'<link rel="alternate" type="text/html" href="'.$a->get_baseurl().'"/>'."\n";
	echo "\t<updated>".date("c")."</updated>\n"; // To-Do
	// <rights>Copyright ... </rights>
	echo "\t".'<generator uri="'.$a->get_baseurl().'">'.$a->config['sitename'].'</generator>'."\n";

	$pidlist = "262568,262567,269154,271508,270121,273721,314735,312616,311570,308771,308247,306100,295372,291096,290390,290389,283242,283060,280465,273725";
	$pids = explode(",", $pidlist);

	$items = q("SELECT `id` FROM `item` FORCE INDEX (`received`) WHERE `item`.`visible` = 1 AND `item`.`deleted` = 0 and `item`.`moderated` = 0 AND `item`.`allow_cid` = ''  AND `item`.`allow_gid` = '' AND `item`.`deny_cid`  = '' AND `item`.`deny_gid`  = '' AND `item`.`private` = 0 AND `item`.`wall` = 1 AND `item`.`id` = `item`.`parent` ORDER BY `received` DESC LIMIT 50");
	//foreach ($items AS $item)
	//	gpluspost_feeditem($item["id"]);
	foreach ($pids AS $pid)
		gpluspost_feeditem($pid);

	echo "</feed>\n";
	killme();
}

function gpluspost_feeditem($pid) {
	global $a;

	require_once('include/bbcode.php');
	require_once("include/html2plain.php");

	$max_char = 140;

	$items = q("SELECT `uri`, `plink`, `author-link`, `author-name`, `created`, `edited`, `id`, `title`, `body` from `item` WHERE id=%d", intval($pid));
	foreach ($items AS $item) {
		// To-Do:
		// extract the link from the body if there is exactly one link

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

		$html = bbcode($item["body"], false, false);
		$msg = trim(html2plain($html, 0, true));

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
			$msglink = $item["plink"];
		else if ($link != "")
			$msglink = $link;
		else if ($multipleimages)
			$msglink = $item["plink"];
		else if ($image != "")
			$msglink = $image;

		//if (($msglink == "")  and strlen($msg) > $max_char)
		if ($msglink == "")
			$msglink = $item["plink"];

		$html = trim(str_replace($msglink, "", $html));

		// Fetching the title - or the first line
		if ($item["title"] != "")
			$title = $item["title"];
		else {
			$lines = explode("\n", $msg);
			$title = $lines[0];
		}
		$title = str_replace("&", "&amp;", $title);
		//$html = str_replace("&", "&amp;", $html);

		echo "\t".'<entry xmlns="http://www.w3.org/2005/Atom">'."\n";
		echo "\t\t".'<title type="html" xml:space="preserve"><![CDATA['.$title."]]></title>\n";
		echo "\t\t".'<link rel="alternate" type="text/html" href="'.$msglink.'" />'."\n";
		// <link rel="enclosure" type="audio/mpeg" length="1337" href="http://example.org/audio/ph34r_my_podcast.mp3"/>
		echo "\t\t<id>".$item["uri"]."</id>\n";
		//echo "\t\t<updated>".date("c", strtotime($item["edited"]))."</updated>\n";
		//echo "\t\t<published>".date("c", strtotime($item["created"]))."</published>\n";
		echo "\t\t<updated>".date("c")."</updated>\n";
		echo "\t\t<published>".date("c")."</published>\n";
		echo "\t\t<author>\n\t\t\t<name><![CDATA[".$item["author-name"]."]]></name>\n";
		echo "\t\t\t<uri>".$item["author-link"]."</uri>\n\t\t</author>\n";
		//echo '<content type="image/png" src="http://media.example.org/the_beach.png"/>';
		echo "\t\t".'<content type="html" xml:space="preserve" xml:base="'.$item["plink"].'"><![CDATA['.$html."]]></content>\n";
		echo "\t</entry>\n";
	}
}
