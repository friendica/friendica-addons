<?php
/**
 * Name: Mail Stream
 * Description: Mail all items coming into your network feed to an email address
 * Version: 1.1
 * Author: Matthew Exon <http://mat.exon.name>
 */

use Friendica\Content\Text\BBCode;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\Util\Network;
use Friendica\Model\Item;

function mailstream_install() {
	Hook::register('addon_settings', 'addon/mailstream/mailstream.php', 'mailstream_addon_settings');
	Hook::register('addon_settings_post', 'addon/mailstream/mailstream.php', 'mailstream_addon_settings_post');
	Hook::register('post_local_end', 'addon/mailstream/mailstream.php', 'mailstream_post_hook');
	Hook::register('post_remote_end', 'addon/mailstream/mailstream.php', 'mailstream_post_hook');
	Hook::register('cron', 'addon/mailstream/mailstream.php', 'mailstream_cron');

	if (Config::get('mailstream', 'dbversion') == '0.1') {
		q('ALTER TABLE `mailstream_item` DROP INDEX `uid`');
		q('ALTER TABLE `mailstream_item` DROP INDEX `contact-id`');
		q('ALTER TABLE `mailstream_item` DROP INDEX `plink`');
		q('ALTER TABLE `mailstream_item` CHANGE `plink` `uri` char(255) NOT NULL');
		Config::set('mailstream', 'dbversion', '0.2');
	}
	if (Config::get('mailstream', 'dbversion') == '0.2') {
		q('DELETE FROM `pconfig` WHERE `cat` = "mailstream" AND `k` = "delay"');
		Config::set('mailstream', 'dbversion', '0.3');
	}
	if (Config::get('mailstream', 'dbversion') == '0.3') {
		q('ALTER TABLE `mailstream_item` CHANGE `created` `created` timestamp NOT NULL DEFAULT now()');
		q('ALTER TABLE `mailstream_item` CHANGE `completed` `completed` timestamp NULL DEFAULT NULL');
		Config::set('mailstream', 'dbversion', '0.4');
	}
	if (Config::get('mailstream', 'dbversion') == '0.4') {
		q('ALTER TABLE `mailstream_item` CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin');
		Config::set('mailstream', 'dbversion', '0.5');
	}
	if (Config::get('mailstream', 'dbversion') == '0.5') {
		Config::set('mailstream', 'dbversion', '1.0');
	}

	if (Config::get('retriever', 'dbversion') != '1.0') {
		$schema = file_get_contents(dirname(__file__).'/database.sql');
		$arr = explode(';', $schema);
		foreach ($arr as $a) {
			$r = q($a);
		}
		Config::set('mailstream', 'dbversion', '1.0');
	}
}

function mailstream_uninstall() {
	Hook::unregister('addon_settings', 'addon/mailstream/mailstream.php', 'mailstream_addon_settings');
	Hook::unregister('addon_settings_post', 'addon/mailstream/mailstream.php', 'mailstream_addon_settings_post');
	Hook::unregister('post_local', 'addon/mailstream/mailstream.php', 'mailstream_post_local_hook');
	Hook::unregister('post_remote', 'addon/mailstream/mailstream.php', 'mailstream_post_remote_hook');
	Hook::unregister('post_local_end', 'addon/mailstream/mailstream.php', 'mailstream_post_local_hook');
	Hook::unregister('post_remote_end', 'addon/mailstream/mailstream.php', 'mailstream_post_remote_hook');
	Hook::unregister('post_local_end', 'addon/mailstream/mailstream.php', 'mailstream_post_hook');
	Hook::unregister('post_remote_end', 'addon/mailstream/mailstream.php', 'mailstream_post_hook');
	Hook::unregister('cron', 'addon/mailstream/mailstream.php', 'mailstream_cron');
	Hook::unregister('incoming_mail', 'addon/mailstream/mailstream.php', 'mailstream_incoming_mail');
}

function mailstream_module() {}

function mailstream_addon_admin(&$a,&$o) {
	$frommail = Config::get('mailstream', 'frommail');
	$template = Renderer::getMarkupTemplate('admin.tpl', 'addon/mailstream/');
	$config = ['frommail',
			L10n::t('From Address'),
			$frommail,
			L10n::t('Email address that stream items will appear to be from.')];
	$o .= Renderer::replaceMacros($template, [
				 '$frommail' => $config,
				 '$submit' => L10n::t('Save Settings')]);
}

function mailstream_addon_admin_post ($a) {
	if (!empty($_POST['frommail'])) {
		Config::set('mailstream', 'frommail', $_POST['frommail']);
	}
}

function mailstream_generate_id($a, $uri) {
	// http://www.jwz.org/doc/mid.html
	$host = $a->getHostName();
	$resource = hash('md5', $uri);
	$message_id = "<" . $resource . "@" . $host . ">";
	Logger::debug('mailstream: Generated message ID ' . $message_id . ' for URI ' . $uri);
	return $message_id;
}

