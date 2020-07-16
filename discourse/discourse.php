<?php

/**
 * Name: Discourse Mail Connector
 * Description: Improves mails from Discourse in mailing list mode
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 *
 */
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Protocol;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Content\Text\Markdown;
use Friendica\Util\Network;
use Friendica\Util\Strings;
Use Friendica\Util\DateTimeFormat;

/* Todo:
 * - Obtaining API tokens to be able to read non public posts as well
 * - Handling duplicates (possibly using some non visible marker)
 * - Fetching missing posts
 * - Fetch topic information
 * - Support mail free mode when write tokens are available
 * - Fix incomplete (relative) links (hosts are missing)
*/

function discourse_install()
{
	Hook::register('email_getmessage',        __FILE__, 'discourse_email_getmessage');
	Hook::register('connector_settings',      __FILE__, 'discourse_settings');
	Hook::register('connector_settings_post', __FILE__, 'discourse_settings_post');
}

function discourse_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$enabled = intval(DI::pConfig()->get(local_user(), 'discourse', 'enabled'));

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/discourse/');
	$s .= Renderer::replaceMacros($t, [
		'$title'   => DI::l10n()->t('Discourse'),
		'$enabled' => ['enabled', DI::l10n()->t('Enable processing of Discourse mailing list mails'), $enabled, DI::l10n()->t('If enabled, incoming mails from Discourse will be improved so they look much better. To make it work, you have to configure the e-mail settings in Friendica. You also have to enable the mailing list mode in Discourse. Then you have to add the Discourse mail account as contact.')],
		'$submit'  => DI::l10n()->t('Save Settings'),
	]);
}

function discourse_settings_post(App $a)
{
	if (!local_user() || empty($_POST['discourse-submit'])) {
                return;
        }

	DI::pConfig()->set(local_user(), 'discourse', 'enabled', intval($_POST['enabled']));
}

function discourse_email_getmessage(App $a, &$message)
{
	if (empty($message['item']['uid'])) {
		return;
	}

	if (!DI::pConfig()->get($message['item']['uid'], 'discourse', 'enabled')) {
		return;
	}

	// We do assume that all Discourse servers are running with SSL
	if (preg_match('=topic/(.*\d)/(.*\d)@(.*)=', $message['item']['uri'], $matches) &&
		discourse_fetch_post_from_api($message, $matches[2], $matches[3])) {
		Logger::info('Fetched comment via API (message-id mode)', ['host' => $matches[3], 'topic' => $matches[1], 'post' => $matches[2]]);
		return;
	}

	if (preg_match('=topic/(.*\d)@(.*)=', $message['item']['uri'], $matches) &&
		discourse_fetch_topic_from_api($message, 'https://' . $matches[2], $matches[1], 1)) {
		Logger::info('Fetched starting post via API (message-id mode)', ['host' => $matches[2], 'topic' => $matches[1]]);
		return;
	}

	// Search in the text part for the link to the discourse entry and the text body
	if (!empty($message['text'])) {
		$message = discourse_get_text($message);
	}

	if (empty($message['item']['plink']) || !preg_match('=(http.*)/t/.*/(.*\d)/(.*\d)=', $message['item']['plink'], $matches)) {
		Logger::info('This is no Discourse post');
		return;
	}

	if (discourse_fetch_topic_from_api($message, $matches[1], $matches[2], $matches[3])) {
		Logger::info('Fetched post via API (plink mode)', ['host' => $matches[1], 'topic' => $matches[2], 'id' => $matches[3]]);
		return;
	}

	Logger::info('Fallback mode', ['plink' => $message['item']['plink']]);
	// Search in the HTML part for the discourse entry and the author profile
	if (!empty($message['html'])) {
		$message = discourse_get_html($message);
	}

	// Remove the title on comments, they don't serve any purpose there
	if ($message['item']['parent-uri'] != $message['item']['uri']) {
		unset($message['item']['title']);
	}
}

