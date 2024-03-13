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
	DI::config()->set('url_replace', 'nitter_server_enabled', !empty($_POST['nitter_server_enabled']));
	DI::config()->set('url_replace', 'nitter_server', rtrim(trim($_POST['nitter_server']), '/'));
	DI::config()->set('url_replace', 'invidious_server_enabled', !empty($_POST['invidious_server_enabled']));
	DI::config()->set('url_replace', 'invidious_server', rtrim(trim($_POST['invidious_server']), '/'));
	DI::config()->set('url_replace', 'proxigram_server_enabled', !empty($_POST['proxigram_server_enabled']));
	DI::config()->set('url_replace', 'proxigram_server', rtrim(trim($_POST['proxigram_server']), '/'));
	// Convert twelvefeet_sites into an array before setting the new value
	$twelvefeet_sites = explode(PHP_EOL, $_POST['twelvefeet_sites']);
	// Normalize URLs by using lower case, removing a trailing slash and whitespace
	$twelvefeet_sites = array_map(fn($value): string => rtrim(trim(strtolower($value)), '/'), $twelvefeet_sites);
	// Do not store empty lines or duplicates
	$twelvefeet_sites = array_filter($twelvefeet_sites, fn($value): bool => !empty($value));
	$twelvefeet_sites = array_unique($twelvefeet_sites);
	// Ensure a protocol and default to HTTPS
	$twelvefeet_sites = array_map(
		fn($value): string => substr($value, 0, 4) !== 'http' ? 'https://' . $value : $value,
		$twelvefeet_sites
	);
	asort($twelvefeet_sites);
	DI::config()->set('url_replace', 'twelvefeet_sites', $twelvefeet_sites);
}

/**
 * Hook into admin settings to enable choosing a different server
 * for twitter, youtube, instagram, and news sites.
 */
function url_replace_addon_admin(string &$o)
{
	$nitter_server_enabled    = DI::config()->get('url_replace', 'nitter_server_enabled', true);
	$nitter_server    = DI::config()->get('url_replace', 'nitter_server');
	$invidious_server_enabled = DI::config()->get('url_replace', 'invidious_server_enabled', true);
	$invidious_server = DI::config()->get('url_replace', 'invidious_server');
	$proxigram_server_enabled = DI::config()->get('url_replace', 'proxigram_server_enabled', true);
	$proxigram_server = DI::config()->get('url_replace', 'proxigram_server');
	$twelvefeet_sites = implode(PHP_EOL, DI::config()->get('url_replace', 'twelvefeet_sites') ?? []);

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/url_replace/');
	$o = Renderer::replaceMacros($t, [
		'$nitter_server_enabled' => [
			'nitter_server_enabled',
			DI::l10n()->t('Replace links to X.'),
			$nitter_server_enabled,
		],
		'$nitter_server' => [
			'nitter_server',
			DI::l10n()->t('Nitter server'),
			$nitter_server,
			DI::l10n()->t('Specify the URL with protocol. The default is https://nitter.net.'),
			null,
			'placeholder="https://nitter.net"',
		],
		'$invidious_server_enabled' => [
			'invidious_server_enabled',
			DI::l10n()->t('Replace links to YouTube.'),
			$invidious_server_enabled,
		],
		'$invidious_server' => [
			'invidious_server',
			DI::l10n()->t('Invidious server'),
			$invidious_server,
			DI::l10n()->t('Specify the URL with protocol. The default is https://yewtu.be.'),
			null,
			'placeholder="https://yewtu.be"',
		],
		'$proxigram_server_enabled' => [
			'proxigram_server_enabled',
			DI::l10n()->t('Replace links to Instagram.'),
			$proxigram_server_enabled,
		],
		'$proxigram_server' => [
			'proxigram_server',
			DI::l10n()->t('Proxigram server'),
			$proxigram_server,
			DI::l10n()->t('Specify the URL with protocol. The default is https://proxigram.lunar.icu.'),
			null,
			'placeholder="https://proxigram.lunar.icu"',
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
	$replacements = [];

	$nitter_server = DI::config()->get('url_replace', 'nitter_server');
	if (empty($nitter_server)) {
		$nitter_server = 'https://nitter.net';
	}
	$nitter_server_enabled = DI::config()->get('url_replace', 'nitter_server_enabled', true);
	if ($nitter_server_enabled) {
		$replacements = array_merge($replacements, [
			'https://mobile.twitter.com' => $nitter_server,
			'https://twitter.com'        => $nitter_server,
			'https://mobile.x.com'       => $nitter_server,
			'https://x.com'              => $nitter_server,
		]);
	}

	$invidious_server = DI::config()->get('url_replace', 'invidious_server');
	if (empty($invidious_server)) {
		$invidious_server = 'https://yewtu.be';
	}
	$invidious_server_enabled = DI::config()->get('url_replace', 'invidious_server_enabled', true);
	if ($invidious_server_enabled) {
		$replacements = array_merge($replacements, [
		'https://www.youtube.com'    => $invidious_server,
		'https://youtube.com'        => $invidious_server,
		'https://m.youtube.com'      => $invidious_server,
		'https://youtu.be'           => $invidious_server,
		]);
	}

	$proxigram_server = DI::config()->get('url_replace', 'proxigram_server');
	if (empty($proxigram_server)) {
		$proxigram_server = 'https://proxigram.lunar.icu';
	}
	$proxigram_server_enabled = DI::config()->get('url_replace', 'proxigram_server_enabled', true);
	if ($proxigram_server_enabled) {
		$replacements = array_merge($replacements, [
		'https://www.instagram.com'  => $proxigram_server,
		'https://instagram.com'      => $proxigram_server,
		'https://ig.me'              => $proxigram_server,
		]);
	}

	foreach ($replacements as $server => $replacement) {
		if (strpos($b['html'], $server) !== false) {
			$b['html'] = str_replace($server, $replacement, $b['html']);
			$replaced  = true;
		}
	}

	$twelvefeet_sites = DI::config()->get('url_replace', 'twelvefeet_sites') ?? [];
	foreach ($twelvefeet_sites as $twelvefeet_site) {
		if (strpos($b['html'], $twelvefeet_site) !== false) {
			$b['html'] = str_replace($twelvefeet_site, 'https://12ft.io/' . $twelvefeet_site, $b['html']);
			$replaced  = true;
		}
	}


	if ($replaced) {
		$b['html'] .= '<hr><p><small>' . DI::l10n()->t('(URL replace addon enabled for X, YouTube, Instagram and some news sites.)') . '</small></p>';
	}
}
