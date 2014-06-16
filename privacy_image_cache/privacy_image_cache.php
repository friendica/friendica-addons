<?php

/**
 * Name: Privacy Image Cache
 * Version: 0.1
 * Author: Tobias Hößl <https://github.com/CatoTH/>
 */

define("PRIVACY_IMAGE_CACHE_DEFAULT_TIME", 86400); // 1 Day

require_once('include/security.php');
require_once("include/Photo.php");

function privacy_image_cache_install() {
    register_hook('prepare_body', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_prepare_body_hook');
 //   register_hook('bbcode',       'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_bbcode_hook');
    register_hook('display_item', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_display_item_hook');
    register_hook('ping_xmlize',  'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_ping_xmlize_hook');
    register_hook('cron',         'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_cron');
}


function privacy_image_cache_uninstall() {
    unregister_hook('prepare_body', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_prepare_body_hook');
    unregister_hook('bbcode',       'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_bbcode_hook');
    unregister_hook('display_item', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_display_item_hook');
    unregister_hook('ping_xmlize',  'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_ping_xmlize_hook');
    unregister_hook('cron',         'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_cron');
}


function privacy_image_cache_module() {}

function privacy_image_cache_init() {
	global $a, $_SERVER;

	// The code needs to be reworked, it is too complicated
	//
	// it is doing the following:
	// 1. If a folder "privacy_image_cache" exists and is writeable, then use this for caching
	// 2. If a cache path is defined, use this
	// 3. If everything else failed, cache into the database

	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		header('HTTP/1.1 304 Not Modified');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
		header('Etag: '.$_SERVER['HTTP_IF_NONE_MATCH']);
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + (31536000)) . " GMT");
		header("Cache-Control: max-age=31536000");
		if(function_exists('header_remove')) {
			header_remove('Last-Modified');
			header_remove('Expires');
			header_remove('Cache-Control');
		}
		exit;
	}

	if(function_exists('header_remove')) {
		header_remove('Pragma');
		header_remove('pragma');
	}

	$thumb = false;
	$size = 1024;

	// If the cache path isn't there, try to create it
	if (!is_dir($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache"))
		if (is_writable($_SERVER["DOCUMENT_ROOT"]))
			mkdir($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache");

	// Checking if caching into a folder in the webroot is activated and working
	$direct_cache = (is_dir($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache") AND is_writable($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache"));

	// Look for filename in the arguments
	if (isset($a->argv[1]) OR isset($a->argv[2]) OR isset($a->argv[3])) {
		if (isset($a->argv[3]))
			$url = $a->argv[3];
		elseif (isset($a->argv[2]))
			$url = $a->argv[2];
		else
			$url = $a->argv[1];

		//$thumb = (isset($a->argv[3]) and ($a->argv[3] == "thumb"));
		if (isset($a->argv[3]) and ($a->argv[3] == "thumb"))
			$size = 200;

		// thumb, small, medium and large.
		if (substr($url, -6) == ":thumb")
			$size = 150;
		if (substr($url, -6) == ":small")
			$size = 340;
		if (substr($url, -7) == ":medium")
			$size = 600;
		if (substr($url, -6) == ":large")
			$size = 1024;

		$pos = strrpos($url, "=.");
		if ($pos)
			$url = substr($url, 0, $pos+1);

		$url = str_replace(array(".jpg", ".jpeg", ".gif", ".png"), array("","","",""), $url);

		$url = base64_decode(strtr($url, '-_', '+/'), true);

		if ($url)
			$_REQUEST['url'] = $url;
	}

	if (!$direct_cache) {
		$urlhash = 'pic:' . sha1($_REQUEST['url']);
		// Double encoded url - happens with Diaspora
		$urlhash2 = 'pic:' . sha1(urldecode($_REQUEST['url']));

		$cachefile = get_cachefile(hash("md5", $_REQUEST['url']));
		if ($cachefile != '') {
			if (file_exists($cachefile)) {
				$img_str = file_get_contents($cachefile);
				$mime = image_type_to_mime_type(exif_imagetype($cachefile));

				header("Content-type: $mime");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
				header('Etag: "'.md5($img_str).'"');
				header("Expires: " . gmdate("D, d M Y H:i:s", time() + (31536000)) . " GMT");
				header("Cache-Control: max-age=31536000");

				// reduce quality - if it isn't a GIF
				if ($mime != "image/gif") {
					$img = new Photo($img_str, $mime);
					if($img->is_valid()) {
						$img_str = $img->imageString();
					}
				}

				echo $img_str;
				killme();
			}
		}
	} else
		$cachefile = "";

	$valid = true;

	if (!$direct_cache AND ($cachefile == "")) {
		$r = q("SELECT * FROM `photo` WHERE `resource-id` in ('%s', '%s') LIMIT 1", $urlhash, $urlhash2);
		if (count($r)) {
        		$img_str = $r[0]['data'];
			$mime = $r[0]["desc"];
			if ($mime == "") $mime = "image/jpeg";
		}
	} else
		$r = array();

	if (!count($r)) {
		// It shouldn't happen but it does - spaces in URL
		$_REQUEST['url'] = str_replace(" ", "+", $_REQUEST['url']);
		$redirects = 0;
		$img_str = fetch_url($_REQUEST['url'],true, $redirects, 10);

		$tempfile = tempnam(get_config("system","temppath"), "cache");
		file_put_contents($tempfile, $img_str);
		$mime = image_type_to_mime_type(exif_imagetype($tempfile));
		unlink($tempfile);

		// If there is an error then return a blank image
		if ((substr($a->get_curl_code(), 0, 1) == "4") or (!$img_str)) {
			$img_str = file_get_contents("images/blank.png");
			$mime = "image/png";
			$cachefile = ""; // Clear the cachefile so that the dummy isn't stored
			$valid = false;
			$img = new Photo($img_str, "image/png");
			if($img->is_valid()) {
				$img->scaleImage(10);
				$img_str = $img->imageString();
			}
		} else if (($mime != "image/jpeg") AND !$direct_cache AND ($cachefile == "")) {
			$image = @imagecreatefromstring($img_str);

			if($image === FALSE) die();

			q("INSERT INTO `photo`
			( `uid`, `contact-id`, `guid`, `resource-id`, `created`, `edited`, `filename`, `album`, `height`, `width`, `desc`, `data`, `scale`, `profile`, `allow_cid`, `allow_gid`, `deny_cid`, `deny_gid` )
			VALUES ( %d, %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, '%s', '%s', %d, %d, '%s', '%s', '%s', '%s' )",
				0, 0, get_guid(), dbesc($urlhash),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc(basename(dbesc($_REQUEST["url"]))),
				dbesc(''),
				intval(imagesy($image)),
				intval(imagesx($image)),
				$mime,
				dbesc($img_str),
				100,
				intval(0),
				dbesc(''), dbesc(''), dbesc(''), dbesc('')
			);

		} else {
			$img = new Photo($img_str, $mime);
			if($img->is_valid()) {
				if (!$direct_cache AND ($cachefile == ""))
					$img->store(0, 0, $urlhash, $_REQUEST['url'], '', 100);

				//if ($thumb) {
				//	$img->scaleImage(200); // Test
				//	$img_str = $img->imageString();
				//}
			}
			//$mime = "image/jpeg";
		}
	}

	// reduce quality - if it isn't a GIF
	if ($mime != "image/gif") {
		$img = new Photo($img_str, $mime);
		if($img->is_valid()) {
			//$img->scaleImage(1024); // Test
			$img->scaleImage($size);
			$img_str = $img->imageString();
		}
	}

	// If there is a real existing directory then put the cache file there
	// advantage: real file access is really fast
	// Otherwise write in cachefile
	if ($valid AND $direct_cache)
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache/".privacy_image_cache_cachename($_REQUEST['url'], true), $img_str);
	elseif ($cachefile != '')
		file_put_contents($cachefile, $img_str);

	header("Content-type: $mime");

	// Only output the cache headers when the file is valid
	if ($valid) {
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
		header('Etag: "'.md5($img_str).'"');
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + (31536000)) . " GMT");
		header("Cache-Control: max-age=31536000");
	}

	echo $img_str;

	killme();
}

function privacy_image_cache_cachename($url, $writemode = false) {
	global $_SERVER;

	$pos = strrpos($url, ".");
	if ($pos) {
		$extension = strtolower(substr($url, $pos+1));
		$pos = strpos($extension, "?");
		if ($pos)
			$extension = substr($extension, 0, $pos);
	}

	$basepath = $_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache";

	$path = substr(hash("md5", $url), 0, 2);

	if (is_dir($basepath) and $writemode)
		if (!is_dir($basepath."/".$path)) {
			mkdir($basepath."/".$path);
			chmod($basepath."/".$path, 0777);
		}

	$path .= "/".strtr(base64_encode($url), '+/', '-_');

	$extensions = array("jpg", "jpeg", "gif", "png");

	if (in_array($extension, $extensions))
		$path .= ".".$extension;

	return($path);
}

/**
 * @param $url string
 * @return boolean
 */
function privacy_image_cache_is_local_image($url) {
	if ($url[0] == '/') return true;

	if (strtolower(substr($url, 0, 5)) == "data:") return true;

	// Check if the cached path would be longer than 255 characters - apache doesn't like it
	if (is_dir($_SERVER["DOCUMENT_ROOT"]."/privacy_image_cache")) {
		$cachedurl = get_app()->get_baseurl()."/privacy_image_cache/". privacy_image_cache_cachename($url);
		if (strlen($url) > 150)
			return true;
	}

	// links normalised - bug #431
	$baseurl = normalise_link(get_app()->get_baseurl());
	$url = normalise_link($url);
	return (substr($url, 0, strlen($baseurl)) == $baseurl);
}

/**
 * @param array $matches
 * @return string
 */
function privacy_image_cache_img_cb($matches) {

	// if the picture seems to be from another picture cache then take the original source
	$queryvar = privacy_image_cache_parse_query($matches[2]);
	if (($queryvar['url'] != "") AND (substr($queryvar['url'], 0, 4) == "http"))
		$matches[2] = urldecode($queryvar['url']);

	// if fetching facebook pictures don't fetch the thumbnail but the big one
	if (((strpos($matches[2], ".fbcdn.net/") OR strpos($matches[2], "/fbcdn-photos-"))) and (substr($matches[2], -6) == "_s.jpg"))
		$matches[2] = substr($matches[2], 0, -6)."_n.jpg";

	// following line changed per bug #431
	if (privacy_image_cache_is_local_image($matches[2]))
		return $matches[1] . $matches[2] . $matches[3];

	//return $matches[1] . get_app()->get_baseurl() . "/privacy_image_cache/?url=" . addslashes(rawurlencode(htmlspecialchars_decode($matches[2]))) . $matches[3];

	return $matches[1].get_app()->get_baseurl()."/privacy_image_cache/". privacy_image_cache_cachename(htmlspecialchars_decode($matches[2])).$matches[3];
}

/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_prepare_body_hook(&$a, &$o) {
	$o["html"] = preg_replace_callback("/(<img [^>]*src *= *[\"'])([^\"']+)([\"'][^>]*>)/siU", "privacy_image_cache_img_cb", $o["html"]);
}

/**
 * @param App $a
 * @param string $o
 * Function disabled because the plugin moved
 */
function privacy_image_cache_bbcode_hook(&$a, &$o) {
	//$o = preg_replace_callback("/(<img [^>]*src *= *[\"'])([^\"']+)([\"'][^>]*>)/siU", "privacy_image_cache_img_cb", $o);
}


/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_display_item_hook(&$a, &$o) {
    if (isset($o["output"])) {
        if (isset($o["output"]["thumb"]) && !privacy_image_cache_is_local_image($o["output"]["thumb"]))
            $o["output"]["thumb"] = $a->get_baseurl() . "/privacy_image_cache/".privacy_image_cache_cachename($o["output"]["thumb"]);
        if (isset($o["output"]["author-avatar"]) && !privacy_image_cache_is_local_image($o["output"]["author-avatar"]))
            $o["output"]["author-avatar"] = $a->get_baseurl() . "/privacy_image_cache/".privacy_image_cache_cachename($o["output"]["author-avatar"]);
        if (isset($o["output"]["owner-avatar"]) && !privacy_image_cache_is_local_image($o["output"]["owner-avatar"]))
            $o["output"]["owner-avatar"] = $a->get_baseurl() . "/privacy_image_cache/".privacy_image_cache_cachename($o["output"]["owner-avatar"]);
        if (isset($o["output"]["owner_photo"]) && !privacy_image_cache_is_local_image($o["output"]["owner_photo"]))
            $o["output"]["owner_photo"] = $a->get_baseurl() . "/privacy_image_cache/".privacy_image_cache_cachename($o["output"]["owner_photo"]);
    }
}


/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_ping_xmlize_hook(&$a, &$o) {
	if ($o["photo"] != "" && !privacy_image_cache_is_local_image($o["photo"]))
		$o["photo"] = $a->get_baseurl() . "/privacy_image_cache/".privacy_image_cache_cachename($o["photo"]);
        //$o["photo"] = $a->get_baseurl() . "/privacy_image_cache/?url=" . escape_tags(addslashes(rawurlencode($o["photo"])));
}


/**
 * @param App $a
 * @param null|object $b
 */
function privacy_image_cache_cron(&$a = null, &$b = null) {
    $cachetime = get_config('privacy_image_cache','cache_time');
    if (!$cachetime) $cachetime = PRIVACY_IMAGE_CACHE_DEFAULT_TIME;

    $last = get_config('pi_cache','last_delete');
    $time = time();
    if ($time < ($last + 3600)) return;

    logger("Purging old Cache of the Privacy Image Cache", LOGGER_DEBUG);
    q('DELETE FROM `photo` WHERE `uid` = 0 AND `resource-id` LIKE "pic:%%" AND `created` < NOW() - INTERVAL %d SECOND', $cachetime);

    clear_cache($a->get_basepath(), $a->get_basepath()."/privacy_image_cache");

    set_config('pi_cache', 'last_delete', $time);
}

/**
 * @param App $a
 * @param null|object $o
 */
function privacy_image_cache_plugin_admin(&$a, &$o){


    $o = '<input type="hidden" name="form_security_token" value="' . get_form_security_token("picsave") . '">';

    $cachetime = get_config('privacy_image_cache','cache_time');
    if (!$cachetime) $cachetime = PRIVACY_IMAGE_CACHE_DEFAULT_TIME;
    $cachetime_h = Ceil($cachetime / 3600);

    $o .= '<label for="pic_cachetime">' . t('Lifetime of the cache (in hours)') . '</label>
        <input id="pic_cachetime" name="cachetime" type="text" value="' . escape_tags($cachetime_h) . '"><br style="clear: both;">';

    $o .= '<input type="submit" name="save" value="' . t('Save') . '">';

    $o .= '<h4>' . t('Cache Statistics') . '</h4>';

    $num = q('SELECT COUNT(*) num, SUM(LENGTH(data)) size FROM `photo` WHERE `uid`=0 AND `contact-id`=0 AND `resource-id` LIKE "pic:%%"');
    $o .= '<label for="statictics_num">' . t('Number of items') . '</label><input style="color: gray;" id="statistics_num" disabled value="' . escape_tags($num[0]['num']) . '"><br style="clear: both;">';
    $size = Ceil($num[0]['size'] / (1024 * 1024));
    $o .= '<label for="statictics_size">' . t('Size of the cache') . '</label><input style="color: gray;" id="statistics_size" disabled value="' . $size . ' MB"><br style="clear: both;">';

    $o .= '<input type="submit" name="delete_all" value="' . t('Delete the whole cache') . '">';
}


/**
 * @param App $a
 * @param null|object $o
 */
function privacy_image_cache_plugin_admin_post(&$a = null, &$o = null){
    check_form_security_token_redirectOnErr('/admin/plugins/privacy_image_cache', 'picsave');

    if (isset($_REQUEST['save'])) {
        $cachetime_h = IntVal($_REQUEST['cachetime']);
        if ($cachetime_h < 1) $cachetime_h = 1;
        set_config('privacy_image_cache','cache_time', $cachetime_h * 3600);
    }
    if (isset($_REQUEST['delete_all'])) {
        q('DELETE FROM `photo` WHERE `uid` = 0 AND `resource-id` LIKE "pic:%%"');
    }
}

function privacy_image_cache_parse_query($var) {
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
