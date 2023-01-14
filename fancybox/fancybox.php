<?php
/**
 * Name: Fancybox
 * Description: Open media attachments of posts into a fancybox overlay.
 * Version: 1.05
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

function fancybox_head(string &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/asset/fancybox/jquery.fancybox.min.css');
}

function fancybox_footer(string &$str)
{
	DI::page()->registerFooterScript(__DIR__ . '/asset/fancybox/jquery.fancybox.min.js');
	DI::page()->registerFooterScript(__DIR__ . '/asset/fancybox/fancybox.config.js');
}

function fancybox_render(array &$b){
	$gallery = 'gallery-' . $b['item']['uri-id'] ?? random_int(1000000, 10000000);

	// performWithEscapedBlocks escapes block defined with 2nd par pattern that won't be processed.
	// We don't want to touch images in class="type-link":
	$b['html'] = \Friendica\Util\Strings::performWithEscapedBlocks(
		$b['html'],
		'#<div class="type-link">.*?</div>#s',
		function ($text) use ($gallery) {
			// This processes images inlined in posts
			// Frio / Vier hooks für lightbox are un-hooked in fancybox-config.js. So this works for them, too!
			//if (!in_array(DI::app()->getCurrentTheme(),['vier','frio']))
			$text = preg_replace(
				'#<a[^>]*href="([^"]*)"[^>]*>(<img[^>]*src="[^"]*"[^>]*>)</a>#',
				'<a data-fancybox="' . $gallery . '" href="$1">$2</a>',
				$text);

			// Local content images attached:
			$text = preg_replace_callback(
				'#<div class="(body-attach|imagegrid-column)">.*?</div>#s',
				function ($matches) use ($gallery) {
					return str_replace('<a href', '<a data-fancybox="' . $gallery . '" href', $matches[0]);
				},
				$text
			);

			return $text;
		}
	);
}
