<?php
/**
 * Name: From GPlus
 * Description: Imports posts from a Google+ account and repeats them
 * Version: 0.1
 * Author: Michael Vogel <ike@piratenpartei.de>
 *
 */

define('FROMGPLUS_DEFAULT_POLL_INTERVAL', 30); // given in minutes

function fromgplus_install() {
	register_hook('plugin_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	register_hook('plugin_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
	register_hook('cron', 'addon/fromgplus/fromgplus.php', 'fromgplus_cron');
}

function fromgplus_uninstall() {
	unregister_hook('plugin_settings', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/fromgplus/fromgplus.php', 'fromgplus_addon_settings_post');
	unregister_hook('cron', 'addon/fromgplus/fromgplus.php', 'fromgplus_cron');
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

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="fromgplus-submit" name="fromgplus-submit" 
class="settings-submit" value="' . t('Submit') . '" /></div>';
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

function fromgplus_post($a, $uid, $source, $body, $location) {

	//$uid = 2;

	$body = trim($body);

        if (substr($body, 0, 3) == "[b]") {
                $pos = strpos($body, "[/b]");
                $title = substr($body, 3, $pos-3);
                $body = trim(substr($body, $pos+4));
        } else
                $title = "";

	$_SESSION['authenticated'] = true;
	$_SESSION['uid'] = $uid;

	$_REQUEST['type'] = 'wall';
	$_REQUEST['api_source'] = true;

	$_REQUEST['profile_uid'] = $uid;
	$_REQUEST['source'] = $source;

	// $_REQUEST['verb']
	// $_REQUEST['parent']
	// $_REQUEST['parent_uri']

	$_REQUEST['title'] = $title;
	$_REQUEST['body'] = $body;
	$_REQUEST['location'] = $location;

        logger('fromgplus: posting for user '.$uid);

	require_once('mod/item.php');
	//print_r($_REQUEST);
	item_post($a);
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

	$preview = "/w".$fullImage->width."-h".$fullImage->height."/";
	$preview2 = "/w".$fullImage->width."-h".$fullImage->height."-p/";
	$fullImage = str_replace(array($preview, $preview2), array("/", "/"), $fullImage->url);

	$preview = "/w".$image->width."-h".$image->height."/";
	$preview2 = "/w".$image->width."-h".$image->height."-p/";
	$image = str_replace(array($preview, $preview2), array("/", "/"), $image->url);

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

	if ($cleaned["full"] == $cleaned["preview"])
		$cleaned["preview"] = "";

	if ($cleaned["full"] == "")
		if (@exif_imagetype($fullImage) != 0)
			$cleaned["full"] = $fullImage;

	if ($cleaned["full"] == "")
		if (@exif_imagetype($image) != 0)
			$cleaned["full"] = $fullImage;

	return($cleaned);
}

function fromgplus_handleattachments($item) {
	$post = "";
	$quote = "";

	foreach ($item->object->attachments as $attachment) {
		switch($attachment->objectType) {
			case "video":
				$post .= "\n\n[bookmark=".$attachment->url."]".fromgplus_html2bbcode($attachment->displayName)."[/bookmark]\n";

				/*$images = cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				if ($images["preview"] != "")
					$post .= "\n[url=".$images["full"]."][img]".$images["preview"]."[/img][/url]\n";
				elseif ($images["full"] != "")
					$post .= "\n[img]".$images["full"]."[/img]\n";*/

				break;

			case "article":
				$post .= "\n\n[bookmark=".$attachment->url."]".fromgplus_html2bbcode($attachment->displayName)."[/bookmark]\n";

				$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				if ($images["preview"] != "")
					$post .= "\n[url=".$images["full"]."][img]".$images["preview"]."[/img][/url]\n";
				elseif ($images["full"] != "")
					$post .= "\n[img]".$images["full"]."[/img]\n";

				//$post .= "[quote]".trim(fromgplus_html2bbcode($attachment->content))."[/quote]";
				$quote = trim(fromgplus_html2bbcode($attachment->content));
				if ($quote != "")
					$quote = "\n[quote]".$quote."[/quote]";
				break;

			case "photo":
				$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				if ($images["preview"] != "")
					$post .= "\n[url=".$images["full"]."][img]".$images["preview"]."[/img][/url]\n";
				elseif ($images["full"] != "")
					$post .= "\n[img]".$images["full"]."[/img]\n";

				if ($attachment->displayName != "")
					$post .= fromgplus_html2bbcode($attachment->displayName)."\n";
				break;

			case "photo-album":
				$post .= "\n\n[bookmark=".$attachment->url."]".fromgplus_html2bbcode($attachment->displayName)."[/bookmark]\n";

				$images = fromgplus_cleanupgoogleproxy($attachment->fullImage, $attachment->image);
				if ($images["preview"] != "")
					$post .= "\n[url=".$images["full"]."][img]".$images["preview"]."[/img][/url]\n";
				elseif ($images["full"] != "")
					$post .= "\n[img]".$images["full"]."[/img]\n";

				break;

			case "album":
				foreach($attachment->thumbnails as $thumb) {
					$preview = "/w".$thumb->image->width."-h".$thumb->image->height."/";
					$preview2 = "/w".$thumb->image->width."-h".$thumb->image->height."-p/";
					$image = str_replace(array($preview, $preview2), array("/", "/"), $thumb->image->url);

					$post .= "\n[url=".$thumb->url."][img]".$image."[/img][/url]\n";
				}
				break;
			//default:
			//	die($attachment->objectType);
		}
	}
	return($post.$quote);
}

function fromgplus_fetch($a, $uid) {
	$maxfetch = 20;

	$account = get_pconfig($uid,'fromgplus','account');
	$key = get_config('fromgplus','key');

	$result = fetch_url("https://www.googleapis.com/plus/v1/people/".$account."/activities/public?alt=json&pp=1&key=".$key."&maxResults=".$maxfetch);
	//$result = file_get_contents("google.txt");
	//file_put_contents("google.txt", $result);

	$activities = json_decode($result);

	$initiallastdate = get_pconfig($uid,'fromgplus','lastdate');

	$lastdate = 0;

	$reversed = array_reverse($activities->items);

	foreach($reversed as $item) {
		if (strtotime($item->published) <= $initiallastdate)
			continue;

		if ($lastdate < strtotime($item->published))
			$lastdate = strtotime($item->published);

		if ($item->access->description == "Public")
			switch($item->object->objectType) {
				case "note":
					$post = fromgplus_html2bbcode($item->object->content);

					if (is_array($item->object->attachments))
						$post .= fromgplus_handleattachments($item);

					// geocode, placeName
					if (isset($item->address))
						$location = $item->address;
					else
						$location = "";

					fromgplus_post($a, $uid, $item->provider->title, $post, $location);

					break;

				case "activity":
					$post = fromgplus_html2bbcode($item->annotation)."\n";

					if (intval(get_config('system','new_share'))) {
						$post .= "[share author='".str_replace("'", "&#039;",$item->object->actor->displayName).
								"' profile='".$item->object->actor->url.
								"' avatar='".$item->object->actor->image->url.
								"' link='".$item->object->url."']";

						$post .= fromgplus_html2bbcode($item->object->content);

						if (is_array($item->object->attachments))
							$post .= "\n".trim(fromgplus_handleattachments($item));

						$post .= "[/share]";
					} else {
						$post .= fromgplus_html2bbcode("&#x2672;");
						$post .= " [url=".$item->object->actor->url."]".$item->object->actor->displayName."[/url] \n";
						$post .= fromgplus_html2bbcode($item->object->content);

						if (is_array($item->object->attachments))
							$post .= "\n".trim(fromgplus_handleattachments($item));
					}

					if (isset($item->address))
						$location = $item->address;
					else
						$location = "";

					fromgplus_post($a, $uid, $item->provider->title, $post, $location);
					break;
			}
	}
	if ($lastdate != 0)
		set_pconfig($uid,'fromgplus','lastdate', $lastdate);
}

/*
// Test
require_once("boot.php");

if(@is_null($a)) {
        $a = new App;
}

if(@is_null($db)) {
        @include(".htconfig.php");
        require_once("include/dba.php");
        $db = new dba($db_host, $db_user, $db_pass, $db_data);
        unset($db_host, $db_user, $db_pass, $db_data);
};

$test = array();
fromgplus_cron($a, $test);
*/
