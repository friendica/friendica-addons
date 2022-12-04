<?php
/**
 * Name: Fancybox
 * Description: Open media attachments of posts into a fancybox overlay.
 * Version: 1.03
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

function fancybox_render($a, array &$b){
	$gallery = 'gallery-' . $b['item']['uri-id'] ?? random_int(1000000, 10000000);

	// prevent urls in <div class="type-link"> to be replaced
	$b['html'] = preg_replace_callback(
		'#<div class="type-link">.*?</div>#s',
		function ($matches) use ($gallery) {
			return str_replace('<a href', '<a data-nofancybox="" href', $matches[0]);
		},
		$b['html']
	);

	// This processes images inlined in posts
	// Frio / Vier hooks für lightbox are un-hooked in fancybox-config.js. So this works for them, too!
	//if (!in_array($a->getCurrentTheme(),['vier','frio']))
	{
		// normal post inline linked images
		$b['html'] = preg_replace_callback(
			'#<a[^>]*href="([^"]*)"[^>]*>(<img[^>]*src="[^"]*"[^>]*>)</a>#',
			function ($matches)  use ($gallery) {
				// don't touch URLS marked as not "fancyable".. ;-)
				if (preg_match('#data-nofancybox#', $matches[0]))
				{
					return $matches[0];
				}
				return '<a data-fancybox="' . $gallery . '" href="'. $matches[1] .'">' . $matches[2] .'</a>';
			},
			$b['html']
		);
	}

	// Local content images attached:
	$b['html'] = preg_replace_callback(
		'#<div class="body-attach">.*?</div>#s',
		function ($matches) use ($gallery) {
			return str_replace('<a href', '<a data-fancybox="' . $gallery . '" href', $matches[0]);
		},
		$b['html']
	);
}
