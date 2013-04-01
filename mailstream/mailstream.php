<?php
/**
 * Name: Mail Stream
 * Description: Mail all items coming into your network feed to an email address
 * Version: 0.2
 * Author: Matthew Exon <http://mat.exon.name>
 */

function mailstream_install() {
    register_hook('plugin_settings', 'addon/mailstream/mailstream.php', 'mailstream_plugin_settings');
    register_hook('plugin_settings_post', 'addon/mailstream/mailstream.php', 'mailstream_plugin_settings_post');
    register_hook('post_remote_end', 'addon/mailstream/mailstream.php', 'mailstream_post_remote_hook');
    register_hook('cron', 'addon/mailstream/mailstream.php', 'mailstream_cron');

    $schema = file_get_contents(dirname(__file__).'/database.sql');
    $arr = explode(';', $schema);
    foreach ($arr as $a) {
        $r = q($a);
    }

    if (get_config('mailstream', 'dbversion') == '0.1') {
        q('ALTER TABLE `mailstream_item` DROP INDEX `uid`');
        q('ALTER TABLE `mailstream_item` DROP INDEX `contact-id`');
        q('ALTER TABLE `mailstream_item` DROP INDEX `plink`');
        q('ALTER TABLE `mailstream_item` CHANGE `plink` `uri` char(255) NOT NULL');
    }
    if (get_config('mailstream', 'dbversion') == '0.2') {
        q('DELETE FROM `pconfig` WHERE `cat` = "mailstream" AND `k` = "delay"');
    }
    if (get_config('mailstream', 'dbversion') == '0.3') {
        q('ALTER TABLE `mailstream_item` CHANGE `created` `created` timestamp NOT NULL DEFAULT now()');
        q('ALTER TABLE `mailstream_item` CHANGE `completed` `completed` timestamp NULL DEFAULT NULL');
    }
    if (get_config('mailstream', 'dbversion') == '0.4') {
        q('ALTER TABLE `mailstream_item` CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin');
    }
    set_config('mailstream', 'dbversion', '0.5');
}

function mailstream_uninstall() {
    unregister_hook('plugin_settings', 'addon/mailstream/mailstream.php', 'mailstream_plugin_settings');
    unregister_hook('plugin_settings_post', 'addon/mailstream/mailstream.php', 'mailstream_plugin_settings_post');
    unregister_hook('post_remote', 'addon/mailstream/mailstream.php', 'mailstream_post_remote_hook');
    unregister_hook('post_remote_end', 'addon/mailstream/mailstream.php', 'mailstream_post_remote_hook');
    unregister_hook('cron', 'addon/mailstream/mailstream.php', 'mailstream_cron');
    unregister_hook('incoming_mail', 'addon/mailstream/mailstream.php', 'mailstream_incoming_mail');
}

function mailstream_module() {}

function mailstream_plugin_admin(&$a,&$o) {
    $frommail = get_config('mailstream', 'frommail');
    $template = get_markup_template('admin.tpl', 'addon/mailstream/');
    $config = array('frommail',
                    t('From Address'),
                    $frommail,
                    t('Email address that stream items will appear to be from.'));
    $o .= replace_macros($template, array(
                             '$frommail' => $config,
                             '$submit' => t('Submit')));
}

function mailstream_plugin_admin_post ($a) {
    if (x($_POST, 'frommail')) {
        set_config('mailstream', 'frommail', $_POST['frommail']);
    }
}

function mailstream_generate_id($a, $uri) {
    // http://www.jwz.org/doc/mid.html
    $host = $a->get_hostname();
    $resource = hash('md5', $uri);
    return "<" . $resource . "@" . $host . ">";
}

function mailstream_post_remote_hook(&$a, &$item) {
    if (!get_pconfig($item['uid'], 'mailstream', 'enabled')) {
        return;
    }
    if (!$item['uid']) {
        return;
    }
    if (!$item['contact-id']) {
        return;
    }
    if (!$item['uri']) {
        return;
    }

    q("INSERT INTO `mailstream_item` (`uid`, `contact-id`, `uri`, `message-id`) " .
      "VALUES (%d, '%s', '%s', '%s')", intval($item['uid']),
      intval($item['contact-id']), dbesc($item['uri']), dbesc(mailstream_generate_id($a, $item['uri'])));
    $r = q('SELECT * FROM `mailstream_item` WHERE `uid` = %d AND `contact-id` = %d AND `uri` = "%s"', intval($item['uid']), intval($item['contact-id']), dbesc($item['uri']));
    if (count($r) != 1) {
        logger('mailstream_post_remote_hook: Unexpected number of items returned from mailstream_item', LOGGER_NORMAL);
        return;
    }
    $ms_item = $r[0];
    logger('mailstream_post_remote_hook: created mailstream_item '
           . $ms_item['id'] . ' for item ' . $item['uri'] . ' '
           . $item['uid'] . ' ' . $item['contact-id'], LOGGER_DATA);
    $user = mailstream_get_user($item['uid']);
    if (!$user) {
        logger('mailstream_post_remote_hook: no user ' . $item['uid'], LOGGER_NORMAL);
        return;
    }
    mailstream_send($a, $ms_item, $item, $user);
}

