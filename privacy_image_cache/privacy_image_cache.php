<?php

/**
 * Name: Privacy Image Cache
 * Version: 0.1
 * Author: Tobias Hößl <https://github.com/CatoTH/>
 */

define("PRIVACY_IMAGE_CACHE_DEFAULT_TIME", 86400); // 1 Day

require_once('include/security.php');

function privacy_image_cache_install() {
    register_hook('bbcode',       'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_bbcode_hook');
    register_hook('display_item', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_display_item_hook');
    register_hook('ping_xmlize',  'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_ping_xmlize_hook');
    register_hook('cron',         'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_cron');
}


function privacy_image_cache_uninstall() {
    unregister_hook('bbcode',       'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_bbcode_hook');
    unregister_hook('display_item', 'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_display_item_hook');
    unregister_hook('ping_xmlize',  'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_ping_xmlize_hook');
    unregister_hook('cron',         'addon/privacy_image_cache/privacy_image_cache.php', 'privacy_image_cache_cron');
}


function privacy_image_cache_module() {}


function privacy_image_cache_init() {
    $urlhash = 'pic:' . sha1($_REQUEST['url']);
    $r = q("SELECT * FROM `photo` WHERE `resource-id` = '%s' LIMIT 1", $urlhash );
    if (count($r)) {
        $img_str = $r[0]['data'];
    }
    else {
        require_once("Photo.php");

        $img_str = fetch_url($_REQUEST['url'],true);
        $img = new Photo($img_str);
        if($img->is_valid()) {
            $img->store(0, 0, $urlhash, $_REQUEST['url'], '', 100);
            $img_str = $img->imageString();
        }
    }


    header("Content-type: image/jpeg");
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + (3600*24)) . " GMT");
    header("Cache-Control: max-age=" . (3600*24));

    echo $img_str;

    killme();
}

/**
 * @param $url string
 * @return boolean
 */
function privacy_image_cache_is_local_image($url) {
    if ($url[0] == '/') return true;
    $baseurl = get_app()->get_baseurl();
    return (substr($url, 0, strlen($baseurl)) == $baseurl);
}

/**
 * @param array $matches
 * @return string
 */
function privacy_image_cache_img_cb($matches) {
    if (privacy_image_cache_is_local_image($matches[2])) return $matches[2];
    return $matches[1] . "/privacy_image_cache/?url=" . escape_tags(addslashes($matches[2])) . $matches[3];
}

/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_bbcode_hook(&$a, &$o) {
    $o = preg_replace_callback("/(<img [^>]*src *= *[\"'])([^\"']+)([\"'][^>]*>)/siU", "privacy_image_cache_img_cb", $o);
}


/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_display_item_hook(&$a, &$o) {
    if (isset($o["output"])) {
        if (isset($o["output"]["thumb"]) && !privacy_image_cache_is_local_image($o["output"]["thumb"]))
            $o["output"]["thumb"] = "/privacy_image_cache/?url=" . escape_tags(addslashes($o["output"]["thumb"]));
        if (isset($o["output"]["author-avatar"]) && !privacy_image_cache_is_local_image($o["output"]["author-avatar"]))
            $o["output"]["author-avatar"] = "/privacy_image_cache/?url=" . escape_tags(addslashes($o["output"]["author-avatar"]));
    }
}


/**
 * @param App $a
 * @param string $o
 */
function privacy_image_cache_ping_xmlize_hook(&$a, &$o) {
    if ($o["photo"] != "" && !privacy_image_cache_is_local_image($o["photo"]))
        $o["photo"] = "/privacy_image_cache/?url=" . escape_tags(addslashes($o["photo"]));
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