<?php
/**
 * Name: Geocoordinates
 * Description: Use the OpenCage Geocoder http://geocoder.opencagedata.com to resolve nearest populated location for given latitude, longitude. Derived from "geonames"
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function geocoordinates_install()
{
	Hook::register('post_local', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
	Hook::register('post_remote', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
}

function geocoordinates_resolve_item(&$item)
{
	if((!$item["coord"]) || ($item["location"]))
		return;

	$key = DI::config()->get("geocoordinates", "api_key");
	if ($key == "")
		return;

	$language = DI::config()->get("geocoordinates", "language");
	if ($language == "")
		$language = "de";

	$coords = explode(' ',$item["coord"]);

	if (count($coords) < 2)
		return;

	$coords[0] = round($coords[0], 5);
	$coords[1] = round($coords[1], 5);

	$result = DI::cache()->get("geocoordinates:".$language.":".$coords[0]."-".$coords[1]);
	if (!is_null($result)) {
		$item["location"] = $result;
		return;
	}

	$s = DI::httpClient()->fetch("https://api.opencagedata.com/geocode/v1/json?q=" . $coords[0] . "," . $coords[1] . "&key=" . $key . "&language=" . $language);

	if (!$s) {
		Logger::info("API could not be queried");
		return;
	}

	$data = json_decode($s);

	if ($data->status->code != "200") {
		Logger::info("API returned error ".$data->status->code." ".$data->status->message);
		return;
	}

	if (($data->total_results == 0) || (count($data->results) == 0)) {
		Logger::info("No results found for coordinates ".$item["coord"]);
		return;
	}

	$item["location"] = $data->results[0]->formatted;

	Logger::info("Got location for coordinates ".$coords[0]."-".$coords[1].": ".$item["location"]);

	if ($item["location"] != "")
		DI::cache()->set("geocoordinates:".$language.":".$coords[0]."-".$coords[1], $item["location"]);
}

function geocoordinates_post_hook(App $a, &$item)
{
	geocoordinates_resolve_item($item);
}

function geocoordinates_addon_admin(App $a, &$o)
{

	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/geocoordinates/");

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$api_key' => ['api_key', DI::l10n()->t('API Key'), DI::config()->get('geocoordinates', 'api_key'), ''],
		'$language' => ['language', DI::l10n()->t('Language code (IETF format)'), DI::config()->get('geocoordinates', 'language'), ''],
	]);
}

function geocoordinates_addon_admin_post(App $a)
{
	$api_key  = trim($_POST['api_key'] ?? '');
	DI::config()->set('geocoordinates', 'api_key', $api_key);

	$language  = trim($_POST['language'] ?? '');
	DI::config()->set('geocoordinates', 'language', $language);
}