function mailstream_post_hook(&$a, &$item) {
	if (!PConfig::get($item['uid'], 'mailstream', 'enabled')) {
		Logger::debug('mailstream: not enabled for item ' . $item['id']);
		return;
	}
	if (!$item['uid']) {
		Logger::debug('mailstream: no uid for item ' . $item['id']);
		return;
	}
	if (!$item['contact-id']) {
		Logger::debug('mailstream: no contact-id for item ' . $item['id']);
		return;
	}
	if (!$item['uri']) {
		Logger::debug('mailstream: no uri for item ' . $item['id']);
		return;
	}
	if (!$item['plink']) {
		Logger::debug('mailstream: no plink for item ' . $item['id']);
		return;
	}
	if (PConfig::get($item['uid'], 'mailstream', 'nolikes')) {
		if ($item['verb'] == ACTIVITY_LIKE) {
			Logger::debug('mailstream: like item ' . $item['id']);
			return;
		}
	}

	$message_id = mailstream_generate_id($a, $item['uri']);
	q("INSERT INTO `mailstream_item` (`uid`, `contact-id`, `uri`, `message-id`) " .
		"VALUES (%d, '%s', '%s', '%s')", intval($item['uid']),
		intval($item['contact-id']), DBA::escape($item['uri']), DBA::escape($message_id));
	$r = q('SELECT * FROM `mailstream_item` WHERE `uid` = %d AND `contact-id` = %d AND `uri` = "%s"', intval($item['uid']), intval($item['contact-id']), DBA::escape($item['uri']));
	if (count($r) != 1) {
		Logger::info('mailstream_post_remote_hook: Unexpected number of items returned from mailstream_item');
		return;
	}
	$ms_item = $r[0];
	Logger::debug('mailstream_post_remote_hook: created mailstream_item ' . $ms_item['id'] . ' for item ' . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);
	$user = mailstream_get_user($item['uid']);
	if (!$user) {
		Logger::info('mailstream_post_remote_hook: no user ' . $item['uid']);
		return;
	}
	mailstream_send($a, $ms_item['message-id'], $item, $user);
}

function mailstream_get_user($uid) {
	$r = q('SELECT * FROM `user` WHERE `uid` = %d', intval($uid));
	if (count($r) != 1) {
		Logger::info('mailstream_post_remote_hook: Unexpected number of users returned');
		return;
	}
	return $r[0];
}

function mailstream_do_images($a, &$item, &$attachments) {
	if (!PConfig::get($item['uid'], 'mailstream', 'attachimg')) {
		return;
	}
	$attachments = [];
	preg_match_all("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism", $item["body"], $matches1);
	preg_match_all("/\[img\](.*?)\[\/img\]/ism", $item["body"], $matches2);
	preg_match_all("/\[img\=([^\]]*)\]([^[]*)\[\/img\]/ism", $item["body"], $matches3);
	foreach (array_merge($matches1[3], $matches2[1], $matches3[1]) as $url) {
		$components = parse_url($url);
		if (!$components) {
			continue;
		}
		$cookiejar = tempnam(get_temppath(), 'cookiejar-mailstream-');
		$curlResult = Network::fetchUrlFull($url, true, 0, '', $cookiejar);
		$attachments[$url] = [
			'data' => $curlResult->getBody(),
			'guid' => hash("crc32", $url),
			'filename' => basename($components['path']),
			'type' => $curlResult->getContentType()
		];

		if (strlen($attachments[$url]['data'])) {
			$item['body'] = str_replace($url, 'cid:' . $attachments[$url]['guid'], $item['body']);
			continue;
		}
	}
	return $attachments;
}

function mailstream_sender($item) {
	$r = q('SELECT * FROM `contact` WHERE `id` = %d', $item['contact-id']);
	if (DBA::isResult($r)) {
		$contact = $r[0];
		if ($contact['name'] != $item['author-name']) {
			return $contact['name'] . ' - ' . $item['author-name'];
		}
	}
	return $item['author-name'];
}

function mailstream_decode_subject($subject) {
	$html = BBCode::convert($subject);
	if (!$html) {
		return $subject;
	}
	$notags = strip_tags($html);
	if (!$notags) {
		return $subject;
	}
	$noentity = html_entity_decode($notags);
	if (!$noentity) {
		return $notags;
	}
	$nocodes = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $noentity);
	if (!$nocodes) {
		return $noentity;
	}
	$trimmed = trim($nocodes);
	if (!$trimmed) {
		return $nocodes;
	}
	return $trimmed;
}

