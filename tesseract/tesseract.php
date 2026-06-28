<?php

/**
 * Name: Tesseract OCR
 * Description: Use OCR to extract text from images (with timeout, resource limits, alt-text & format checks)
 * Version: 0.2
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 *  * Modified by: Matthias Ebers <http://loma.ml/profile/feb>
 */

use Friendica\Core\Hook;
use Friendica\Core\System;
use Friendica\DI;
use thiagoalessio\TesseractOCR\TesseractOCR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * Called when the addon is enabled
 */
function tesseract_install()
{
	Hook::register('ocr-detection', __FILE__, 'tesseract_ocr_detection');

	$wrapperPath = __DIR__ . '/tesseract-limited.sh';

	// Create a wrapper script with timeout and resource constraints
	if (!file_exists($wrapperPath)) {
		$script = <<<BASH
#!/bin/bash
# Wrapper for tesseract with timeout and resource limits
timeout 5s nice -n 10 ionice -c3 /usr/bin/tesseract "\$@"
BASH;

		file_put_contents($wrapperPath, $script);
		chmod($wrapperPath, 0755);

		DI::logger()->notice('Tesseract wrapper script created', ['path' => $wrapperPath]);
	} else {
		DI::logger()->info('Tesseract wrapper script already exists', ['path' => $wrapperPath]);
	}

	DI::logger()->notice('Tesseract OCR addon installed');
}

/**
 * Called when the addon is disabled
 */
function tesseract_uninstall()
{
	$wrapperPath = __DIR__ . '/tesseract-limited.sh';

	if (file_exists($wrapperPath)) {
		unlink($wrapperPath);
		DI::logger()->notice('Tesseract wrapper script removed', ['path' => $wrapperPath]);
	}

	Hook::unregister('ocr-detection', __FILE__, 'tesseract_ocr_detection');
	DI::logger()->notice('Tesseract OCR addon uninstalled');
}

/**
 * Main OCR processing hook for incoming images
 */
function tesseract_ocr_detection(&$media)
{
	// Skip OCR if image already contains an alt-text
	if (!empty($media['description'])) {
		DI::logger()->debug('Image already has description, skipping OCR');
		return;
	}

	// Only allow specific MIME types for OCR
	$allowedTypes = ['image/jpeg', 'image/png', 'image/bmp', 'image/tiff'];
	if (!empty($media['type']) && !in_array($media['type'], $allowedTypes)) {
		DI::logger()->debug('Unsupported image type for OCR', ['type' => $media['type']]);
		return;
	}

	// Alternatively skip GIF files based on filename
	if (empty($media['type']) && !empty($media['filename']) && preg_match('/\.gif$/i', $media['filename'])) {
		DI::logger()->debug('GIF image detected via filename, skipping OCR');
		return;
	}

	$ocr = new TesseractOCR();

	try {
		// Use wrapper script with timeout and niceness
		$ocr->executable(__DIR__ . '/tesseract-limited.sh');

		// Detect and set available languages
		$languages = $ocr->availableLanguages();
		if ($languages) {
			// @phpstan-ignore-next-line
			$ocr->lang(implode('+', $languages));
		}

		// Use Friendica's temporary path
		$ocr->tempDir(System::getTempPath());

		// Provide raw image data to Tesseract
		$ocr->imageData($media['img_str'], strlen($media['img_str']));

		// Run OCR and assign description if text is found
		$text = trim($ocr->run());

		if (!empty($text)) {
			$media['description'] = $text;
			DI::logger()->debug('OCR text detected', ['text' => $text]);
		} else {
			DI::logger()->debug('No text detected in image');
		}
	} catch (\Throwable $th) {
		DI::logger()->info('Error calling TesseractOCR', ['message' => $th->getMessage()]);
	}
}
