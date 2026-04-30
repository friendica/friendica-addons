<?php

/**
 * Name: QuickPhoto
 * Description: Replaces the BBCode for inserted images and provides a placeholder for image descriptions.
 * Version: 1.5
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;
use Friendica\DI;

function quickphoto_install() {
    Hook::register('page_header', 'addon/quickphoto/quickphoto.php', 'quickphoto_header');
    Hook::register('post_post', 'addon/quickphoto/quickphoto.php', 'quickphoto_post_hook');
}

function quickphoto_header(&$header) {
    $desc_label = DI::l10n()->t('Image description');

    $js_label = json_encode($desc_label);

    $header .= "\n" . '<script type="text/javascript">var qp_i18n = { imageDesc: ' . $js_label . ' };</script>';
    $header .= "\n" . '<script type="text/javascript" src="/addon/quickphoto/quickphoto.js?v=5.2"></script>' . "\n";
}

function quickphoto_post_hook(&$item) {
    if (strpos($item['body'], '[img]') === false || strpos($item['body'], '|') === false) {
        return;
    }

    $pattern = '/\[img\](.*?)\|(.*?)\[\/img\]/i';

    $item['body'] = preg_replace_callback($pattern, function($matches) {
        $filename = $matches[1];
        $description = $matches[2];

        $condition = [
            'resource-id' => $filename,
            'uid' => local_user()
        ];

        $photo = DI::pStore()->selectFirst('photo', ['url'], $condition);

        if ($photo) {
            return '[url=' . $photo['url'] . '][img=' . $photo['url'] . ']' . $description . '[/img][/url]';
        }

        return $matches[0];
    }, $item['body']);
}
