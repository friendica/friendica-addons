<?php
/*
 * Name: S3 Storage
 * Description: Adds the possibility to use Amazon S3 as a selectable storage backend
 * Version: 1.0
 * Author: Philipp Holzer
 */

use Friendica\Addon\s3_storage\src\S3Client;
use Friendica\Addon\s3_storage\src\S3Config;
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function s3_storage_install()
{
	Hook::register('storage_instance' , __FILE__, 's3_storage_instance');
	Hook::register('storage_config' , __FILE__, 's3_storage_config');
	DI::storageManager()->register(S3Client::class);
}

function s3_storage_uninstall()
{
	DI::storageManager()->unregister(S3Client::class);
}

function s3_storage_instance(array &$data)
{
	if ($data['name'] == S3Client::getName()) {
		$config          = new S3Config(DI::l10n(), DI::config());
		$data['storage'] = new S3Client($config->getConfig(), $config->getBucket());
	}
}

function s3_storage_config(array &$data)
{
	if ($data['name'] == S3Client::getName()) {
		$data['storage_config'] = new S3Config(DI::l10n(), DI::config());
	}
}
