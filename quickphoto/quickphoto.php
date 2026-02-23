<?php
/**
 * Name: QuickPhoto
 * Description: Client- and server-side reconstruction for maximum compatibility.
 * Version: 1.1
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;

function quickphoto_install() {
    Hook::register('page_header', 'addon/quickphoto/quickphoto.php', 'quickphoto_header');
    // Emergency hook: If the JS fails during transmission
    Hook::register('post_post', 'addon/quickphoto/quickphoto.php', 'quickphoto_post_hook');
}

function quickphoto_header(&$header) {
    $header .= '<script src="/addon/quickphoto/quickphoto.js?v=5.0"></script>' . "\n";
}

/**
 * Processes the text directly upon receipt on the server
 */
function quickphoto_post_hook(&$item) {
    if (!isset($item['body'])) {
        return;
    }

    // If the JS couldn't do the job, we have the problem here
    // that we don't have LocalStorage. That's why the JS is primarily responsible here.
    // We'll leave this hook as a placeholder for future server validations.
}