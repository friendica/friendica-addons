<?php
/**
 * Name: From GPlus
 * Description: Imports posts from a Google+ account and repeats them
 * Version: 0.1
 * Author: Michael Vogel <ike@piratenpartei.de>
 *
 */

define('FROMGPLUS_DEFAULT_POLL_INTERVAL', 30); // given in minutes

use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;
use Friendica\Object\Image;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\Network;

require_once 'mod/share.php';
require_once 'mod/parse_url.php';
require_once 'include/text.php';

function fromgplus_install() {
	Addon::registerHook('connector_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	Addon::registerHook('connector_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
	Addon::registerHook('cron', 'addon/fromgplus/fromgplus.php', 'fromgplus_cron');
}

function fromgplus_uninstall() {
	Addon::unregisterHook('connector_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	Addon::unregisterHook('connector_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
	Addon::unregisterHook('cron', 'addon/fromgplus/fromgplus.php', 'fromgplus_cron');

	// Old hooks
	Addon::unregisterHook('addon_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
}

function fromgplus_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

	// If "gpluspost" is installed as well, then the settings are displayed there
	$result = q("SELECT `installed` FROM `addon` WHERE `name` = 'gpluspost' AND `installed`");
	if (count($result) > 0)
		return;

	$enable_checked = (intval(get_pconfig(local_user(),'fromgplus','enable')) ? ' checked="checked"' : '');
	$keywords_checked = (intval(get_pconfig(local_user(), 'fromgplus', 'keywords')) ? ' checked="checked"' : '');
	$account = get_pconfig(local_user(),'fromgplus','account');

	$s .= '<span id="settings_fromgplus_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_fromgplus_expanded\'); openClose(\'settings_fromgplus_inflated\');">';
	$s .= '<img class="connector" src="images/googleplus.png" /><h3 class="connector">'. L10n::t('Google+ Mirror').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_fromgplus_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_fromgplus_expanded\'); openClose(\'settings_fromgplus_inflated\');">';
	$s .= '<img class="connector" src="images/googleplus.png" /><h3 class="connector">'. L10n::t('Google+ Mirror').'</h3>';
	$s .= '</span>';

	$s .= '<div id="fromgplus-wrapper">';

	$s .= '<label id="fromgplus-enable-label" for="fromgplus-enable">'.L10n::t('Enable Google+ Import').'</label>';
	$s .= '<input id="fromgplus-enable" type="checkbox" name="fromgplus-enable" value="1"'.$enable_checked.' />';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="fromgplus-label" for="fromgplus-account">'.L10n::t('Google Account ID').' </label>';
	$s .= '<input id="fromgplus-account" type="text" name="fromgplus-account" value="'.$account.'" />';
	$s .= '</div><div class="clear"></div>';
	$s .= '<label id="fromgplus-keywords-label" for="fromgplus-keywords">'.L10n::t('Add keywords to post').'</label>';
	$s .= '<input id="fromgplus-keywords" type="checkbox" name="fromgplus-keywords" value="1"'.$keywords_checked.' />';
	$s .= '<div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="fromgplus-submit" name="fromgplus-submit"
class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
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
		$keywords = ((x($_POST, 'fromgplus-keywords')) ? intval($_POST['fromgplus-keywords']) : 0);
		set_pconfig(local_user(),'fromgplus', 'keywords', $keywords);

		if (!$enable)
			del_pconfig(local_user(),'fromgplus','lastdate');

		info(L10n::t('Google+ Import Settings saved.') . EOL);
	}
}

function fromgplus_addon_admin(&$a, &$o)
{
	$t = get_markup_template("admin.tpl", "addon/fromgplus/");

	$o = replace_macros($t, [
			'$submit' => L10n::t('Save Settings'),
			'$key' => ['key', L10n::t('Key'), trim(Config::get('fromgplus', 'key')), L10n::t('')],
	]);
}

function fromgplus_addon_admin_post(&$a)
{
	$key = ((x($_POST, 'key')) ? trim($_POST['key']) : '');
	Config::set('fromgplus', 'key', $key);
	info(L10n::t('Settings updated.'). EOL);
}

function fromgplus_cron($a,$b) {
	$last = get_config('fromgplus','last_poll');

        $poll_interval = intval(get_config('fromgplus','poll_interval'));
        if(! $poll_interval)
                $poll_interval = FROMGPLUS_DEFAULT_POLL_INTERVAL;

        if($last) {
                $next = $last + ($poll_interval * 60);
                if($next > time()) {
			logger('fromgplus: poll intervall not reached');
                        return;
		}
	}

        logger('fromgplus: cron_start');

        $r = q("SELECT * FROM `pconfig` WHERE `cat` = 'fromgplus' AND `k` = 'enable' AND `v` = '1' ORDER BY RAND() ");
        if(count($r)) {
                foreach($r as $rr) {
			$account = get_pconfig($rr['uid'],'fromgplus','account');
			if ($account) {
		        logger('fromgplus: fetching for user '.$rr['uid']);
				fromgplus_fetch($a, $rr['uid']);
			}
		}
	}

        logger('fromgplus: cron_end');

	set_config('fromgplus','last_poll', time());
}

function fromgplus_post($a, $uid, $source, $body, $location, $coord, $id) {

	//$uid = 2;

	// Don't know what it is. Maybe some trash from the mobile client
	$trash = html_entity_decode("&#xFEFF;", ENT_QUOTES, 'UTF-8');
	$body = str_replace($trash, "", $body);

	$body = trim($body);

        if (substr($body, 0, 3) == "[b]") {
                $pos = strpos($body, "[/b]");
                $title = substr($body, 3, $pos-3);
                $body = trim(substr($body, $pos+4));
        } else
                $title = "";

	$_SESSION['authenticated'] = true;
	$_SESSION['uid'] = $uid;

	unset($_REQUEST);
	$_REQUEST['type'] = 'wall';
	$_REQUEST['api_source'] = true;

	$_REQUEST['profile_uid'] = $uid;
	$_REQUEST['source'] = $source;
	$_REQUEST['extid'] = NETWORK_GPLUS;

	if (isset($id)) {
		$_REQUEST['message_id'] = item_new_uri($a->get_hostname(), $uid, NETWORK_GPLUS.':'.$id);
	}

	// $_REQUEST['verb']
	// $_REQUEST['parent']
	// $_REQUEST['parent_uri']

	$_REQUEST['title'] = $title;
	$_REQUEST['body'] = $body;
	$_REQUEST['location'] = $location;
	$_REQUEST['coord'] = $coord;

	if (($_REQUEST['title'] == "") && ($_REQUEST['body'] == "")) {
	        logger('fromgplus: empty post for user '.$uid." ".print_r($_REQUEST, true));
		return;
	}

	require_once('mod/item.php');
	//print_r($_REQUEST);
        logger('fromgplus: posting for user '.$uid." ".print_r($_REQUEST, true));
	item_post($a);
        logger('fromgplus: done for user '.$uid);
}

function fromgplus_html2bbcode($html) {

	$bbcode = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

	$bbcode = str_ireplace(array("\n"), array(""), $bbcode);
	$bbcode = str_ireplace(array("<b>", "</b>"), array("[b]", "[/b]"), $bbcode);
	$bbcode = str_ireplace(array("<i>", "</i>"), array("[i]", "[/i]"), $bbcode);
	$bbcode = str_ireplace(array("<s>", "</s>"), array("[s]", "[/s]"), $bbcode);
	$bbcode = str_ireplace(array("<br />"), array("\n"), $bbcode);
	$bbcode = str_ireplace(array("<br/>"), array("\n"), $bbcode);
	$bbcode = str_ireplace(array("<br>"), array("\n"), $bbcode);

	$bbcode = trim(strip_tags($bbcode));
	return($bbcode);
}

function fromgplus_parse_query($var)
 {
	/**
	*  Use this function to parse out the query array element from
	*  the output of parse_url().
	*/
	$var  = parse_url($var, PHP_URL_QUERY);
	$var  = html_entity_decode($var);
	$var  = explode('&', $var);
	$arr  = array();

	foreach($var as $val) {
		$x          = explode('=', $val);
		$arr[$x[0]] = $x[1];
	}
	unset($val, $x, $var);
	return $arr;
}

function fromgplus_cleanupgoogleproxy($fullImage, $image) {
	//$preview = "/w".$fullImage->width."-h".$fullImage->height."/";
	//$preview2 = "/w".$fullImage->width."-h".$fullImage->height."-p/";
	//$fullImage = str_replace(array($preview, $preview2), array("/", "/"), $fullImage->url);
	$fullImage = $fullImage->url;

	//$preview = "/w".$image->width."-h".$image->height."/";
	//$preview2 = "/w".$image->width."-h".$image->height."-p/";
	//$image = str_replace(array($preview, $preview2), array("/", "/"), $image->url);
	$image = $image->url;

       	$cleaned = array();

	$queryvar = fromgplus_parse_query($fullImage);
	if ($queryvar['url'] != "")
        	$cleaned["full"] = urldecode($queryvar['url']);
	else
		$cleaned["full"] = $fullImage;
	if (@exif_imagetype($cleaned["full"]) == 0)
		$cleaned["full"] = "";

	$queryvar = fromgplus_parse_query($image);
	if ($queryvar['url'] != "")
       		$cleaned["preview"] = urldecode($queryvar['url']);
	else
		$cleaned["preview"] = $image;
	if (@exif_imagetype($cleaned["preview"]) == 0)
		$cleaned["preview"] = "";

	if ($cleaned["full"] == "") {
		$cleaned["full"] = $cleaned["preview"];
		$cleaned["preview"] = "";
	}

	if ($cleaned["full"] != "")
		$infoFull = get_photo_info($cleaned["full"]);
	else
		$infoFull = array("0" => 0, "1" => 0);

	if ($cleaned["preview"] != "")
		$infoPreview = get_photo_info($cleaned["preview"]);
	else
		$infoFull = array("0" => 0, "1" => 0);

	if (($infoPreview[0] >= $infoFull[0]) && ($infoPreview[1] >= $infoFull[1])) {
		$temp = $cleaned["full"];
		$cleaned["full"] = $cleaned["preview"];
		$cleaned["preview"] = $temp;
	}

	if (($cleaned["full"] == $cleaned["preview"]) || (($infoPreview[0] == $infoFull[0]) && ($infoPreview[1] == $infoFull[1])))
		$cleaned["preview"] = "";

	if ($cleaned["full"] == "")
		if (@exif_imagetype($fullImage) != 0)
			$cleaned["full"] = $fullImage;

	if ($cleaned["full"] == "")
		if (@exif_imagetype($image) != 0)
			$cleaned["full"] = $image;

	// Could be changed in the future to a link to the album
	$cleaned["page"] = $cleaned["full"];

	return($cleaned);
}

function fromgplus_cleantext($text) {

	// Don't know what it is. But it is added to the text.
	$trash = html_entity_decode("&#xFEFF;", ENT_QUOTES, 'UTF-8');

	$text = strip_tags($text);
	$text = html_entity_decode($text, ENT_QUOTES);
	$text = trim($text);
	$text = str_replace(array("\n", "\r", " ", $trash), array("", "", "", ""), $text);
	return($text);
}

function fromgplus_handleattachments($a, $uid, $item, $displaytext, $shared) {
	require_once 'include/items.php';

	$post = "";
	$quote = "";
	$pagedata = array();
	$pagedata["type"] = "";

	foreach ($item->object->attachments as $attachment) {
		switch($attachment->objectType) {
			case "video":
				$pagedata["type"] = "video";
				$pagedata["url"] = Network::finalUrl($attachment->url);
				$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);
				break;

			case "article":
				$pagedata["type"] = "link";
				$pagedata["url"] = Network::finalUrl($attachment->url);
				$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);

				$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				if ($images["full"] != "")
					$pagedata["images"][0]["src"] = $images["full"];

				$quote = trim(fromgplus_html2bbcode($attachment->content));

				if ($quote != "")
					$pagedata["text"] = $quote;

				// Add Keywords to page link
				$data = parseurl_getsiteinfo_cached($pagedata["url"], true);
				if (isset($data["keywords"]) && get_pconfig($uid, 'fromgplus', 'keywords')) {
					$pagedata["keywords"] = $data["keywords"];
				}
				break;

			case "photo":
				// Don't store shared pictures in your wall photos (to prevent a possible violating of licenses)
				if ($shared)
					$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				else {
					if ($attachment->fullImage->url != "")
						$images = store_photo($a, $uid, "", $attachment->fullImage->url);
					elseif ($attachment->image->url != "")
						$images = store_photo($a, $uid, "", $attachment->image->url);
				}

				if ($images["preview"] != "") {
					$post .= "\n[url=".$images["page"]."][img]".$images["preview"]."[/img][/url]\n";
					$pagedata["images"][0]["src"] = $images["preview"];
					$pagedata["url"] = $images["page"];
				} elseif ($images["full"] != "") {
					$post .= "\n[img]".$images["full"]."[/img]\n";
					$pagedata["images"][0]["src"] = $images["full"];

					if ($images["preview"] != "")
						$pagedata["images"][1]["src"] = $images["preview"];
				}

				if (($attachment->displayName != "") && (fromgplus_cleantext($attachment->displayName) != fromgplus_cleantext($displaytext))) {
					$post .= fromgplus_html2bbcode($attachment->displayName)."\n";
					$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);
				}
				break;

			case "photo-album":
				$pagedata["url"] = Network::finalUrl($attachment->url);
				$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);
				$post .= "\n\n[bookmark=".$pagedata["url"]."]".$pagedata["title"]."[/bookmark]\n";

				$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);

				if ($images["preview"] != "") {
					$post .= "\n[url=".$images["full"]."][img]".$images["preview"]."[/img][/url]\n";
					$pagedata["images"][0]["src"] = $images["preview"];
					$pagedata["url"] = $images["full"];
				} elseif ($images["full"] != "") {
					$post .= "\n[img]".$images["full"]."[/img]\n";
					$pagedata["images"][0]["src"] = $images["full"];

					if ($images["preview"] != "")
						$pagedata["images"][1]["src"] = $images["preview"];
				}
				break;

			case "album":
				$pagedata["type"] = "link";
				$pagedata["url"] = Network::finalUrl($attachment->url);
				$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);

				$thumb = $attachment->thumbnails[0];
				$pagedata["images"][0]["src"] = $thumb->image->url;

				$quote = trim(fromgplus_html2bbcode($thumb->description));
				if ($quote != "")
					$pagedata["text"] = $quote;

				break;

			case "audio":
				$pagedata["url"] = Network::finalUrl($attachment->url);
				$pagedata["title"] = fromgplus_html2bbcode($attachment->displayName);
				$post .= "\n\n[bookmark=".$pagedata["url"]."]".$pagedata["title"]."[/bookmark]\n";
				break;

			//default:
			//	die($attachment->objectType);
		}
	}

	if ($pagedata["type"] != "")
		return(add_page_info_data($pagedata));

	return($post.$quote);
}

