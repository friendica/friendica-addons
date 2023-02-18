<?php
/**
 * Name: Current Weather
 * Description: Shows current weather conditions for user's location on their network page.
 * Version: 1.2
 * Author: Tony Baldwin <http://friendica.tonybaldwin.info/u/t0ny>
 * Author: Fabio Comuni <http://kirkgroup.com/u/fabrixxm>
 * Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
 *
 */

use Friendica\App;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Proxy as ProxyUtils;

function curweather_install()
{
	Hook::register('network_mod_init'   , 'addon/curweather/curweather.php', 'curweather_network_mod_init');
	Hook::register('addon_settings'     , 'addon/curweather/curweather.php', 'curweather_addon_settings');
	Hook::register('addon_settings_post', 'addon/curweather/curweather.php', 'curweather_addon_settings_post');
}

//  get the weather data from OpenWeatherMap
function getWeather($loc, $units = 'metric', $lang = 'en', $appid = '', $cachetime = 0)
{
	$url = "http://api.openweathermap.org/data/2.5/weather?q=" . $loc . "&appid=" . $appid . "&lang=" . $lang . "&units=" . $units . "&mode=xml";
	$cached = DI::cache()->get('curweather'.md5($url));
	$now = new DateTime();

	if (!is_null($cached)) {
		$cdate = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'last');
		$cached = unserialize($cached);

		if ($cdate + $cachetime > $now->getTimestamp()) {
			return $cached;
		}
	}

	try {
		$res = new SimpleXMLElement(DI::httpClient()->fetch($url));
	} catch (Exception $e) {
		if (empty($_SESSION['curweather_notice_shown'])) {
			DI::sysmsg()->addNotice(DI::l10n()->t('Error fetching weather data. Error was: ' . $e->getMessage()));
			$_SESSION['curweather_notice_shown'] = true;
		}

		return false;
	}

	unset($_SESSION['curweather_notice_shown']);

	if (in_array((string) $res->temperature['unit'], ['celsius', 'metric'])) {
		$tunit = '°C';
		$wunit = 'm/s';
	} else {
		$tunit = '°F';
		$wunit = 'mph';
	}

	if (trim((string) $res->weather['value']) == trim((string) $res->clouds['name'])) {
		$desc = (string) $res->clouds['name'];
	} else {
		$desc = (string) $res->weather['value'] . ', ' . (string) $res->clouds['name'];
	}

	$r = [
		'city'        => (string) $res->city['name'][0],
		'country'     => (string) $res->city->country[0],
		'lat'         => (string) $res->city->coord['lat'],
		'lon'         => (string) $res->city->coord['lon'],
		'temperature' => (string) $res->temperature['value'][0].$tunit,
		'pressure'    => (string) $res->pressure['value'] . (string) $res->pressure['unit'],
		'humidity'    => (string) $res->humidity['value'] . (string) $res->humidity['unit'],
		'descripion'  => $desc,
		'wind'        => (string) $res->wind->speed['name'] . ' (' . (string) $res->wind->speed['value'] . $wunit . ')',
		'update'      => (string) $res->lastupdate['value'],
		'icon'        => (string) $res->weather['icon'],
	];

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'curweather', 'last', $now->getTimestamp());
	DI::cache()->set('curweather'.md5($url), serialize($r), Duration::HOUR);

	return $r;
}

