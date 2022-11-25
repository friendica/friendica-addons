<?php
/**
 * Name: Fancybox
 * Description: Open media attachments of posts into a fancybox overlay.
 * Version: 1.01
 * Author: Grischa Brockhaus <grischa@brockha.us>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function fancybox_install()
{
	Hook::register('head', __FILE__, 'fancybox_head');
	Hook::register('footer', __FILE__, 'fancybox_footer');
	Hook::register('prepare_body_final', __FILE__, 'fancybox_render');
}

function fancybox_head(App $a, string &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/asset/fancybox/jquery.fancybox.min.css');
}

function fancybox_footer(App $a, string &$str)
{
	DI::page()->registerFooterScript(__DIR__ . '/asset/fancybox/jquery.fancybox.min.js');
	DI::page()->registerFooterScript(__DIR__ . '/asset/fancybox/fancybox.config.js');
}

function fancybox_render(App $a, array &$b)
{
	$matches = [];
	$pattern = '#<div class="body-attach">.*?</div>#s';
	$gallery = 'gallery';
	if (array_key_exists('item', $b)) {
		$item = $b['item'];
		if (array_key_exists('uri-id', $item)) {
			$gallery = $gallery . '-' . $item['uri-id'];
		}
	}
	$html = $b['html'];
	while (preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
		if (is_array($matches)) {
			$matches = $matches[0];
		}
		$part     = $matches[0];
		$replaced = str_replace('<a href', '<a data-fancybox="' . $gallery . '" href', $part);
		$replaced = str_replace('<div class="body-attach"', '<div class="body-attach done"', $replaced);
		$html     = str_replace($part, $replaced, $html);
	}
	$html      = str_replace('class="body-attach done"', 'class="body-attach"', $html);
	$b['html'] = $html;
}