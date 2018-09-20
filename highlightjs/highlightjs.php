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
	Addon::registerHook('page_end', __FILE__, 'highlightjs_page_end');
}

function highlightjs_uninstall()
{
	Addon::unregisterHook('page_end', __FILE__, 'highlightjs_page_end');
}

function highlightjs_page_end(App $a, &$b)
{
	$basedir = $a->get_baseurl() . '/addon/highlightjs/asset';

	if ($a->getCurrentTheme() == 'frio') {
		$style = 'bootstrap';
	} else {
		$style = 'default';
	}

	$a->page['htmlhead'] .= <<< HTML

<link rel="stylesheet" href="{$basedir}/styles/{$style}.css">

HTML;

	$b .= <<< HTML

<script type="text/javascript" src="{$basedir}/highlight.pack.js"></script>
<script type="text/javascript">
	hljs.initHighlightingOnLoad();

	document.addEventListener('postprocess_liveupdate', function () {
		var blocks = document.querySelectorAll('pre code:not(.hljs)');
		Array.prototype.forEach.call(blocks, hljs.highlightBlock);
	});
</script>

HTML;
}
