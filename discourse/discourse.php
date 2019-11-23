<?php

/**
 * Name: Discourse Mail Connector
 * Description: Improves mails from Discourse in mailing list mode
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 *
 */
//use DOMDocument;
//use DOMXPath;
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Util\XML;
use Friendica\Content\Text\Markdown;
use Friendica\Util\Network;
Use Friendica\Util\DateTimeFormat;

function discourse_install()
{
	Hook::register('email_getmessage',     __FILE__, 'discourse_email_getmessage');
	Hook::register('email_getmessage_end', __FILE__, 'discourse_email_getmessage_end');
	Hook::register('addon_settings',       __FILE__, 'discourse_addon_settings');
	Hook::register('addon_settings_post',  __FILE__, 'discourse_addon_settings_post');
}

function discourse_uninstall()
{
	Hook::unregister('email_getmessage',     __FILE__, 'discourse_email_getmessage');
	Hook::unregister('email_getmessage_end', __FILE__, 'discourse_email_getmessage_end');
	Hook::unregister('addon_settings',       __FILE__, 'discourse_addon_settings');
	Hook::unregister('addon_settings_post',  __FILE__, 'discourse_addon_settings_post');
}

function discourse_addon_settings(App $a, &$s)
{
}

function discourse_addon_settings_post(App $a)
{
}

function discourse_email_getmessage(App $a, &$message)
{
//	Logger::info('Got raw message', $message);
	// Remove the title on comments, they don't serve any purpose there
	if ($message['item']['parent-uri'] != $message['item']['uri']) {
		unset($message['item']['title']);
	}

	if (preg_match('=topic/(.*)/(.*)@(.*)=', $message['item']['uri'], $matches)) {
		Logger::info('Got post data', ['topic' => $matches[1], 'post' => $matches[2], 'host' => $matches[3]]);
		if (discourse_fetch_post_from_api($message, $matches[2], $matches[3])) {
			return;
		}
	}

	// Search in the text part for the link to the discourse entry and the text body
	// The text body is used as alternative, if the fetched HTML isn't working
	if (!empty($message['text'])) {
		discourse_get_text($message);
	}

	if (!empty($message['item']['plink'])) {
		if (preg_match('=(http.*)/t/.*/(.*\d)/(.*\d)=', $message['item']['plink'], $matches)) {
			if (discourse_fetch_topic_from_api($message, $matches[1], $matches[1], $matches[1])) {
				return;
			}
		}
	}

	// Search in the HTML part for the discourse entry and the author profile
	if (!empty($message['html'])) {
		discourse_get_html($message);
	}
}

function discourse_fetch_topic_from_api(&$message, $host, $topic, $pid)
{
	$url = $host . '/t/' . $topic . '/posts.json?posts_ids[]=' . $pid;
	$curlResult = Network::curl($url);
	if (!$curlResult->isSuccess()) {
		return false;
	}
	$raw = $curlResult->getBody();
	$data = json_decode($raw, true);
	$posts = $data['post_stream']['posts'];
	foreach($posts as $post) {
		if ($post['post_number'] != $pid) {
			continue;
		}
		Logger::info('Got post data from topic', $post);
		discourse_process_post($message, $post);
		return true;
	}
	return false;
}

function discourse_fetch_post_from_api(&$message, $post, $host)
{
	$url = "https://" . $host . '/posts/' . $post . '.json';
	$curlResult = Network::curl($url);
	if (!$curlResult->isSuccess()) {
		return false;
	}

	$raw = $curlResult->getBody();
	$data = json_decode($raw, true);
	if (empty($data)) {
		return false;
	}

	discourse_process_post($message, $data);

	Logger::info('Got API data', $message);
	return true;
}

function discourse_process_post(&$message, $post)
{
	if ($post['post_number'] == 1) {
		// Thread information
	}

	$nick = $post['username'];
	$name = $post['name'];
	// User information

	$message['html'] = $post['cooked'];
	$message['text'] = $post['raw'];
	$message['item']['created'] = DateTimeFormat::utc($post['created_at']);
}

function discourse_get_html(&$message)
{
	$doc = new DOMDocument();
	$doc2 = new DOMDocument();
	$doc->preserveWhiteSpace = false;

	$html = mb_convert_encoding($message['html'], 'HTML-ENTITIES', "UTF-8");
	@$doc->loadHTML($html, LIBXML_HTML_NODEFDTD);

	$xpath = new DomXPath($doc);

	// Fetch the first 'div' before the 'hr' -hopefully this fits for all systems
	$result = $xpath->query("//hr//preceding::div[1]");
	$div = $doc2->importNode($result->item(0), true);
	$doc2->appendChild($div);
	$message['html'] = $doc2->saveHTML();
	Logger::info('Found html body', ['html' => $message['html']]);

	$profile = discourse_get_profile($xpath);
	if (!empty($profile)) {
		Logger::info('Found profile', $profile);
/*
		$message['item']['author-avatar'] = $contact['avatar'];
		$message['item']['author-link'] = $profile['link'];
		$message['item']['author-name'] = $profile['name'];
*/
	}
}

function discourse_get_text(&$message)
{
	$text = $message['text'];
	$text = str_replace("\r", '', $text);
	$pos = strpos($text, "\n---\n");
	if ($pos > 0) {
		$message['text'] = trim(substr($text, 0, $pos));
		Logger::info('Found text body', ['text' => $message['text']]);

		$message['text'] = Markdown::toBBCode($message['text']);

		$text = substr($text, $pos);
		if (preg_match('=\((http.*?)\)=', $text, $link)) {
			$message['item']['plink'] = $link[1];
			Logger::info('Found plink', ['plink' => $message['item']['plink']]);
		}
	} else {
		Logger::info('No separator found', ['text' => $text]);
	}
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
			$profile = ['avatar' => $attr['src'], 'name' => $attr['title']];
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
				$profile['link'] = $attr['href'];
				break;
			}
		}
	}
	return $profile;
}

function discourse_email_getmessage_end(App $a, &$message)
{
//	Logger::info('Got converted message', $message);
}
