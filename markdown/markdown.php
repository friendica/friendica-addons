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

function markdown_addon_settings(App $a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$enabled = intval(DI::pConfig()->get(local_user(), 'markdown', 'enabled'));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/markdown/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['enabled', DI::l10n()->t('Enable Markdown parsing'), $enabled, DI::l10n()->t('If enabled, adds Markdown support to the Compose Post form.')],
	]);

	$data = [
		'addon' => 'markdown',
		'title' => DI::l10n()->t('Markdown Settings'),
		'html'  => $html,
	];
}

function markdown_addon_settings_post(App $a, array &$b)
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

	// Escape elements that shouldn't be parsed
	$request['body'] = \Friendica\Content\Text\BBCode::performWithEscapedTags(
		$request['body'],
		['code', 'noparse', 'nobb', 'pre', 'share', 'url', 'img', 'bookmark',
			'audio', 'video', 'youtube', 'vimeo', 'attachment', 'iframe', 'map', 'mail'],
		function ($body) {
			// Escape mentions which username can contain Markdown-like characters
			// See https://github.com/friendica/friendica/issues/9486
			return \Friendica\Util\Strings::performWithEscapedBlocks($body, '/[@!][^@\s]+@[^\s]+\w/', function ($text) {
				// Markdown accepts literal HTML but we do not in post body, so we need to escape left chevrons
				// (right chevrons are used for quoting in Markdown)
				// See https://github.com/friendica/friendica/issues/10634
				$text = strtr($text, ['<' => '&lt;']);

				return Markdown::toBBCode($text);
			});
		}
	);
}