function mailstream_get_user($uid) {
    $r = q('SELECT * FROM `user` WHERE `uid` = %d', intval($uid));
    if (count($r) != 1) {
        logger('mailstream_post_remote_hook: Unexpected number of users returned', LOGGER_NORMAL);
        return;
    }
    return $r[0];
}

function mailstream_do_images($a, &$item, &$attachments) {
    $baseurl = $a->get_baseurl();
    $id = 1;
    $matches = array();
    preg_match_all("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism", $item["body"], $matches);
    if (count($matches)) {
        foreach ($matches[3] as $url) {
            $attachments[$url] = array();
        }
    }
    preg_match_all("/\[img\](.*?)\[\/img\]/ism", $item["body"], $matches);
    if (count($matches)) {
        foreach ($matches[1] as $url) {
            $attachments[$url] = array();
        }
    }
    foreach ($attachments as $url=>$cid) {
        if (strncmp($url, $baseurl, strlen($baseurl))) {
            unset($attachments[$url]); // Not a local image, don't replace
        }
        else {
            $attachments[$url]['guid'] = substr($url, strlen($baseurl) + strlen('/photo/'));
            $r = q("SELECT `data`, `filename`, `type` FROM `photo` WHERE `resource-id` = '%s'", dbesc($attachments[$url]['guid']));
            $attachments[$url]['data'] = $r[0]['data'];
            $attachments[$url]['filename'] = $r[0]['filename'];
            $attachments[$url]['type'] = $r[0]['type'];
            $item['body'] = str_replace($url, 'cid:' . $attachments[$url]['guid'], $item['body']);
        }
    }
}

function mailstream_subject($item) {
    if ($item['title']) {
        return $item['title'];
    }
    $parent = $item['thr-parent'];
    // Don't look more than 100 levels deep for a subject, in case of loops
    for ($i = 0; ($i < 100) && $parent; $i++) {
        $r = q("SELECT `thr-parent`, `title` FROM `item` WHERE `uri` = '%s'", dbesc($parent));
        if (!count($r)) {
            break;
        }
        if ($r[0]['thr-parent'] === $parent) {
            break;
        }
        if ($r[0]['title']) {
            return t('Re:') . ' ' . $r[0]['title'];
        }
        $parent = $r[0]['thr-parent'];
    }
    $r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d",
           intval($item['contact-id']), intval($item['uid']));
    $contact = $r[0];
    if ($contact['network'] === 'dfrn') {
        return t("Friendica post");
    }
    if ($contact['network'] === 'dspr') {
        return t("Diaspora post");
    }
    if ($contact['network'] === 'face') {
        $subject = (strlen($item['body']) > 150) ? (substr($item['body'], 0, 140) . '...') : $item['body'];
        return preg_replace('/\\s+/', ' ', $subject);
    }
    if ($contact['network'] === 'feed') {
        return t("Feed item");
    }
    if ($contact['network'] === 'mail') {
        return t("Email");
    }
    return t("Friendica Item");
}

