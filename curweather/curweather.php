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
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\AbstractCache;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;

// Must point to composer's autoload file.
require('vendor/autoload.php');

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

//  The caching mechanism is taken from the cache example of the
//  OpenWeatherMap-PHP-API library and a bit customized to allow admins to set
//  the caching time depending on the plans they got from openweathermap.org

class ExampleCache extends AbstractCache
{
    private function urlToPath($url)
    {
        $tmp = sys_get_temp_dir();
        $dir = $tmp . DIRECTORY_SEPARATOR . "OpenWeatherMapPHPAPI";
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $path = $dir . DIRECTORY_SEPARATOR . md5($url);
        return $path;
    }

    /**
     * @inheritdoc
     */
    public function isCached($url)
    {
        $path = $this->urlToPath($url);
        if (!file_exists($path) || filectime($path) + $this->seconds < time()) {
            return false;
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function getCached($url)
    {
        return file_get_contents($this->urlToPath($url));
    }
    /**
     * @inheritdoc
     */
    public function setCached($url, $content)
    {
        file_put_contents($this->urlToPath($url), $content);
    }
}


function curweather_network_mod_init(&$fk_app,&$b) {

    if(! intval(get_pconfig(local_user(),'curweather','curweather_enable')))
        return;

    $fk_app->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $fk_app->get_baseurl() . '/addon/curweather/curweather.css' . '" media="all" />' . "\r\n";

    // the OpenWeatherMap-PHP-APIlib does all the work here
    // the $rpt value is needed for location
    // $lang will be taken from the browser session to honour user settings
    // $units can be set in the settings by the user
    // $appid is configured by the admin in the admin panel
    // those parameters will be used to get: cloud status, temperature, preassure
    // and relative humidity for display, also the relevent area of the map is
    // linked from lat/log of the reply of OWMp
    $rpt = get_pconfig(local_user(), 'curweather', 'curweather_loc');


    //  set the language to the browsers language and use metric units
    $lang = $_SESSION['language'];
    $units = get_pconfig( local_user(), 'curweather', 'curweather_units');
    $appid = get_config('curweather','appid');
    $cachetime = intval(get_config('curweather','cachetime'));
    if ($units==="")
	$units = 'metric';
    // Get OpenWeatherMap object. Don't use caching (take a look into
    // Example_Cache.php to see how it works).
    //$owm = new OpenWeatherMap();
    $owm = new OpenWeatherMap(null, new ExampleCache(), $cachetime);
    
    try {
	$weather = $owm->getWeather($rpt, $units, $lang, $appid);
	$temp = $weather->temperature->getValue();
        if ( $units === 'metric') {
	    $temp .= '°C';
	} else {
	    $temp .= '°F';
	};
	$rhumid = $weather->humidity;
	$pressure = $weather->pressure;
	$wind = $weather->wind->speed->getDescription().', '.$weather->wind->speed . " " . $weather->wind->direction;
	$description = $weather->clouds->getDescription();
    } catch(OWMException $e) {
        info ( 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');
    } catch(\Exception $e) {
        info ('General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');
    }

    $curweather = '<div id="curweather-network" class="widget">
		<div class="title tool">
                <h4>'.t("Current Weather").': '.$weather->city->name.'</h4></div>';

    $curweather .= "$description; $temp<br />";
    $curweather .= t('Relative Humidity').": $rhumid<br />";
    $curweather .= t('Pressure').": $pressure<br />";
    $curweather .= t('Wind').": $wind<br />";
    $curweather .= '<span style="font-size:0.8em;">'. t('Data by').': <a href="http://openweathermap.org">OpenWeatherMap</a>. <a href="http://openweathermap.org/Maps?zoom=7&lat='.$weather->city->lat.'&lon='.$weather->city->lon.'&layers=B0FTTFF">'.t('Show on map').'</a></span>';

    $curweather .= '</div><div class="clear"></div>';

    $fk_app->page['aside'] = $curweather.$fk_app->page['aside'];

}


function curweather_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'curweather-settings-submit')))
		return;
	set_pconfig(local_user(),'curweather','curweather_loc',trim($_POST['curweather_loc']));
	set_pconfig(local_user(),'curweather','curweather_enable',intval($_POST['curweather_enable']));
	set_pconfig(local_user(),'curweather','curweather_units',trim($_POST['curweather_units']));

	info( t('Current Weather settings updated.') . EOL);
}


function curweather_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Get the current state of our config variable */

	$curweather_loc = get_pconfig(local_user(), 'curweather', 'curweather_loc');
	$curweather_units = get_pconfig(local_user(), 'curweather', 'curweather_units');
	$appid = get_config('curweather','appid');
	if ($appid=="") { 
		$noappidtext = t('No APPID found, please contact your admin to optain one.');
	} else {
	    $noappidtext = '';
	}
	$enable = intval(get_pconfig(local_user(),'curweather','curweather_enable'));
	$enable_checked = (($enable) ? ' checked="checked" ' : '');
	
	// load template and replace the macros
	$t = get_markup_template("settings.tpl", "addon/curweather/" );
	$s = replace_macros ($t, array(
    		'$submit' => t('Save Settings'),	    
		'$header' => t('Current Weather').' '.t('Settings'),
		'$noappidtext' => $noappidtext,
		'$info' => t('Enter either the name of your location or the zip code.'),
		'$curweather_loc' => array( 'curweather_loc', t('Your Location'), $curweather_loc, t('Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.') ),
		'$curweather_units' => array( 'curweather_units', t('Units'), $curweather_units, t('select if the temperatur should be displayed in °C or °F'), array('metric'=>'°C', 'imperial'=>'°F')),
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
	    set_config('curweather','appid',trim($_POST['appid']));
	    set_config('curweather','cachetime',trim($_POST['cachetime']));
	    info( t('Curweather settings saved.'.EOL));
	}
}
function curweather_plugin_admin (&$a, &$o) {
    if(! is_site_admin())
	    return;
    $appid = get_config('curweather','appid');
    $cachetime = get_config('curweather','cachetime');
    $t = get_markup_template("admin.tpl", "addon/curweather/" );
    $o = replace_macros ($t, array(
	'$submit' => t('Save Settings'),
	'$cachetime' => array('cachetime', t('Caching Interval'), $cachetime, t('For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'), array('0'=>t('no cache'), '300'=>'5 '.t('minutes'), '900'=>'15 '.t('minutes'), '1800'=>'30 '.t('minutes'), '3600'=>'60 '.t('minutes'))),
	'$appid' => array('appid', t('Your APPID'), $appid, t('Your API key provided by OpenWeatherMap'))
    ));
}
