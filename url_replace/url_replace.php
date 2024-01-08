<?php
/**
 * Name: URL Replace
 * Description: Replaces occurrences of specified URLs with the address of alternative servers in all displays of postings on a node.
 * Version: 1.0
 * Author: Dr. Tobias Quathamer <https://social.anoxinon.de/@toddy>
 * Maintainer: Dr. Tobias Quathamer <https://social.anoxinon.de/@toddy>
 */
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function url_replace_install()
{
	Hook::register('prepare_body_final', 'addon/url_replace/url_replace.php', 'url_replace_render');
}

/**
 * Handle sent data from admin settings
 */
function url_replace_addon_admin_post()
{
	DI::config()->set('url_replace', 'nitter_server', rtrim(trim($_POST['nitter_server']), '/'));
	DI::config()->set('url_replace', 'invidious_server', rtrim(trim($_POST['invidious_server']), '/'));
	// Convert twelvefeet_sites into an array before setting the new value
	$twelvefeet_sites = explode(PHP_EOL, $_POST['twelvefeet_sites']);
	// Normalize URLs by using lower case, removing a trailing slash and whitespace
	$twelvefeet_sites = array_map(fn ($value): string => rtrim(trim(strtolower($value)), '/'), $twelvefeet_sites);
	// Do not store empty lines or duplicates
	$twelvefeet_sites = array_filter($twelvefeet_sites, fn ($value): bool => !empty($value));
	$twelvefeet_sites = array_unique($twelvefeet_sites);
	// Ensure a protocol and default to HTTPS
	$twelvefeet_sites = array_map(
		fn ($value): string => substr($value, 0, 4) !== 'http' ? 'https://'.$value : $value,
		$twelvefeet_sites
	);
	asort($twelvefeet_sites);
	DI::config()->set('url_replace', 'twelvefeet_sites', $twelvefeet_sites);
}

/**
 * Hook into admin settings to enable choosing a different server
 * for twitter, youtube, and news sites.
 */
function url_replace_addon_admin(string &$o)
{
	$nitter_server    = DI::config()->get('url_replace', 'nitter_server');
	$invidious_server = DI::config()->get('url_replace', 'invidious_server');
	$twelvefeet_sites = implode(PHP_EOL, DI::config()->get('url_replace', 'twelvefeet_sites'));
	$t                = Renderer::getMarkupTemplate('admin.tpl', 'addon/url_replace/');
	$o                = Renderer::replaceMacros($t, [
		'$nitter_server' => [
			'nitter_server',
			DI::l10n()->t('Nitter server'),
			$nitter_server,
			DI::l10n()->t('Specify the URL with protocol. The default is https://nitter.net.'),
			null,
			'placeholder="https://nitter.net"',
		],
		'$invidious_server' => [
			'invidious_server',
			DI::l10n()->t('Invidious server'),
			$invidious_server,
			DI::l10n()->t('Specify the URL with protocol. The default is https://yewtu.be.'),
			null,
			'placeholder="https://yewtu.be"',
		],
		'$twelvefeet_sites' => [
			'twelvefeet_sites',
			DI::l10n()->t('Sites which are accessed through 12ft.io'),
			$twelvefeet_sites,
			DI::l10n()->t('Specify the URLs with protocol, one per line.'),
			null,
			'rows="6"'
		],
		'$submit' => DI::l10n()->t('Save settings'),
	]);
}

/**
 * Replace proprietary URLs with their specified counterpart
 */
function url_replace_render(array &$b)
{
	$replaced = false;

	$nitter_server = DI::config()->get('url_replace', 'nitter_server');
	if (empty($nitter_server)) {
		$nitter_server = 'https://nitter.net';
	}

	$invidious_server = DI::config()->get('url_replace', 'invidious_server');
	if (empty($invidious_server)) {
		$invidious_server = 'https://yewtu.be';
	}

	// Handle some of twitter and youtube
	$replacements = [
		'https://mobile.twitter.com' => $nitter_server,
		'https://twitter.com'        => $nitter_server,
		'https://mobile.x.com'       => $nitter_server,
		'https://x.com'              => $nitter_server,
		'https://www.youtube.com'    => $invidious_server,
		'https://youtube.com'        => $invidious_server,
		'https://m.youtube.com'      => $invidious_server,
		'https://youtu.be'           => $invidious_server,
	];
	foreach ($replacements as $server => $replacement) {
		if (strpos($b['html'], $server) !== false) {
			$b['html'] = str_replace($server, $replacement, $b['html']);
			$replaced  = true;
		}
	}

	$twelvefeet_sites = DI::config()->get('url_replace', 'twelvefeet_sites');
	if (empty($twelvefeet_sites)) {
		$twelvefeet_sites = [];
	}
	foreach ($twelvefeet_sites as $twelvefeet_site) {
		if (strpos($b['html'], $twelvefeet_site) !== false) {
			$b['html'] = str_replace($twelvefeet_site, 'https://12ft.io/'.$twelvefeet_site, $b['html']);
			$replaced  = true;
		}
	}


	if ($replaced) {
		$b['html'] .= '<hr><p><small>' . DI::l10n()->t('(URL replace addon enabled for X, YouTube and some news sites.)') . '</small></p>';
	}
}
