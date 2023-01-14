<?php
/*
 * Name: nitter
 * Description: Replaces links to twitter.com to a nitter server in all displays of postings on a node.
 * Version: 2.0
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
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function nitter_install()
{
	Hook::register('prepare_body_final', 'addon/nitter/nitter.php', 'nitter_render');
}

/* Handle the send data from the admin settings
 */
function nitter_addon_admin_post()
{
	DI::config()->set('nitter', 'server', rtrim(trim($_POST['nitterserver']), '/'));
}

/* Hook into the admin settings to let the admin choose a
 * nitter server to use for the replacement.
 */
function nitter_addon_admin(string &$o)
{
	$nitterserver = DI::config()->get('nitter', 'server');
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/nitter/');
	$o = Renderer::replaceMacros($t, [
		'$settingdescription' => DI::l10n()->t('Which nitter server shall be used for the replacements in the post bodies? Use the URL with servername and protocol.  See %s for a list of available public Nitter servers.', 'https://github.com/zedeus/nitter/wiki/Instances'),
		'$nitterserver' => ['nitterserver', DI::l10n()->t('Nitter server'), $nitterserver, 'https://example.com'], 
		'$submit' => DI::l10n()->t('Save Settings'),
	]);
}

/*
 *  replace "twitter.com" with "nitter.net"
 */
function nitter_render(array &$b)
{
	// this needs to be a system setting
	$replaced = false;
	$nitter = DI::config()->get('nitter', 'server', 'https://nitter.net');
	if (strstr($b['html'], 'https://mobile.twitter.com')) {
		$b['html'] = str_replace('https://mobile.twitter.com', $nitter, $b['html']);
		$replaced = true;
	}
	if (strstr($b['html'], 'https://twitter.com')) {
		$b['html'] = str_replace('https://twitter.com', $nitter, $b['html']);
		$replaced = true;
	}
	if ($replaced) {
		$b['html'] .= '<hr><p><small>' . DI::l10n()->t('(Nitter addon enabled: Twitter links via %s)', $nitter) . '</small></p>';
	}
}
