	<?php
/**
 * Name: Current Temperature
 * Description: Shows current temperature for user's location on their network page.<br />Find the location code for the station or airport nearest you at http://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code
 * Version: 1.0
 * Author: Tony Baldwin <t0ny@friendica.tonybaldwin.info>
 *
 */
require_once('addon/curtemp/getweather.php');

function curtemp_install() {
	register_hook('network_mod_init', 'addon/curtemp/curtemp.php', 'curtemp_network_mod_init');
	register_hook('plugin_settings', 'addon/curtemp/curtemp.php', 'curtemp_plugin_settings');
	register_hook('plugin_settings_post', 'addon/curtemp/curtemp.php', 'curtemp_plugin_settings_post');

}

function curtemp_uninstall() {
	unregister_hook('network_mod_init', 'addon/curtemp/curtemp.php', 'curtemp_network_mod_init');
	unregister_hook('plugin_settings', 'addon/curtemp/curtemp.php', 'curtemp_plugin_settings');
	unregister_hook('plugin_settings_post', 'addon/curtemp/curtemp.php', 'curtemp_plugin_settings_post');

}


function curtemp_network_mod_init(&$fk_app,&$b) {

    if(! intval(get_pconfig(local_user(),'curtemp','curtemp_enable')))
        return;

    $fk_app->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $fk_app->get_baseurl() . '/addon/curtemp/curtemp.css' . '" media="all" />' . "\r\n";

    // the getweather file does all the work here
    // the $rpt value is needed for location
    // which getweather uses to fetch the weather data for weather and temp
    $rpt = get_pconfig(local_user(), 'curtemp', 'curtemp_loc');
    $wxdata = GetWeather::get($rpt);
    $temp = $wxdata['TEMPERATURE_STRING'];
    $weather = $wxdata['WEATHER'];
    $curtemp = '<div id="curtemp-network" class="widget">
                <div class="title tool">
                <h4>'.t("Current Temp").'</h4></div>';

    $curtemp .= "Weather: $weather <br />
                 Temperature: $temp ";

    $curtemp .= '</div><div class="clear"></div>';

    $fk_app->page['aside'] = $curtemp.$fk_app->page['aside'];

}


function curtemp_plugin_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'curtemp-settings-submit')))
		return;
	set_pconfig(local_user(),'curtemp','curtemp_loc',trim($_POST['curtemp_loc']));
	set_pconfig(local_user(),'curtemp','curtemp_enable',intval($_POST['curtemp_enable']));

	info( t('Current Temp settings updated.') . EOL);
}


function curtemp_plugin_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the curtemp so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/curtemp/curtemp.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$curtemp_loc = get_pconfig(local_user(), 'curtemp', 'curtemp_loc');
	$enable = intval(get_pconfig(local_user(),'curtemp','curtemp_enable'));
	$enable_checked = (($enable) ? ' checked="checked" ' : '');
	
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Current Temp Settings') . '</h3>';
	$s .= '<div id="curtemp-settings-wrapper">';
	$s .= '<p>Find the location code for the airport/weather station nearest you <a href="http://en.wikipedia.org/wiki/International_Air_Transport_Association_airport_code">here</a>.</p>';
	$s .= '<label id="curtemp-location-label" for="curtemp_loc">' . t('Current Temp Location: ') . '</label>';
	$s .= '<input id="curtemp-location" type="text" name="curtemp_loc" value="' . $curtemp_loc . '"/>';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="curtemp-enable-label" for="curtemp_enable">' . t('Enable Curent Temp') . '</label>';
	$s .= '<input id="curtemp-enable" type="checkbox" name="curtemp_enable" value="1" ' . $enable_checked . '/>';
	$s .= '<div class="clear"></div>';

	$s .= '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="curtemp-settings-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


