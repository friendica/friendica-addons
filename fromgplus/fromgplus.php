<?php
/**
 * Name: From GPlus
 * Description: Imports posts from a Google+ account and repeats them
 * Version: 1.0
 * Author: Michael Vogel <ike@piratenpartei.de>
 *
 */

function fromgplus_install() {
	register_hook('plugin_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	register_hook('plugin_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
}

function fromgplus_uninstall() {
	unregister_hook('plugin_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
}

function fromgplus_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

	$enable_checked = (intval(get_pconfig(local_user(),'fromgplus','enable')) ? ' checked="checked"' : '');
	$account = get_pconfig(local_user(),'fromgplus','account');

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Google+ Import Settings').'</h3>';
	$s .= '<div id="fromgplus-wrapper">';

	$s .= '<label id="fromgplus-enable-label" for="fromgplus-enable">'.t('Enable Google+ Import').'</label>';
	$s .= '<input id="fromgplus-enable" type="checkbox" name="fromgplus-enable" value="1"'.$enable_checked.' />';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="fromgplus-label" for="fromgplus-account">'.t('Google Account ID').' </label>';
	$s .= '<input id="fromgplus-account" type="text" name="fromgplus-account" value="'.$account.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="fromgplus-submit" name="fromgplus-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
	$s .= '</div>';

	return;
}

function fromgplus_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['fromgplus-submit']) {
		set_pconfig(local_user(),'fromgplus','account',trim($_POST['fromgplus-account']));
		$enable = ((x($_POST,'fromgplus-enable')) ? intval($_POST['fromgplus-enable']) : 0);
		set_pconfig(local_user(),'fromgplus','enable', $enable);
		info( t('Google+ Import Settings saved.') . EOL);
	}
}
/*
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
*/
