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
	Hook::register('get_language', 'addon/cld/cld.php', 'cld_get_language');
}

function cld_get_language(array &$data)
{
	if (!in_array('cld2', get_loaded_extensions())) {
		Logger::warning('CLD2 is not installed.');
		return;
	}

	$cld2 = new \CLD2Detector();

	$cld2->setEncodingHint(CLD2Encoding::UTF8); // optional, hints about text encoding

	$result = $cld2->detect($data['text']);
	
	if ($data['detected']) {
		$original = array_key_first($data['detected']);
	} else {
		$original = '';
	}

	$detected = $result['language_code'];
	if ($detected == 'pt') {
		$detected = 'pt-PT';
	} elseif ($detected == 'el') {
		$detected = 'el-monoton';
	} elseif ($detected == 'no') {
		$detected = 'nb';
	} elseif ($detected == 'zh') {
		$detected = 'zh-Hans';
	} elseif ($detected == 'zh-Hant') {
		$detected = 'zh-hant';
	}

	if (!$result['is_reliable']) {
		Logger::debug('Unreliable detection', ['original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
		return;
	}

	if ($original == $detected) {
		return;
	}

	$available = array_keys(DI::l10n()->convertForLanguageDetection(DI::l10n()->getAvailableLanguages(true)));
	
	if (!in_array($detected, $available)) {
		Logger::debug('Unsupported language', ['original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
		return;
	}

	Logger::debug('Detected different language', ['original' => $original, 'detected' => $detected, 'name' => $result['language_name'], 'probability' => $result['language_probability'], 'text' => $data['text']]);
	$data['detected'] = [$detected => $result['language_probability'] / 100];
}
