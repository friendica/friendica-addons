	<?php
/**
 * Name: Current Weather 
 * Description: Shows current weather conditions for user's location on their network page.<br />Find the location code for the station or airport nearest you <a href="http://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code" target="_blank">here</a>.
 * Version: 1.0
 * Author: Tony Baldwin <http://friendica.tonybaldwin.info/u/t0ny>
 * Author: Fabio Comuni <http://kirkgroup.com/u/fabrixxm>
 *
 */
require_once('addon/curweather/getweather.php');

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

    if(! intval(get_pconfig(local_user(),'curweather','curweather_enable')))
        return;

    $fk_app->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $fk_app->get_baseurl() . '/addon/curweather/curweather.css' . '" media="all" />' . "\r\n";

    // the getweather file does all the work here
    // the $rpt value is needed for location
    // which getweather uses to fetch the weather data for weather and temp
    $rpt = get_pconfig(local_user(), 'curweather', 'curweather_loc');
    $wxdata = GetWeather::get($rpt);
    $temp = $wxdata['TEMPERATURE_STRING'];
    $weather = $wxdata['WEATHER'];
    $rhumid = $wxdata['RELATIVE_HUMIDITY'];
    $pressure = $wxdata['PRESSURE_STRING'];
    $wind = $wxdata['WIND_STRING'];
    $curweather = '<div id="curweather-network" class="widget">
                <div class="title tool">
                <h4>'.t("Current Weather").'</h4></div>';

    $curweather .= "Weather: $weather<br />
                 Temperature: $temp<br />
		 Relative Humidity: $rhumid<br />
		 Pressure: $pressure<br />
		 Wind: $wind";

    $curweather .= '</div><div class="clear"></div>';

    $fk_app->page['aside'] = $curweather.$fk_app->page['aside'];

}


function curweather_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'curweather-settings-submit')))
		return;
	set_pconfig(local_user(),'curweather','curweather_loc',trim($_POST['curweather_loc']));
	set_pconfig(local_user(),'curweather','curweather_enable',intval($_POST['curweather_enable']));

	info( t('Current Weather settings updated.') . EOL);
}


function curweather_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the curweather so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/curweather/curweather.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$curweather_loc = get_pconfig(local_user(), 'curweather', 'curweather_loc');
	$enable = intval(get_pconfig(local_user(),'curweather','curweather_enable'));
	$enable_checked = (($enable) ? ' checked="checked" ' : '');
	
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Current Weather') . '</h3>';
	$s .= '<div id="curweather-settings-wrapper">';
	$s .= '<p>Find the location code for the airport/weather station nearest you <a href="http://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code" target="_blank">here</a>.</p>';
	$s .= '<label id="curweather-location-label" for="curweather_loc">' . t('Weather Location: ') . '</label>';
	$s .= '<input id="curweather-location" type="text" name="curweather_loc" value="' . $curweather_loc . '"/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="curweather-enable-label" for="curweather_enable">' . t('Enable Current Weather') . '</label>';
	$s .= '<input id="curweather-enable" type="checkbox" name="curweather_enable" value="1" ' . $enable_checked . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="curweather-settings-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div></div>';

}