function mailstream_subject($item) {
	if ($item['title']) {
		return mailstream_decode_subject($item['title']);
	}
	$parent = $item['thr-parent'];
	// Don't look more than 100 levels deep for a subject, in case of loops
	for ($i = 0; ($i < 100) && $parent; $i++) {
		$parent_item = Item::selectFirst(['thr-parent', 'title'], ['uri' => $parent]);
		if (!DBA::isResult($parent_item)) {
			break;
		}
		if ($parent_item['thr-parent'] === $parent) {
			break;
		}
		if ($parent_item['title']) {
			return L10n::t('Re:') . ' ' . mailstream_decode_subject($parent_item['title']);
		}
		$parent = $parent_item['thr-parent'];
	}
	$r = q("SELECT * FROM `contact` WHERE `id` = %d AND `uid` = %d",
		intval($item['contact-id']), intval($item['uid']));
	$contact = $r[0];
	if ($contact['network'] === 'dfrn') {
		return L10n::t("Friendica post");
	}
	if ($contact['network'] === 'dspr') {
		return L10n::t("Diaspora post");
	}
	if ($contact['network'] === 'face') {
		$text = mailstream_decode_subject($item['body']);
		// For some reason these do show up in Facebook
		$text = preg_replace('/\xA0$/', '', $text);
		$subject = (strlen($text) > 150) ? (substr($text, 0, 140) . '...') : $text;
		return preg_replace('/\\s+/', ' ', $subject);
	}
	if ($contact['network'] === 'feed') {
		return L10n::t("Feed item");
	}
	if ($contact['network'] === 'mail') {
		return L10n::t("Email");
	}
	return L10n::t("Friendica Item");
}

function mailstream_send(\Friendica\App $a, $message_id, $item, $user) {
	if (!$item['visible']) {
		return;
	}
	if (!$message_id) {
		return;
	}
	require_once(dirname(__file__).'/phpmailer/class.phpmailer.php');

	$attachments = [];
	mailstream_do_images($a, $item, $attachments);
	$frommail = Config::get('mailstream', 'frommail');
	if ($frommail == "") {
		$frommail = 'friendica@localhost.local';
	}
	$address = PConfig::get($item['uid'], 'mailstream', 'address');
	if (!$address) {
		$address = $user['email'];
	}
	$mail = new PHPmailer;
	try {
		$mail->XMailer = 'Friendica Mailstream Addon';
		$mail->SetFrom($frommail, mailstream_sender($item));
		$mail->AddAddress($address, $user['username']);
		$mail->MessageID = $message_id;
		$mail->Subject = mailstream_subject($item);
		if ($item['thr-parent'] != $item['uri']) {
			$mail->addCustomHeader('In-Reply-To: ' . mailstream_generate_id($a, $item['thr-parent']));
		}
		$mail->addCustomHeader('X-Friendica-Mailstream-URI: ' . $item['uri']);
		$mail->addCustomHeader('X-Friendica-Mailstream-Plink: ' . $item['plink']);
		$encoding = 'base64';
		foreach ($attachments as $url => $image) {
			$mail->AddStringEmbeddedImage($image['data'], $image['guid'], $image['filename'], $encoding, $image['type']);
		}
		$mail->IsHTML(true);
		$mail->CharSet = 'utf-8';
		$template = Renderer::getMarkupTemplate('mail.tpl', 'addon/mailstream/');
		$mail->AltBody = BBCode::toPlaintext($item['body']);
		$item['body'] = BBCode::convert($item['body']);
		$item['url'] = $a->getBaseURL() . '/display/' . $item['guid'];
		$mail->Body = Renderer::replaceMacros($template, [
						 '$upstream' => L10n::t('Upstream'),
						 '$local' => L10n::t('Local'),
						 '$item' => $item]);
		mailstream_html_wrap($mail->Body);
		if (!$mail->Send()) {
			throw new Exception($mail->ErrorInfo);
		}
		Logger::debug('mailstream_send sent message ' . $mail->MessageID . ' ' . $mail->Subject);
	} catch (phpmailerException $e) {
		Logger::debug('mailstream_send PHPMailer exception sending message ' . $message_id . ': ' . $e->errorMessage());
	} catch (Exception $e) {
		Logger::debug('mailstream_send exception sending message ' . $message_id . ': ' . $e->getMessage());
	}
	// In case of failure, still set the item to completed.  Otherwise
	// we'll just try to send it over and over again and it'll fail
	// every time.
	q('UPDATE `mailstream_item` SET `completed` = now() WHERE `message-id` = "%s"', DBA::escape($message_id));
}

/**
 * Email tends to break if you send excessively long lines.  To make
 * bbcode's output suitable for transmission, we try to break things
 * up so that lines are about 200 characters.
 */
function mailstream_html_wrap(&$text)
{
	$lines = str_split($text, 200);
	for ($i = 0; $i < count($lines); $i++) {
		$lines[$i] = preg_replace('/ /', "\n", $lines[$i], 1);
	}
	$text = implode($lines);
}

