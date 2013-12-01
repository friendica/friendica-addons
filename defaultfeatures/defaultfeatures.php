<?php
/**
 * Name: Default Features
 * Description: Choose which Additional Features are on by default for new users on the site.
 * Version: 1.0
 * Author: Michael Johnston
 */

function defaultfeatures_install() {
    register_hook('register_account', 'addon/defaultfeatures/defaultfeatures.php', 'defaultfeatures_register');
    logger("installed defaultfeatures plugin");
}

function defaultfeatures_uninstall() {
    unregister_hook('register_account', 'addon/defaultfeatures/defaultfeatures.php', 'defaultfeatures_register');
    logger("uninstalled defaultfeatures plugin");
}

function defaultfeatures_register($a, $newuid) {
    $arr = array();
    $features = get_features();
    foreach($features as $fname => $fdata) {
	    foreach(array_slice($fdata,1) as $f) {
                    set_pconfig($newuid,'feature',$f[0],((intval(get_config('defaultfeatures',$f[0]))) ? "1" : "0"));
	    }
    }
}

function defaultfeatures_plugin_admin_post (&$a) {
    check_form_security_token_redirectOnErr('/admin/plugins/defaultfeatures', 'defaultfeaturessave');
    foreach($_POST as $k => $v) {
	    if(strpos($k,'feature_') === 0) {
	            set_config('defaultfeatures',substr($k,8),((intval($v)) ? 1 : 0));
	    }
    }
    info( t('Features updated') . EOL);
}

function defaultfeatures_plugin_admin (&$a, &$o) {
    $t = get_markup_template( "admin.tpl", "addon/defaultfeatures/" );
    $token = get_form_security_token("defaultfeaturessave");
    $arr = array();
    $features = get_features();
    foreach($features as $fname => $fdata) {
	    $arr[$fname] = array();
	    $arr[$fname][0] = $fdata[0];
	    foreach(array_slice($fdata,1) as $f) {
		    $arr[$fname][1][] = array('feature_' .$f[0],$f[1],((intval(get_config('defaultfeatures',$f[0]))) ? "1" : "0"),$f[2],array(t('Off'),t('On')));
	    }
    }

    //logger("Features: " . print_r($arr,true));

    $o = replace_macros($t, array(
        '$submit' => t('Save Settings'),
        '$features' => $arr,
        '$form_security_token' => $token
    ));
}