function discourse_fetch_post($host, $topic, $pid)
{
	$url = $host . '/t/' . $topic . '/' . $pid . '.json';
	$curlResult = Network::curl($url);
	if (!$curlResult->isSuccess()) {
		Logger::info('No success', ['url' => $url]);
		return false;
	}

	$raw = $curlResult->getBody();
	$data = json_decode($raw, true);
	$posts = $data['post_stream']['posts'];
	foreach($posts as $post) {
		if ($post['post_number'] != $pid) {
			/// @todo Possibly fetch missing posts here
			continue;
		}
		Logger::info('Got post data from topic', $post);
		return $post;
	}

	Logger::info('Post not found', ['host' => $host, 'topic' => $topic, 'pid' => $pid]);
	return false;
}

function discourse_fetch_topic_from_api(&$message, $host, $topic, $pid)
{
	$post = discourse_fetch_post($host, $topic, $pid);
	if (empty($post)) {
		return false;
	}

	$message = discourse_process_post($message, $post, $host);
	return true;
}

function discourse_fetch_post_from_api(&$message, $post, $host)
{
	$hostaddr = 'https://' . $host;
	$url = $hostaddr . '/posts/' . $post . '.json';
	$curlResult = Network::curl($url);
	if (!$curlResult->isSuccess()) {
		return false;
	}

	$raw = $curlResult->getBody();
	$data = json_decode($raw, true);
	if (empty($data)) {
		return false;
	}

	$message = discourse_process_post($message, $data, $hostaddr);

	Logger::info('Got API data', $message);
	return true;
}

function discourse_get_user($post, $hostaddr)
{
	$host = parse_url($hostaddr, PHP_URL_HOST);

	// Currently unused contact fields:
	// - display_username
	// - user_id

	$contact = [];
	$contact['uid'] = 0;
	$contact['network'] = Protocol::DISCOURSE;
	$contact['name'] = $contact['nick'] = $post['username'];
	if (!empty($post['name'])) {
		$contact['name'] = $post['name'];
	}

	$contact['about'] = $post['user_title'];

	if (parse_url($post['avatar_template'], PHP_URL_SCHEME)) {
		$contact['photo'] = str_replace('{size}', '300', $post['avatar_template']);
	} else {
		$contact['photo'] = $hostaddr . str_replace('{size}', '300', $post['avatar_template']);
	}

	$contact['addr'] = $contact['nick'] . '@' . $host;
	$contact['contact-type'] = Contact::TYPE_PERSON;
	$contact['url'] = $hostaddr . '/u/' . $contact['nick'];
	$contact['nurl'] = Strings::normaliseLink($contact['url']);
	$contact['baseurl'] = $hostaddr;
	Logger::info('Contact', $contact);
	$contact['id'] = Contact::getIdForURL($contact['url'], 0, false, $contact);
        if (!empty($contact['id'])) {
		$avatar = $contact['photo'];
		unset($contact['photo']);
		DBA::update('contact', $contact, ['id' => $contact['id']]);
		Contact::updateAvatar($avatar, 0, $contact['id']);
		$contact['photo'] = $avatar;
	}

	return $contact;
}

