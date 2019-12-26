<?php
/**
 * Name: Markdown
 * Description: Parse Markdown code when creating new items
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Content\Text\Markdown;
use Friendica\Core\Renderer;
use Friendica\Core\PConfig;
use Friendica\Core\L10n;

function markdown_install() {
	Hook::register('post_local_start',      __FILE__, 'markdown_post_local_start');
	Hook::register('addon_settings',        __FILE__, 'markdown_addon_settings');
	Hook::register('addon_settings_post',   __FILE__, 'markdown_addon_settings_post');
}

function markdown_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$enabled = intval(PConfig::get(local_user(), 'markdown', 'enabled'));

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/markdown/');
	$s .= Renderer::replaceMacros($t, [
		'$title'   => L10n::t('Markdown'),
		'$enabled' => ['enabled', L10n::t('Enable Markdown parsing'), $enabled, L10n::t('If enabled, self created items will additionally be parsed via Markdown.')],
		'$submit'  => L10n::t('Save Settings'),
	]);
}

function markdown_addon_settings_post(App $a, &$b)
{
	if (!local_user() || empty($_POST['markdown-submit'])) {
		return;
	}

	PConfig::set(local_user(), 'markdown', 'enabled', intval($_POST['enabled']));
}

function markdown_post_local_start(App $a, &$request) {
	if (empty($request['body']) || !PConfig::get(local_user(), 'markdown', 'enabled')) {
		return;
	}

	$request['body'] = Markdown::toBBCode($request['body']);
}
