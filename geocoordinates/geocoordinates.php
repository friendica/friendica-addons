<?php
/**
 * Name: Geocoordinates
 * Description: Use the OpenCage Geocoder http://geocoder.opencagedata.com to resolve nearest populated location for given latitude, longitude. Derived from "geonames"
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>.
 */
function geocoordinates_install()
{
    register_hook('post_local', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
    register_hook('post_remote', 'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
}

function geocoordinates_uninstall()
{
    unregister_hook('post_local',    'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
    unregister_hook('post_remote',    'addon/geocoordinates/geocoordinates.php', 'geocoordinates_post_hook');
}

function geocoordinates_resolve_item(&$item)
{
    if ((!$item['coord']) || ($item['location'])) {
        return;
    }

    $key = get_config('geocoordinates', 'api_key');
    if ($key == '') {
        return;
    }

    $language = get_config('geocoordinates', 'language');
    if ($language == '') {
        $language = 'de';
    }

    $coords = explode(' ', $item['coord']);

    if (count($coords) < 2) {
        return;
    }

    $coords[0] = round($coords[0], 5);
    $coords[1] = round($coords[1], 5);

    $result = Cache::get('geocoordinates:'.$language.':'.$coords[0].'-'.$coords[1]);
    if (!is_null($result)) {
        $item['location'] = $result;

        return;
    }

    $s = fetch_url('https://api.opencagedata.com/geocode/v1/json?q='.$coords[0].','.$coords[1].'&key='.$key.'&language='.$language);

    if (!$s) {
        logger('API could not be queried', LOGGER_DEBUG);

        return;
    }

    $data = json_decode($s);

    if ($data->status->code != '200') {
        logger('API returned error '.$data->status->code.' '.$data->status->message, LOGGER_DEBUG);

        return;
    }

    if (($data->total_results == 0) or (count($data->results) == 0)) {
        logger('No results found for coordinates '.$item['coord'], LOGGER_DEBUG);

        return;
    }

    $item['location'] = $data->results[0]->formatted;

    logger('Got location for coordinates '.$coords[0].'-'.$coords[1].': '.$item['location'], LOGGER_DEBUG);

    if ($item['location'] != '') {
        Cache::set('geocoordinates:'.$language.':'.$coords[0].'-'.$coords[1], $item['location']);
    }
}

function geocoordinates_post_hook($a, &$item)
{
    geocoordinates_resolve_item($item);
}

function geocoordinates_plugin_admin(&$a, &$o)
{
    $t = get_markup_template('admin.tpl', 'addon/geocoordinates/');

    $o = replace_macros($t, array(
        '$submit' => t('Save Settings'),
        '$api_key' => array('api_key', t('API Key'),  get_config('geocoordinates', 'api_key'), ''),
        '$language' => array('language', t('Language code (IETF format)'),  get_config('geocoordinates', 'language'), ''),
    ));
}

function geocoordinates_plugin_admin_post(&$a)
{
    $api_key = ((x($_POST, 'api_key')) ? notags(trim($_POST['api_key'])) : '');
    set_config('geocoordinates', 'api_key', $api_key);

    $language = ((x($_POST, 'language')) ? notags(trim($_POST['language'])) : '');
    set_config('geocoordinates', 'language', $language);
    info(t('Settings updated.').EOL);
}