function discourse_process_post($message, $post, $hostaddr)
{
	$host = parse_url($hostaddr, PHP_URL_HOST);

	$message['html'] = $post['cooked'];

	$contact = discourse_get_user($post, $hostaddr);
	$message['item']['author-id'] = $contact['id'];
	$message['item']['author-link'] = $contact['url'];
	$message['item']['author-name'] = $contact['name'];
	$message['item']['author-avatar'] = $contact['photo'];
	$message['item']['created'] = DateTimeFormat::utc($post['created_at']);
	$message['item']['plink'] = $hostaddr . '/t/' . $post['topic_slug'] . '/' . $post['topic_id'] . '/' . $post['post_number'];

	if ($post['post_number'] == 1) {
		$message['item']['parent-uri'] = $message['item']['uri'] = 'topic/' . $post['topic_id'] . '@' . $host;

		// Remove the Discourse forum name from the subject
		$pattern = '=\[.*\].*\s(\[.*\].*)=';
		if (preg_match($pattern, $message['item']['title'])) {
			$message['item']['title'] = preg_replace($pattern, '$1', $message['item']['title']);
		}
		/// @ToDo Fetch thread information
	} else {
		$message['item']['uri'] = 'topic/' . $post['topic_id'] . '/' . $post['id'] . '@' . $host;
		unset($message['item']['title']);
		if (empty($post['reply_to_post_number']) || $post['reply_to_post_number'] == 1) {
			$message['item']['parent-uri'] = 'topic/' . $post['topic_id'] . '@' . $host;
		} else {
			$reply = discourse_fetch_post($hostaddr, $post['topic_id'], $post['reply_to_post_number']);
			$message['item']['parent-uri'] = 'topic/' . $post['topic_id'] . '/' . $reply['id'] . '@' . $host;
		}
	}

	return $message;
}

function discourse_get_html($message)
{
	$doc = new DOMDocument();
	$doc2 = new DOMDocument();
	$doc->preserveWhiteSpace = false;

	$html = mb_convert_encoding($message['html'], 'HTML-ENTITIES', "UTF-8");
	@$doc->loadHTML($html, LIBXML_HTML_NODEFDTD);

	$xpath = new DomXPath($doc);

	// Fetch the first 'div' before the 'hr' - hopefully this fits for all systems
	$result = $xpath->query("//hr//preceding::div[1]");
	$div = $doc2->importNode($result->item(0), true);
	$doc2->appendChild($div);
	$message['html'] = $doc2->saveHTML();
	Logger::info('Found html body', ['html' => $message['html']]);

	$profile = discourse_get_profile($xpath);
	if (!empty($profile['url'])) {
		Logger::info('Found profile', $profile);
		$message['item']['author-id'] = Contact::getIdForURL($profile['url'], 0, false, $profile);
		$message['item']['author-link'] = $profile['url'];
		$message['item']['author-name'] = $profile['name'];
		$message['item']['author-avatar'] = $profile['photo'];
	}

	return $message;
}

function discourse_get_text($message)
{
	$text = $message['text'];
	$text = str_replace("\r", '', $text);
	$pos = strpos($text, "\n---\n");
	if ($pos == 0) {
		Logger::info('No separator found', ['text' => $text]);
		return $message;
	}

	$message['text'] = trim(substr($text, 0, $pos));

	Logger::info('Found text body', ['text' => $message['text']]);

	$message['text'] = Markdown::toBBCode($message['text']);

	$text = substr($text, $pos);
	Logger::info('Found footer', ['text' => $text]);
	if (preg_match('=\((http.*/t/.*/.*\d/.*\d)\)=', $text, $link)) {
		$message['item']['plink'] = $link[1];
		Logger::info('Found plink', ['plink' => $message['item']['plink']]);
	}
	return $message;
}

function discourse_get_profile($xpath)
{
	$profile = [];
	$list = $xpath->query("//td//following::img");
	foreach ($list as $node) {
		$attr = [];
		foreach ($node->attributes as $attribute) {
			$attr[$attribute->name] = $attribute->value;
		}

		if (!empty($attr['src']) && !empty($attr['title'])
			&& !empty($attr['width']) && !empty($attr['height'])
			&& ($attr['width'] == $attr['height'])) {
			$profile = ['photo' => $attr['src'], 'name' => $attr['title']];
			break;
		}
	}

	$list = $xpath->query("//td//following::a");
	foreach ($list as $node) {
		if (!empty(trim($node->textContent)) && $node->attributes->length) {
			$attr = [];
			foreach ($node->attributes as $attribute) {
				$attr[$attribute->name] = $attribute->value;
			}
			if (!empty($attr['href']) && (strpos($attr['href'], '/' . $profile['name']))) {
				$profile['url'] = $attr['href'];
				break;
			}
		}
	}
	return $profile;
}
