<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\MiniTest\Test;


use Akeeba\Engine\Postproc\Connector\S3v4\Connector;

/**
 * Upload small files (under 1MB) using a string source
 *
 * @package Akeeba\MiniTest\Test
 */
class SmallInlineFilesOnlyUpload extends SmallInlineFiles
{
	public static function setup(Connector $s3, array $options): void
	{
		self::$deleteRemote  = false;
		self::$downloadAfter = false;

		parent::setup($s3, $options);
	}

}