<?php
/**
 * Name: Nominatim
 * Description: Use Nominatim from OpenStreetMap to resolve the location for the given latitude and longitude. Derived from "geocoordinates"
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function nominatim_install()
{
	Hook::register('post_local', 'addon/nominatim/nominatim.php', 'nominatim_post_hook');
	Hook::register('post_remote', 'addon/nominatim/nominatim.php', 'nominatim_post_hook');
}

function nominatim_resolve_item(&$item)
{
	if(empty($item['coord']) || !empty($item['location'])) {
		return;
	}

	$language = DI::config()->get('nominatim', 'language');
	if (empty($language)) {
		$language = 'en';
	}

	$coords = explode(' ',$item['coord']);

	if (count($coords) < 2) {
		return;
	}

	$coords[0] = round($coords[0], 5);
	$coords[1] = round($coords[1], 5);

	$result = DI::cache()->get('nominatim:' . $language . ':' . $coords[0] . '-' . $coords[1]);
	if (!is_null($result)) {
		$item['location'] = $result;
		return;
	}

	$s = DI::httpRequest()->fetch('https://nominatim.openstreetmap.org/reverse?lat=' . $coords[0] . '&lon=' . $coords[1] . '&format=json&addressdetails=0&accept-language=' . $language);
	if (empty($s)) {
		Logger::info('API could not be queried');
		return;
	}

	$data = json_decode($s, true);
	if (empty($data['display_name'])) {
		Logger::info('No results found for coordinates', ['coordinates' => $item['coord'], 'data' => $data]);
		return;
	}

	$item['location'] = $data['display_name'];

	Logger::info('Got location', ['lat' => $coords[0], 'long' => $coords[1], 'location' => $item['location']]);

	if (!empty($item['location'])) {
		DI::cache()->set('nominatim:' . $language . ':' . $coords[0] . '-' . $coords[1], $item['location']);
	}
}

function nominatim_post_hook($a, &$item)
{
	nominatim_resolve_item($item);
}

function nominatim_addon_admin(&$a, &$o)
{

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/nominatim/');

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$language' => ['language', DI::l10n()->t('Language code (IETF format)'), DI::config()->get('nominatim', 'language'), ''],
	]);
}

function nominatim_addon_admin_post(&$a)
{
	$language  = !empty($_POST['language']) ? trim($_POST['language']) : '';
	DI::config()->set('nominatim', 'language', $language);
}
