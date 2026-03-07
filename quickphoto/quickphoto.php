<?php
/**
 * Name: QuickPhoto
 * Description: Replaces the BBCode for inserted images and provides a placeholder for image descriptions.
 * Version: 1.2
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 */

function quickphoto_install() {
    Friendica\Core\Hook::register('page_header', 'addon/quickphoto/quickphoto.php', 'quickphoto_header');
    Friendica\Core\Hook::register('post_post', 'addon/quickphoto/quickphoto.php', 'quickphoto_post_hook');
}

function quickphoto_header(&$header) {
    $desc_label = 'Image description';

    if (function_exists('t')) {
        $desc_label = t('Image description');
    }

    $js_label = addslashes($desc_label);

    $header .= "\n" . '<script type="text/javascript">var qp_i18n = { imageDesc: "' . $js_label . '" };</script>';
    $header .= "\n" . '<script type="text/javascript" src="/addon/quickphoto/quickphoto.js?v=5.1"></script>' . "\n";
}

function quickphoto_post_hook(&$item) {
    // Placeholder
}