<?php
/**
 * Name: Syntax Highlighting
 * Description: Highlights syntax of code blocks with highlight.js
 * Version: 1.0
 * Author: Hypolite Petovan <hypolite@mrpetovan.com>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Registry\App as A;

function highlightjs_install()
{
	Hook::register('head'  , __FILE__, 'highlightjs_head');
	Hook::register('footer', __FILE__, 'highlightjs_footer');
}

function highlightjs_uninstall()
{
	Hook::unregister('head'  , __FILE__, 'highlightjs_head');
	Hook::unregister('footer', __FILE__, 'highlightjs_footer');
}

function highlightjs_head(App $a, &$b)
{
	if ($a->getCurrentTheme() == 'frio') {
		$style = 'bootstrap';
	} else {
		$style = 'default';
	}

	A::page()->registerStylesheet(__DIR__ . '/asset/styles/' . $style . '.css');
}

function highlightjs_footer(App $a, &$b)
{
	A::page()->registerFooterScript(__DIR__ . '/asset/highlight.pack.js');
	A::page()->registerFooterScript(__DIR__ . '/highlightjs.js');
}
