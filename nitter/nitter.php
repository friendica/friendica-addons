<?php
/*
 * Name: nitter
 * Description: Replaces links to twitter.com to a nitter server in all displays of postings on a node.
 * Version: 1.1
 * Author: Tobias Diekershoff <tobias@social.diekershoff.de>
 *
 * Copyright (c) 2020 Tobias Diekershoff
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and 
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 * NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Renderer;
use Friendica\DI;

function nitter_install()
{
	Addon::registerHook ('prepare_body_final', 'addon/nitter/nitter.php', 'nitter_render');
}

/* Handle the send data from the admin settings
 */
function nitter_addon_admin_post(App $a)
{
	$nitterserver = rtrim(trim($_POST['nitterserver']),'/');
	DI::config()->set('nitter', 'server', $nitterserver);
}

/* Hook into the admin settings to let the admin choose a
 * nitter server to use for the replacement.
 */
function nitter_addon_admin(App $a, &$o)
{
	$nitterserver = DI::config()->get('nitter', 'server');
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/nitter/');
	$o = Renderer::replaceMacros($t, [
		'$settingdescription' => DI::l10n()->t('Which nitter server shall be used for the replacements in the post bodies? Use the URL with servername and protocol.  See %s for a list of available public Nitter servers.', 'https://github.com/zedeus/nitter/wiki/Instances'),
		'$nitterserver' => ['nitterserver', DI::l10n()->t('Nitter server'), $nitterserver, 'http://example.com'], 
		'$submit' => DI::l10n()->t('Save Settings'),
	]);
}

/*
 *  replace "twitter.com" with "nitter.net"
 */
function nitter_render(&$a, &$o)
{
	// this needs to be a system setting
	$replaced = false;
	$nitter = DI::config()->get('nitter', 'server', 'https://nitter.net');
	if (strstr($o['html'], 'https://mobile.twitter.com')) {
		$o['html'] = str_replace('https://mobile.twitter.com', $nitter, $o['html']);
		$replaced = true;
	}
	if (strstr($o['html'], 'https://twitter.com')) {
		$o['html'] = str_replace('https://twitter.com', $nitter, $o['html']);
		$replaced = true;
	}
	if ($replaced) {
		$o['html'] .= '<hr><p>' . DI::l10n()->t('In an attempt to protect your privacy, links to Twitter in this posting were replaced by links to the Nitter instance at %s', $nitter) . '</p>';
	}
}
