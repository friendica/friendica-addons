<?php
/**
 * SPDX-FileCopyrightText: 2010 -2026 the Friendica project
 *
 * SPDX-License-Identifier: CC0-1.0
 */

declare(strict_types=1);

return \Rector\Config\RectorConfig::configure()
	->withPaths([
		__DIR__ . '/..',
	])
	->withSkipPath(__DIR__ . '/../advancedcontentfilter/vendor')
	->withSkipPath(__DIR__ . '/../blockbot/vendor')
	->withSkipPath(__DIR__ . '/../monolog/vendor')
	->withSkipPath(__DIR__ . '/../phpmailer/vendor')
	->withSkipPath(__DIR__ . '/../s3_storage/vendor')
	->withSkipPath(__DIR__ . '/../saml/vendor')
	->withSkipPath(__DIR__ . '/../securemail/vendor')
	->withSkipPath(__DIR__ . '/../tesseract/vendor')
	->withIndent("\t", 4)
	->withPhpVersion(80200)
	->withPhpLevel(0)
	->withTypeCoverageLevel(0)
	->withDeadCodeLevel(0)
	->withCodeQualityLevel(0)
	->withSets([
		//\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_85,
		//\Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_120,
		//\Rector\PHPUnit\Set\PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
	])
	->withSkip([
		//\Rector\Php56\Rector\FuncCall\PowToExpRector::class,
		//\Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class,
	])
;
