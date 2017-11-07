<?php
/**
 * Name: Current Weather 
 * Description: Shows current weather conditions for user's location on their network page.
 * Version: 1.1
 * Author: Tony Baldwin <http://friendica.tonybaldwin.info/u/t0ny>
 * Author: Fabio Comuni <http://kirkgroup.com/u/fabrixxm>
 * Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
 *
 */

require_once('include/network.php');
require_once("mod/proxy.php");
require_once('include/text.php');

use Friendica\Core\Config;
use Friendica\Core\PConfig;

//  get the weather data from OpenWeatherMap
function getWeather( $loc, $units='metric', $lang='en', $appid='', $cachetime=0) {
    $url = "http://api.openweathermap.org/data/2.5/weather?q=".$loc."&appid=".$appid."&lang=".$lang."&units=".$units."&mode=xml";
    $cached = Cache::get('curweather'.md5($url));
    $now = new DateTime();
    if (!is_null($cached)) {
	$cdate = PConfig::get(local_user(), 'curweather', 'last');
	$cached = unserialize($cached);
	if ($cdate + $cachetime > $now->getTimestamp()) {
	    return $cached;
	}
    }
    try {
    	$res = new SimpleXMLElement(fetch_url($url));
    } catch (Exception $e) {
	info(t('Error fetching weather data.\nError was: '.$e->getMessage()));
	return false;
    }
    if ((string)$res->temperature['unit']==='metric') {
	$tunit = '°C';
	$wunit = 'm/s';
    } else {
	$tunit = '°F';
	$wunit = 'mph';
    }
    if ( trim((string)$res->weather['value']) == trim((string)$res->clouds['name']) ) {
	$desc = (string)$res->clouds['name'];
    } else {
	$desc = (string)$res->weather['value'].', '.(string)$res->clouds['name'];
    }
    $r = array(
	'city'=> (string) $res->city['name'][0],
	'country' => (string) $res->city->country[0],
	'lat' => (string) $res->city->coord['lat'],
	'lon' => (string) $res->city->coord['lon'],
	'temperature' => (string) $res->temperature['value'][0].$tunit,
	'pressure' => (string) $res->pressure['value'].(string)$res->pressure['unit'],
	'humidity' => (string) $res->humidity['value'].(string)$res->humidity['unit'],
	'descripion' => $desc,
	'wind' => (string)$res->wind->speed['name'].' ('.(string)$res->wind->speed['value'].$wunit.')',
	'update' => (string)$res->lastupdate['value'],
	'icon' => (string)$res->weather['icon']
    );
    PConfig::set(local_user(), 'curweather', 'last', $now->getTimestamp());
    Cache::set('curweather'.md5($url), serialize($r), CACHE_HOUR);
    return $r;
}

function curweather_install() {
	register_hook('network_mod_init', 'addon/curweather/curweather.php', 'curweather_network_mod_init');
	register_hook('plugin_settings', 'addon/curweather/curweather.php', 'curweather_plugin_settings');
	register_hook('plugin_settings_post', 'addon/curweather/curweather.php', 'curweather_plugin_settings_post');
}

function curweather_uninstall() {
	unregister_hook('network_mod_init', 'addon/curweather/curweather.php', 'curweather_network_mod_init');
	unregister_hook('plugin_settings', 'addon/curweather/curweather.php', 'curweather_plugin_settings');
	unregister_hook('plugin_settings_post', 'addon/curweather/curweather.php', 'curweather_plugin_settings_post');
}