function fromgplus_fetch($a, $uid) {
	$maxfetch = 20;

	// Special blank to identify postings from the googleplus connector
	$blank = html_entity_decode("&#x00A0;", ENT_QUOTES, 'UTF-8');

	$account = get_pconfig($uid,'fromgplus','account');
	$key = get_config('fromgplus','key');

	$result = Network::fetchUrl("https://www.googleapis.com/plus/v1/people/".$account."/activities/public?alt=json&pp=1&key=".$key."&maxResults=".$maxfetch);
	//$result = file_get_contents("google.txt");
	//file_put_contents("google.txt", $result);

	$activities = json_decode($result);

	$initiallastdate = get_pconfig($uid,'fromgplus','lastdate');

	$first_time = ($initiallastdate == "");

	$lastdate = 0;

	if (!is_array($activities->items))
		return;

	$reversed = array_reverse($activities->items);

	foreach($reversed as $item) {

		if (strtotime($item->published) <= $initiallastdate)
			continue;

		// Don't publish items that are too young
		if (strtotime($item->published) > (time() - 3*60)) {
			logger('fromgplus_fetch: item too new '.$item->published);
			continue;
		}

		if ($lastdate < strtotime($item->published))
			$lastdate = strtotime($item->published);

		set_pconfig($uid,'fromgplus','lastdate', $lastdate);

		if ($first_time)
			continue;

		if ($item->access->description == "Public") {

			// Loop prevention through the special blank from the googleplus connector
			//if (strstr($item->object->content, $blank))
			if (strrpos($item->object->content, $blank) >= strlen($item->object->content) - 5)
				continue;

			switch($item->object->objectType) {
				case "note":
					$post = fromgplus_html2bbcode($item->object->content);

					if (is_array($item->object->attachments))
						$post .= fromgplus_handleattachments($a, $uid, $item, $item->object->content, false);

					$coord = "";
					$location = "";
					if (isset($item->location)) {
						if (isset($item->location->address->formatted))
							$location = $item->location->address->formatted;

						if (isset($item->location->displayName))
							$location = $item->location->displayName;

						if (isset($item->location->position->latitude) &&
							isset($item->location->position->longitude))
							$coord = $item->location->position->latitude." ".$item->location->position->longitude;

					} elseif (isset($item->address))
						$location = $item->address;

					fromgplus_post($a, $uid, $item->provider->title, $post, $location, $coord, $item->id);

					break;

				case "activity":
					$post = fromgplus_html2bbcode($item->annotation)."\n";

					if (!intval(get_config('system','old_share'))) {

						if (function_exists("share_header"))
							$post .= share_header($item->object->actor->displayName, $item->object->actor->url,
										$item->object->actor->image->url, "",
										DateTimeFormat::utc($item->object->published),$item->object->url);
						else
							$post .= "[share author='".str_replace("'", "&#039;",$item->object->actor->displayName).
									"' profile='".$item->object->actor->url.
									"' avatar='".$item->object->actor->image->url.
									"' posted='".DateTimeFormat::utc($item->object->published).
									"' link='".$item->object->url."']";

						$post .= fromgplus_html2bbcode($item->object->content);

						if (is_array($item->object->attachments))
							$post .= "\n".trim(fromgplus_handleattachments($a, $uid, $item, $item->object->content, true));

						$post .= "[/share]";
					} else {
						$post .= fromgplus_html2bbcode("&#x2672;");
						$post .= " [url=".$item->object->actor->url."]".$item->object->actor->displayName."[/url] \n";
						$post .= fromgplus_html2bbcode($item->object->content);

						if (is_array($item->object->attachments))
							$post .= "\n".trim(fromgplus_handleattachments($a, $uid, $item, $item->object->content, true));
					}

					$coord = "";
					$location = "";
					if (isset($item->location)) {
						if (isset($item->location->address->formatted))
							$location = $item->location->address->formatted;

						if (isset($item->location->displayName))
							$location = $item->location->displayName;

						if (isset($item->location->position->latitude) &&
							isset($item->location->position->longitude))
							$coord = $item->location->position->latitude." ".$item->location->position->longitude;

					} elseif (isset($item->address))
						$location = $item->address;

					fromgplus_post($a, $uid, $item->provider->title, $post, $location, $coord, $item->id);
					break;
			}
		}
	}
	if ($lastdate != 0)
		set_pconfig($uid,'fromgplus','lastdate', $lastdate);
}