function mailstream_cron($a, $b) {
	// Only process items older than an hour in cron.  This is because
	// we want to give mailstream_post_remote_hook a fair chance to
	// send the email itself before cron jumps in.  Only if
	// mailstream_post_remote_hook fails for some reason will this get
	// used, and in that case it's worth holding off a bit anyway.
	$ms_item_ids = q("SELECT `mailstream_item`.`message-id`, `mailstream_item`.`uri`, `item`.`id` FROM `mailstream_item` JOIN `item` ON (`mailstream_item`.`uid` = `item`.`uid` AND `mailstream_item`.`uri` = `item`.`uri` AND `mailstream_item`.`contact-id` = `item`.`contact-id`) WHERE `mailstream_item`.`completed` IS NULL AND `mailstream_item`.`created` < DATE_SUB(NOW(), INTERVAL 1 HOUR) AND `item`.`visible` = 1 ORDER BY `mailstream_item`.`created` LIMIT 100");
	Logger::debug('mailstream_cron processing ' . count($ms_item_ids) . ' items');
	foreach ($ms_item_ids as $ms_item_id) {
		if (!$ms_item_id['message-id'] || !strlen($ms_item_id['message-id'])) {
			Logger::info('mailstream_cron: Item ' . $ms_item_id['id'] . ' URI ' . $ms_item_id['uri'] . ' has no message-id');
		}
		$item = Item::selectFirst([], ['id' => $ms_item_id['id']]);
		$users = q("SELECT * FROM `user` WHERE `uid` = %d", intval($item['uid']));
		$user = $users[0];
		if ($user && $item) {
			mailstream_send($a, $ms_item_id['message-id'], $item, $user);
		}
		else {
			Logger::info('mailstream_cron: Unable to find item ' . $ms_item_id['id']);
			q("UPDATE `mailstream_item` SET `completed` = now() WHERE `message-id` = %d", intval($ms_item['message-id']));
		}
	}
	mailstream_tidy();
}

function mailstream_addon_settings(&$a,&$s) {
	$enabled = PConfig::get(local_user(), 'mailstream', 'enabled');
	$address = PConfig::get(local_user(), 'mailstream', 'address');
	$nolikes = PConfig::get(local_user(), 'mailstream', 'nolikes');
	$attachimg= PConfig::get(local_user(), 'mailstream', 'attachimg');
	$template = Renderer::getMarkupTemplate('settings.tpl', 'addon/mailstream/');
	$s .= Renderer::replaceMacros($template, [
				 '$enabled' => [
					'mailstream_enabled',
					L10n::t('Enabled'),
					$enabled],
				 '$address' => [
					'mailstream_address',
					L10n::t('Email Address'),
					$address,
					L10n::t("Leave blank to use your account email address")],
				 '$nolikes' => [
					'mailstream_nolikes',
					L10n::t('Exclude Likes'),
					$nolikes,
					L10n::t("Check this to omit mailing \"Like\" notifications")],
				 '$attachimg' => [
					'mailstream_attachimg',
					L10n::t('Attach Images'),
					$attachimg,
					L10n::t("Download images in posts and attach them to the email.  Useful for reading email while offline.")],
				 '$title' => L10n::t('Mail Stream Settings'),
				 '$submit' => L10n::t('Save Settings')]);
}

function mailstream_addon_settings_post($a,$post) {
	if ($_POST['mailstream_address'] != "") {
		PConfig::set(local_user(), 'mailstream', 'address', $_POST['mailstream_address']);
	}
	else {
		PConfig::delete(local_user(), 'mailstream', 'address');
	}
	if ($_POST['mailstream_nolikes']) {
		PConfig::set(local_user(), 'mailstream', 'nolikes', $_POST['mailstream_enabled']);
	}
	else {
		PConfig::delete(local_user(), 'mailstream', 'nolikes');
	}
	if ($_POST['mailstream_enabled']) {
		PConfig::set(local_user(), 'mailstream', 'enabled', $_POST['mailstream_enabled']);
	}
	else {
		PConfig::delete(local_user(), 'mailstream', 'enabled');
	}
	if ($_POST['mailstream_attachimg']) {
		PConfig::set(local_user(), 'mailstream', 'attachimg', $_POST['mailstream_attachimg']);
	}
	else {
		PConfig::delete(local_user(), 'mailstream', 'attachimg');
	}
}

function mailstream_tidy() {
	$r = q("SELECT id FROM mailstream_item WHERE completed IS NOT NULL AND completed < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
	foreach ($r as $rr) {
		q('DELETE FROM mailstream_item WHERE id = %d', intval($rr['id']));
	}
	Logger::debug('mailstream_tidy: deleted ' . count($r) . ' old items');
}