function mailstream_send($a, $ms_item, $item, $user) {
    if (!$item['visible']) {
        return;
    }
    require_once(dirname(__file__).'/class.phpmailer.php');
    require_once('include/bbcode.php');
    $attachments = array();
    mailstream_do_images($a, $item, $attachments);
    $frommail = get_config('mailstream', 'frommail');
    if ($frommail == "") {
        $frommail = 'friendica@localhost.local';
    }
    $address = get_pconfig($item['uid'], 'mailstream', 'address');
    if (!$address) {
        $address = $user['email'];
    }
    $mail = new PHPmailer;
    try {
        $mail->XMailer = 'Friendica Mailstream Plugin';
        $mail->SetFrom($frommail, $item['author-name']);
        $mail->AddAddress($address, $user['username']);
        $mail->MessageID = $ms_item['message-id'];
        $mail->Subject = mailstream_subject($item);
        if ($item['thr-parent'] != $item['uri']) {
            $mail->addCustomHeader('In-Reply-To: ' . mailstream_generate_id($a, $item['thr-parent']));
        }
        $mail->addCustomHeader('X-Friendica-Mailstream-URI: ' . $item['uri']);
        $mail->addCustomHeader('X-Friendica-Mailstream-Plink: ' . $item['plink']);
        $encoding = 'base64';
        foreach ($attachments as $url=>$image) {
            $mail->AddStringEmbeddedImage($image['data'], $image['guid'], $image['filename'], $encoding, $image['type']);
        }
        $mail->IsHTML(true);
        $mail->CharSet = 'utf-8';
        $template = get_markup_template('mail.tpl', 'addon/mailstream/');
        $item['body'] = bbcode($item['body']);
        $item['url'] = $a->get_baseurl() . '/display/' . $user['nickname'] . '/' . $item['id'];
        $mail->Body = replace_macros($template, array(
                                         '$upstream' => t('Upstream'),
                                         '$local' => t('Local'),
                                         '$item' => $item));
        if (!$mail->Send()) {
            throw new Exception($mail->ErrorInfo);
        }
        logger('mailstream_send sent message ' . $mail->MessageID . ' ' . $mail->Subject, LOGGER_DEBUG);
    } catch (phpmailerException $e) {
        logger('mailstream_send PHPMailer exception sending message ' . $ms_item['message-id'] . ': ' . $e->errorMessage(), LOGGER_NORMAL);
    } catch (Exception $e) {
        logger('mailstream_send exception sending message ' . $ms_item['message-id'] . ': ' . $e->getMessage(), LOGGER_NORMAL);
    }
    // In case of failure, still set the item to completed.  Otherwise
    // we'll just try to send it over and over again and it'll fail
    // every time.
    q("UPDATE `mailstream_item` SET `completed` = now() WHERE `id` = %d", intval($ms_item['id']));
}

function mailstream_cron($a, $b) {
    $ms_items = q("SELECT * FROM `mailstream_item` WHERE `completed` IS NULL LIMIT 100");
    logger('mailstream_cron processing ' . count($ms_items) . ' items', LOGGER_DEBUG);
    foreach ($ms_items as $ms_item) {
        $items = q("SELECT * FROM `item` WHERE `uid` = %d AND `uri` = '%s' AND `contact-id` = %d",
                   intval($ms_item['uid']), dbesc($ms_item['uri']), intval($ms_item['contact-id']));
        $item = $items[0];
        $users = q("SELECT * FROM `user` WHERE `uid` = %d", intval($ms_item['uid']));
        $user = $users[0];
        if ($user && $item) {
            mailstream_send($a, $ms_item, $item, $user);
        }
        else {
            logger('mailstream_cron: Unable to find item ' . $ms_item['uri'], LOGGER_NORMAL);
            q("UPDATE `mailstream_item` SET `completed` = now() WHERE `id` = %d", intval($ms_item['id']));
        }
    }
    mailstream_tidy();
}

function mailstream_plugin_settings(&$a,&$s) {
    $enabled = get_pconfig(local_user(), 'mailstream', 'enabled');
    $address = get_pconfig(local_user(), 'mailstream', 'address');
    $template = get_markup_template('settings.tpl', 'addon/mailstream/');
    $s .= replace_macros($template, array(
                             '$address' => array(
                                 'mailstream_address',
                                 t('Email Address'),
                                 $address,
                                 t("Leave blank to use your account email address")),
                             '$enabled' => array(
                                 'mailstream_enabled',
                                 t('Enabled'),
                                 $enabled),
                             '$title' => t('Mail Stream Settings'),
                             '$submit' => t('Submit')));
}

function mailstream_plugin_settings_post($a,$post) {
    if ($_POST['mailstream_address'] != "") {
        set_pconfig(local_user(), 'mailstream', 'address', $_POST['mailstream_address']);
    }
    else {
        del_pconfig(local_user(), 'mailstream', 'address');
    }
    if ($_POST['mailstream_enabled']) {
        set_pconfig(local_user(), 'mailstream', 'enabled', $_POST['mailstream_enabled']);
    }
    else {
        del_pconfig(local_user(), 'mailstream', 'enabled');
    }
}

function mailstream_tidy() {
    $r = q("SELECT id FROM mailstream_item WHERE completed IS NOT NULL AND completed < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
    foreach ($r as $rr) {
        q('DELETE FROM mailstream_item WHERE id = %d', intval($rr['id']));
    }
    logger('mailstream_tidy: deleted ' . count($r) . ' old items', LOGGER_DEBUG);
}
