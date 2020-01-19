<?php
/**
 * Name: Markdown
 * Description: Parse Markdown code when creating new items
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Content\Text\Markdown;
use Friendica\Core\Renderer;
use Friendica\DI;

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

	$enabled = intval(DI::pConfig()->get(local_user(), 'markdown', 'enabled'));

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/markdown/');
	$s .= Renderer::replaceMacros($t, [
		'$title'   => DI::l10n()->t('Markdown'),
		'$enabled' => ['enabled', DI::l10n()->t('Enable Markdown parsing'), $enabled, DI::l10n()->t('If enabled, self created items will additionally be parsed via Markdown.')],
		'$submit'  => DI::l10n()->t('Save Settings'),
	]);
}

function markdown_addon_settings_post(App $a, &$b)
{
	if (!local_user() || empty($_POST['markdown-submit'])) {
		return;
	}

	DI::pConfig()->set(local_user(), 'markdown', 'enabled', intval($_POST['enabled']));
}

function markdown_post_local_start(App $a, &$request) {
	if (empty($request['body']) || !DI::pConfig()->get(local_user(), 'markdown', 'enabled')) {
		return;
	}

	// Elements that shouldn't be parsed
	$elements = ['code', 'noparse', 'nobb', 'pre', 'share', 'url', 'img', 'bookmark',
		'audio', 'video', 'youtube', 'vimeo', 'attachment', 'iframe', 'map', 'mail'];
	foreach ($elements as $element) {
		$request['body'] = preg_replace_callback("/\[" . $element . "(.*?)\](.*?)\[\/" . $element . "\]/ism",
			function ($match) use ($element) {
				return '[' . $element . '-b64' . base64_encode($match[1]) . ']' . base64_encode($match[2]) . '[/b64-' . $element . ']';
			},
			$request['body']
		);
	}

	$request['body'] = Markdown::toBBCode($request['body']);

	foreach (array_reverse($elements) as $element) {
		$request['body'] = preg_replace_callback("/\[" . $element . "-b64(.*?)\](.*?)\[\/b64-" . $element . "\]/ism",
			function ($match) use ($element) {
				return '[' . $element . base64_decode($match[1]) . ']' . base64_decode($match[2]) . '[/' . $element . ']';
			},
			$request['body']
		);
	}
}
