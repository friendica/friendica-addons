<?php
/*
 * Name: WebDAV Storage
 * Description: Adds the possibility to use WebDAV as a selectable storage backend
 * Version: 1.0
 * Author: Philipp Holzer
 */

use Friendica\Addon\webdav_storage\src\WebDav;
use Friendica\Addon\webdav_storage\src\WebDavConfig;
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function webdav_storage_install($a)
{
	Hook::register('storage_instance' , __FILE__, 'webdav_storage_instance');
	Hook::register('storage_config' , __FILE__, 'webdav_storage_config');
	DI::storageManager()->register(WebDav::class);
}

function webdav_storage_uninstall()
{
	DI::storageManager()->unregister(WebDav::class);
}

function webdav_storage_instance(App $a, array &$data)
{
	$config          = new WebDavConfig(DI::l10n(), DI::config(), DI::httpClient());
	$data['storage'] = new WebDav($config->getUrl(), $config->getAuthOptions(), DI::httpClient(), DI::logger());
}

function webdav_storage_config(App $a, array &$data)
{
	$data['storage_config'] = new WebDavConfig(DI::l10n(), DI::config(), DI::httpClient());
}
