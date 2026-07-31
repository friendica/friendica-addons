<?php

/**
 * Name: QuickPhoto
 * Description: Easily edit an image description by replacing the BBCode
 * Version: 1.7
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;
use Friendica\DI;

function quickphoto_install()
{
    Hook::register('page_header', 'addon/quickphoto/quickphoto.php', 'quickphoto_header');
    Hook::register('post_post', 'addon/quickphoto/quickphoto.php', 'quickphoto_post_hook');
}

function quickphoto_header(&$header)
{
    $desc_label = DI::l10n()->t('Image description');

    $js_label = json_encode($desc_label);

    $addon_path = '/addon/quickphoto/';
    $local_path = 'addon/quickphoto/';

    if (file_exists($local_path . 'styles.css')) {
        $v_css = filemtime($local_path . 'styles.css');
        $header .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $addon_path . 'styles.css?v=' . $v_css . '" media="all" />';
    }

    $header .= "\n" . '<script type="text/javascript">var qp_i18n = { imageDesc: ' . $js_label . ' };</script>';

    if (file_exists($local_path . 'quickphoto.js')) {
        $v_js = filemtime($local_path . 'quickphoto.js');
        $header .= "\n" . '<script type="text/javascript" src="' . $addon_path . 'quickphoto.js?v=' . $v_js . '"></script>' . "\n";
    }
}

function quickphoto_post_hook(&$item)
{
    if (strpos($item['body'], '[img]') === false || strpos($item['body'], '|') === false) {
        return;
    }

    $pattern = '/\\[img\\](.*?)\\|(.*?)\\[\\/img\\]/i';

    $item['body'] = preg_replace_callback($pattern, function($matches) {
        $filename = $matches[1];
        $description = $matches[2];

        $condition = [
            'resource-id' => $filename,
            'uid' => DI::userSession()->getLocalUserId(),
            'imgscale'    => 0
        ];

        $photo = DI::dba()->selectFirst('photo', ['url'], $condition);

        if ($photo) {
            return '[url=' . $photo['url'] . '][img=' . $photo['src'] . ']' . $description . '[/img][/url]';
        }

        return '[img]' . $filename . '[/img]';
    }, $item['body']);
}
