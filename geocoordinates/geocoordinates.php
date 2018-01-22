<?php
/**
 * Name: Geocoordinates
 * Description: Use the OpenCage Geocoder http://geocoder.opencagedata.com to resolve nearest populated location for given latitude, longitude. Derived from "geonames"
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */
use Friendica\Core\Addon;
use Friendica\Core\Cache;
use Friendica\Core\Config;
use Friendica\Core\L10n;

function geocoordinates_install()
{
	Addon::registerHook('post_local', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
	Addon::registerHook('post_remote', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
}


function geocoordinates_uninstall()
{
	Addon::unregisterHook('post_local',    'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
	Addon::unregisterHook('post_remote',    'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
}

function geocoordinates_resolve_item(&$item)
{
	if((!$item["coord"]) || ($item["location"]))
		return;

	$key = Config::get("geocoordinates", "api_key");
	if ($key == "")
		return;

	$language = Config::get("geocoordinates", "language");
	if ($language == "")
		$language = "de";

	$coords = explode(' ',$item["coord"]);

	if (count($coords) < 2)
		return;

	$coords[0] = round($coords[0], 5);
	$coords[1] = round($coords[1], 5);

	$result = Cache::get("geocoordinates:".$language.":".$coords[0]."-".$coords[1]);
	if (!is_null($result)) {
		$item["location"] = $result;
		return;
	}

	$s = fetch_url("https://api.opencagedata.com/geocode/v1/json?q=".$coords[0].",".$coords[1]."&key=".$key."&language=".$language);

	if (!$s) {
		logger("API could not be queried", LOGGER_DEBUG);
		return;
	}

	$data = json_decode($s);

	if ($data->status->code != "200") {
		logger("API returned error ".$data->status->code." ".$data->status->message, LOGGER_DEBUG);
		return;
	}

	if (($data->total_results == 0) || (count($data->results) == 0)) {
		logger("No results found for coordinates ".$item["coord"], LOGGER_DEBUG);
		return;
	}

	$item["location"] = $data->results[0]->formatted;

	logger("Got location for coordinates ".$coords[0]."-".$coords[1].": ".$item["location"], LOGGER_DEBUG);

	if ($item["location"] != "")
		Cache::set("geocoordinates:".$language.":".$coords[0]."-".$coords[1], $item["location"]);
}

function geocoordinates_post_hook($a, &$item)
{
	geocoordinates_resolve_item($item);
}

function geocoordinates_addon_admin(&$a, &$o)
{

	$t = get_markup_template("admin.tpl", "addon/geocoordinates/");

	$o = replace_macros($t, [
		'$submit' => L10n::t('Save Settings'),
		'$api_key' => ['api_key', L10n::t('API Key'), Config::get('geocoordinates', 'api_key'), ''],
		'$language' => ['language', L10n::t('Language code (IETF format)'), Config::get('geocoordinates', 'language'), ''],
	]);
}

function geocoordinates_addon_admin_post(&$a)
{
	$api_key  = ((x($_POST, 'api_key')) ? notags(trim($_POST['api_key']))   : '');
	Config::set('geocoordinates', 'api_key', $api_key);

	$language  = ((x($_POST, 'language')) ? notags(trim($_POST['language']))   : '');
	Config::set('geocoordinates', 'language', $language);
	info(L10n::t('Settings updated.'). EOL);
}