function curweather_network_mod_init(&$fk_app,&$b) {

    if(! intval(PConfig::get(local_user(),'curweather','curweather_enable')))
        return;

    $fk_app->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $fk_app->get_baseurl() . '/addon/curweather/curweather.css' . '" media="all" />' . "\r\n";

    // $rpt value is needed for location
    // $lang will be taken from the browser session to honour user settings
    // TODO $lang does not work if the default settings are used
    //      and not all response strings are translated
    // $units can be set in the settings by the user
    // $appid is configured by the admin in the admin panel
    // those parameters will be used to get: cloud status, temperature, preassure
    // and relative humidity for display, also the relevent area of the map is
    // linked from lat/log of the reply of OWMp
    $rpt = PConfig::get(local_user(), 'curweather', 'curweather_loc');


    //  set the language to the browsers language and use metric units
    $lang = $_SESSION['language'];
    $units = PConfig::get( local_user(), 'curweather', 'curweather_units');
    $appid = Config::get('curweather','appid');
    $cachetime = intval(Config::get('curweather','cachetime'));
    if ($units==="")
	$units = 'metric';
    $ok = true;

    $res = getWeather($rpt, $units, $lang, $appid, $cachetime);
    if ($res===false)
	$ok = false;

    if ($ok) {
	$t = get_markup_template("widget.tpl", "addon/curweather/" );
	$curweather = replace_macros ($t, array(
	    '$title' => t("Current Weather"),
	    '$icon' => proxy_url('http://openweathermap.org/img/w/'.$res['icon'].'.png'),
	    '$city' => $res['city'],
	    '$lon' => $res['lon'],
	    '$lat' => $res['lat'],
	    '$description' => $res['descripion'],
	    '$temp' => $res['temperature'],
	    '$relhumidity' => array('caption'=>t('Relative Humidity'), 'val'=>$res['humidity']),
	    '$pressure' => array('caption'=>t('Pressure'), 'val'=>$res['pressure']),
	    '$wind' => array('caption'=>t('Wind'), 'val'=> $res['wind']),
	    '$lastupdate' => t('Last Updated').': '.$res['update'].'UTC',
	    '$databy' =>  t('Data by'),
	    '$showonmap' => t('Show on map')
	));
    } else {
	$t = get_markup_template('widget-error.tpl', 'addon/curweather/');
	$curweather = replace_macros( $t, array(
	    '$problem' => t('There was a problem accessing the weather data. But have a look'),
	    '$rpt' => $rpt,
	    '$atOWM' => t('at OpenWeatherMap')
	));
    }

    $fk_app->page['aside'] = $curweather.$fk_app->page['aside'];

}


function curweather_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'curweather-settings-submit')))
		return;
	PConfig::set(local_user(),'curweather','curweather_loc',trim($_POST['curweather_loc']));
	PConfig::set(local_user(),'curweather','curweather_enable',intval($_POST['curweather_enable']));
	PConfig::set(local_user(),'curweather','curweather_units',trim($_POST['curweather_units']));

	info( t('Current Weather settings updated.') . EOL);
}


function curweather_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Get the current state of our config variable */

	$curweather_loc = PConfig::get(local_user(), 'curweather', 'curweather_loc');
	$curweather_units = PConfig::get(local_user(), 'curweather', 'curweather_units');
	$appid = Config::get('curweather','appid');
	if ($appid=="") { 
		$noappidtext = t('No APPID found, please contact your admin to obtain one.');
	} else {
	    $noappidtext = '';
	}
	$enable = intval(PConfig::get(local_user(),'curweather','curweather_enable'));
	$enable_checked = (($enable) ? ' checked="checked" ' : '');
	
	// load template and replace the macros
	$t = get_markup_template("settings.tpl", "addon/curweather/" );
	$s = replace_macros ($t, array(
    		'$submit' => t('Save Settings'),	    
		'$header' => t('Current Weather').' '.t('Settings'),
		'$noappidtext' => $noappidtext,
		'$info' => t('Enter either the name of your location or the zip code.'),
		'$curweather_loc' => array( 'curweather_loc', t('Your Location'), $curweather_loc, t('Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.') ),
		'$curweather_units' => array( 'curweather_units', t('Units'), $curweather_units, t('select if the temperature should be displayed in &deg;C or &deg;F'), array('metric'=>'°C', 'imperial'=>'°F')),
		'$enabled' => array( 'curweather_enable', t('Show weather data'), $enable, '')
	    ));
	return;

}
// Config stuff for the admin panel to let the admin of the node set a APPID
// for accessing the API of openweathermap
function curweather_plugin_admin_post (&$a) {
	if(! is_site_admin())
	    return;
	if ($_POST['curweather-submit']) {
	    Config::set('curweather','appid',trim($_POST['appid']));
	    Config::set('curweather','cachetime',trim($_POST['cachetime']));
	    info( t('Curweather settings saved.'.EOL));
	}
}
function curweather_plugin_admin (&$a, &$o) {
    if(! is_site_admin())
	    return;
    $appid = Config::get('curweather','appid');
    $cachetime = Config::get('curweather','cachetime');
    $t = get_markup_template("admin.tpl", "addon/curweather/" );
    $o = replace_macros ($t, array(
	'$submit' => t('Save Settings'),
	'$cachetime' => array('cachetime', t('Caching Interval'), $cachetime, t('For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'), array('0'=>t('no cache'), '300'=>'5 '.t('minutes'), '900'=>'15 '.t('minutes'), '1800'=>'30 '.t('minutes'), '3600'=>'60 '.t('minutes'))),
	'$appid' => array('appid', t('Your APPID'), $appid, t('Your API key provided by OpenWeatherMap'))
    ));
}