function curweather_network_mod_init(string &$body)
{
	if (!intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_enable'))) {
		return;
	}

	DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl() . '/addon/curweather/curweather.css' . '" media="all" />' . "\r\n";

	// $rpt value is needed for location
	// $lang will be taken from the browser session to honour user settings
	// TODO $lang does not work if the default settings are used
	//      and not all response strings are translated
	// $units can be set in the settings by the user
	// $appid is configured by the admin in the admin panel
	// those parameters will be used to get: cloud status, temperature, preassure
	// and relative humidity for display, also the relevent area of the map is
	// linked from lat/log of the reply of OWMp
	$rpt = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_loc');

	// Set the language to the browsers language or default and use metric units
	$lang  = DI::session()->get('language', DI::config()->get('system', 'language'));
	$units = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_units');
	$appid = DI::config()->get('curweather', 'appid');
	$cachetime = intval(DI::config()->get('curweather', 'cachetime'));

	if ($units === '') {
		$units = 'metric';
	}

	$ok = true;

	$res = getWeather($rpt, $units, $lang, $appid, $cachetime);

	if ($res === false) {
		$ok = false;
	}

	if ($ok) {
		$t = Renderer::getMarkupTemplate("widget.tpl", "addon/curweather/" );
		$curweather = Renderer::replaceMacros($t, [
			'$title' => DI::l10n()->t("Current Weather"),
			'$icon' => ProxyUtils::proxifyUrl('http://openweathermap.org/img/w/'.$res['icon'].'.png'),
			'$city' => $res['city'],
			'$lon' => $res['lon'],
			'$lat' => $res['lat'],
			'$description' => $res['descripion'],
			'$temp' => $res['temperature'],
			'$relhumidity' => ['caption'=>DI::l10n()->t('Relative Humidity'), 'val'=>$res['humidity']],
			'$pressure' => ['caption'=>DI::l10n()->t('Pressure'), 'val'=>$res['pressure']],
			'$wind' => ['caption'=>DI::l10n()->t('Wind'), 'val'=> $res['wind']],
			'$lastupdate' => DI::l10n()->t('Last Updated').': '.$res['update'].'UTC',
			'$databy' =>  DI::l10n()->t('Data by'),
			'$showonmap' => DI::l10n()->t('Show on map')
		]);
	} else {
		$t = Renderer::getMarkupTemplate('widget-error.tpl', 'addon/curweather/');
		$curweather = Renderer::replaceMacros( $t, [
			'$problem' => DI::l10n()->t('There was a problem accessing the weather data. But have a look'),
			'$rpt' => $rpt,
			'$atOWM' => DI::l10n()->t('at OpenWeatherMap')
		]);
	}

	DI::page()['aside'] = $curweather . DI::page()['aside'];
}

function curweather_addon_settings_post($post)
{
	if (!DI::userSession()->getLocalUserId() || empty($_POST['curweather-settings-submit'])) {
		return;
	}

	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_loc'   , trim($_POST['curweather_loc']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_enable', intval($_POST['curweather_enable']));
	DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_units' , trim($_POST['curweather_units']));
}

function curweather_addon_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$curweather_loc   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_loc');
	$curweather_units = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_units');
	$appid            = DI::config()->get('curweather', 'appid');

	if ($appid == '') {
		$noappidtext = DI::l10n()->t('No APPID found, please contact your admin to obtain one.');
	} else {
		$noappidtext = '';
	}

	$enabled = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'curweather', 'curweather_enable'));

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/curweather/');
	$html = Renderer::replaceMacros($t, [
		'$noappidtext'      => $noappidtext,
		'$info'             => DI::l10n()->t('Enter either the name of your location or the zip code.'),
		'$curweather_loc'   => ['curweather_loc', DI::l10n()->t('Your Location'), $curweather_loc, DI::l10n()->t('Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.')],
		'$curweather_units' => ['curweather_units', DI::l10n()->t('Units'), $curweather_units, DI::l10n()->t('select if the temperature should be displayed in &deg;C or &deg;F'), ['metric' => '°C', 'imperial' => '°F']],
		'$enabled'          => ['curweather_enable', DI::l10n()->t('Show weather data'), $enabled, ''],
	]);

	$data = [
		'addon' => 'curweather',
		'title' => DI::l10n()->t('Current Weather Settings'),
		'html'  => $html,
	];
}

// Config stuff for the admin panel to let the admin of the node set a APPID
// for accessing the API of openweathermap
function curweather_addon_admin_post()
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	if (!empty($_POST['curweather-submit'])) {
		DI::config()->set('curweather', 'appid',     trim($_POST['appid']));
		DI::config()->set('curweather', 'cachetime', trim($_POST['cachetime']));
	}
}

function curweather_addon_admin(string &$o)
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	$appid = DI::config()->get('curweather', 'appid');
	$cachetime = DI::config()->get('curweather', 'cachetime');

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/curweather/' );

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$cachetime' => [
			'cachetime',
			DI::l10n()->t('Caching Interval'),
			$cachetime,
			DI::l10n()->t('For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'), [
				'0'    => DI::l10n()->t('no cache'),
				'300'  => '5 '  . DI::l10n()->t('minutes'),
				'900'  => '15 ' . DI::l10n()->t('minutes'),
				'1800' => '30 ' . DI::l10n()->t('minutes'),
				'3600' => '60 ' . DI::l10n()->t('minutes')
			]
		],
		'$appid' => ['appid', DI::l10n()->t('Your APPID'), $appid, DI::l10n()->t('Your API key provided by OpenWeatherMap')]
	]);
}
