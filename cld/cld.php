<?php
/**
 * Name: Compact Language Detector
 * Description: Improved language detection
 * Version: 0.1
 * Author: Michael Vogel <heluecht@pirati.ca>
 */

use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;

function cld_install()
{
	Hook::register('detect_languages', __FILE__, 'cld_detect_languages');
}

function cld_detect_languages(array &$data)
{
	if (!in_array('cld2', get_loaded_extensions())) {
		Logger::warning('CLD2 is not installed.');
		return;
	}

	$cld2 = new \CLD2Detector();

	$cld2->setEncodingHint(CLD2Encoding::UTF8); // optional, hints about text encoding
	$cld2->setPlainText(true);

	$result = $cld2->detect($data['text']);

	if ($data['detected']) {
		$original = array_key_first($data['detected']);
	} else {
		$original = '';
	}

	$detected = $result['language_code'];
	if ($detected == 'pt') {
		$detected = 'pt-PT';
	} elseif ($detected == 'az') {
		$detected = 'az-Latn';
	} elseif ($detected == 'bs') {
		$detected = 'bs-Latn';
	} elseif ($detected == 'el') {
		$detected = 'el-monoton';
	} elseif ($detected == 'ht') {
		$detected = 'fr';
	} elseif ($detected == 'iw') {
		$detected = 'he';
	} elseif ($detected == 'jw') {
		$detected = 'jv';
	} elseif ($detected == 'ms') {
		$detected = 'ms-Latn';
	} elseif ($detected == 'no') {
		$detected = 'nb';
	} elseif ($detected == 'sr') {
		$detected = 'sr-Cyrl';
	} elseif ($detected == 'zh') {
		$detected = 'zh-Hans';
	} elseif ($detected == 'zh-Hant') {
		$detected = 'zh-hant';
	}

	// languages that aren't supported via the base language detection
	if (in_array($detected, ['ceb', 'hmn', 'ht', 'kk', 'ky', 'mg', 'mk', 'ml', 'ny', 'or', 'pa', 'rw', 'su', 'st', 'tg', 'ts', 'xx-Qaai'])) {
		return;
	}

	if (!$result['is_reliable']) {
		Logger::debug('Unreliable detection', ['uri-id' => $data['uri-id'], 'original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
		if (($original == $detected) && ($data['detected'][$original] < $result['language_probability'] / 100)) {
			$data['detected'][$original] = $result['language_probability'] / 100;
		}
		return;
	}

	$available = array_keys(DI::l10n()->convertForLanguageDetection(DI::l10n()->getAvailableLanguages(true)));
	
	if (!in_array($detected, $available)) {
		Logger::debug('Unsupported language', ['uri-id' => $data['uri-id'], 'original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
		return;
	}

	if ($original != $detected) {
		Logger::debug('Detected different language', ['uri-id' => $data['uri-id'], 'original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
	}

	$length = count($data['detected']);
	if ($length > 0) {
		unset($data['detected'][$detected]);
		$data['detected'] = array_merge([$detected => $result['language_probability'] / 100], array_slice($data['detected'], 0, $length - 1));
	} else {
		$data['detected'] = [$detected => $result['language_probability'] / 100];
	}
}
