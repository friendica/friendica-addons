<?php
/**
 * Name: Tesseract OCR
 * Description: Use OCR to get text from images
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */

use Friendica\Core\Hook;
use Friendica\Core\System;
use Friendica\DI;
use thiagoalessio\TesseractOCR\TesseractOCR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function tesseract_install()
{
	Hook::register('ocr-detection', __FILE__, 'tesseract_ocr_detection');

	DI::logger()->notice('installed tesseract');
}

function tesseract_ocr_detection(&$media)
{
	$ocr = new TesseractOCR();
	try {
		$languages = $ocr->availableLanguages();
		if ($languages) {
			/** @phpstan-ignore-next-line ignore call of \thiagoalessio\TesseractOCR\Option::lang() */
			$ocr->lang(implode('+', $languages));
		}
		$ocr->tempDir(System::getTempPath());
		$ocr->imageData($media['img_str'], strlen($media['img_str']));
		$media['description'] = $ocr->run();
	} catch (\Throwable $th) {
		DI::logger()->info('Error calling TesseractOCR', ['message' => $th->getMessage()]);
	}
}
