<?php
/**
 * Name: Showmore Dynamic
 * Description: Dynamically limits height of posts
 * Version: 1.0
 * Author: Christian Wiwie
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function showmore_dyn_install() {
	Hook::register('head'  , __FILE__, 'showmore_dyn_head');
	Hook::register('footer', __FILE__, 'showmore_dyn_footer');
}

function showmore_dyn_uninstall()
{
	Hook::unregister('head'  , __FILE__, 'showmore_dyn_head');
	Hook::unregister('footer', __FILE__, 'showmore_dyn_footer');
}

function showmore_dyn_head(App $a, &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/showmore_dyn.css');
}

function showmore_dyn_footer(App $a, &$b)
{
	DI::page()->registerFooterScript(__DIR__ . '/showmore_dyn.js');
}

