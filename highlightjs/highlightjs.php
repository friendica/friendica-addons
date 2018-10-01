<?php
/**
 * Name: Syntax Highlighting
 * Description: Highlights syntax of code blocks with highlight.js
 * Version: 1.0
 * Author: Hypolite Petovan <hypolite@mrpetovan.com>
 */

use Friendica\App;
use Friendica\Core\Addon;

function highlightjs_install()
{
	Addon::registerHook('head'  , __FILE__, 'highlightjs_head');
	Addon::registerHook('footer', __FILE__, 'highlightjs_footer');
}

function highlightjs_uninstall()
{
	Addon::unregisterHook('head'  , __FILE__, 'highlightjs_head');
	Addon::unregisterHook('footer', __FILE__, 'highlightjs_footer');
}

function highlightjs_head(App $a, &$b)
{
	if ($a->getCurrentTheme() == 'frio') {
		$style = 'bootstrap';
	} else {
		$style = 'default';
	}

	$a->registerStylesheet(__DIR__ . '/asset/styles/' . $style . '.css');
}

function highlightjs_footer(App $a, &$b)
{
	$a->registerFooterScript(__DIR__ . '/asset/highlight.pack.js');
	$a->registerFooterScript(__DIR__ . '/highlightjs.js');
}
