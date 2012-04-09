<?php
require_once("statusnet.lib.php");
include("config.php");

function html2bbcode($html) {

	$bbcode = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

	$bbcode = str_replace(array("\n"), array(""), $bbcode);
	$bbcode = str_replace(array("<b>", "</b>"), array("[b]", "[/b]"), $bbcode);
	$bbcode = str_replace(array("<i>", "</i>"), array("[i]", "[/i]"), $bbcode);
	$bbcode = str_replace(array("<s>", "</s>"), array("[s]", "[/s]"), $bbcode);
	$bbcode = str_replace(array("<br />"), array("\n"), $bbcode);

	$bbcode = trim(strip_tags($bbcode));
	return($bbcode);
}

function friendicapost($post) {
	global $friendica;

	$api = new Statusnet($friendica["user"], $friendica["pw"], "GooglePlus", $friendica["server"]);
	$ret = $api->updateStatus($post);
	$api->endSession();
}

function handleattachments($item) {
	$post = "";

	foreach ($item->object->attachments as $attachment) {
		switch($attachment->objectType) {
			case "video":
				//$post .= "\n\n[url=".$attachment->url."]".
				//		"[size=large][b]".html2bbcode($attachment->displayName)."[/b][/size][/url]\n";
				$post .= "\n\n[bookmark=".$attachment->url."]".html2bbcode($attachment->displayName)."[/bookmark]\n";

				//if (strpos($attachment->embed->url, "youtube.com"))
				//	$post .= "[youtube]".$attachment->url."[/youtube]\n";
				//else
				///	$post .= "[url=".$attachment->url."][img]".$attachment->image->url."[/img][/url]\n";

				///$post .= "[quote]".trim(html2bbcode($attachment->content))."[/quote]";
				break;

			case "article":
				//$post .= "\n\n[url=".$attachment->url."]".
				//		"[size=large][b]".html2bbcode($attachment->displayName)."[/b][/size][/url]\n";
				$post .= "\n\n[bookmark=".$attachment->url."]".html2bbcode($attachment->displayName)."[/bookmark]\n";
				$post .= "[quote]".trim(html2bbcode($attachment->content))."[/quote]";
				break;

			case "photo":
				//$post .= "\n\n[url=".$attachment->fullImage->url."]".
				//		"[img]".$attachment->fullImage->url."[/img][/url]\n";
				$post .= "\n\n[img]".$attachment->fullImage->url."[/img]\n";
				if ($attachment->displayName != "")
					$post .= html2bbcode($attachment->displayName)."\n";
				break;

			case "photo-album":
				$post .= "\n\n[url=".$attachment->url."]".
						"[size=large][b]".html2bbcode($attachment->displayName)."[/b][/size][/url]\n";
				break;

			default:
				print_r($attachment);
				die();
				break;
		}
	}
	return($post);
}

$result = file_get_contents("https://www.googleapis.com/plus/v1/people/".$google["id"]."/activities/public?alt=json&pp=1&key=".$google["key"]."&maxResults=".$google["maxfetch"]);
$activities = json_decode($result);

$state = array("lastid"=>'');
if (file_exists($statefile))
	$state = unserialize(file_get_contents($statefile));

$lastid = "";

foreach($activities->items as $item) {
	if ($item->id == $state["lastid"])
		break;

	if ($lastid == "")
		$lastid = $item->id;

	switch($item->object->objectType) {
		case "note":
			$post = html2bbcode($item->object->content);

			if (is_array($item->object->attachments))
				$post .= handleattachments($item);
			friendicapost($post);
			break;

		case "activity":
			$post = html2bbcode($item->annotation)."\n";
			//$post .= html2bbcode("&#x2672; ");
			$post .= html2bbcode("&#x267B; ");
			$post .= "[url=".$item->object->actor->url."]".$item->object->actor->displayName."[/url]";
			$post .= " \n";
			//$post .= "[quote]";

			$post .= html2bbcode($item->object->content);

			if (is_array($item->object->attachments))
				$post .= "\n".trim(handleattachments($item));

			//$post .= "[/quote]";

			friendicapost($post);
			break;

		default:
			print_r($item);
			die();
			break;
	}
}

if ($lastid != "") {
	$state['lastid'] = $lastid;
	file_put_contents($statefile, serialize($state));
}
?>
