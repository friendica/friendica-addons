<?php
/**
 * Name: Remember OpenID Login
 * Description: Autologin with last openid used
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */
 

function rememberoid_install(){ 
	register_hook('init_1','addon/rememberoid/rememberoid.php','rememberoid_init'); 
	register_hook('logging_out','addon/rememberoid/rememberoid.php','rememberoid_logout'); 
	register_hook('login_hook','addon/rememberoid/rememberoid.php','rememberoid_form'); 
}

function rememberoid_uninstall(){ 
	unregister_hook('init_1','addon/rememberoid/rememberoid.php','rememberoid_init'); 
	unregister_hook('logging_out','addon/rememberoid/rememberoid.php','rememberoid_logout'); 
	unregister_hook('login_hook','addon/rememberoid/rememberoid.php','rememberoid_form'); 
}

function rememberoid_init(&$a) {
	if (x($_COOKIE, "remember_oid") && !x($_SESSION['openid']) && !x($_SESSION,'authenticated') && !x($_POST,'auth-params') && $a->module === 'home' ){
		$_POST['openid_url'] = $_COOKIE["remember_oid"];
		$_POST['auth-params'] = 1;
	}
	if (x($_POST,'auth-params') && $_POST['openid_url'] && $_POST['openid_url']!="" && $_POST['remember_oid']){
		setcookie('remember_oid', $_POST['openid_url'],  time()+60*60*24*30, "/");
	}
}

function rememberoid_logout(&$a) {
	setcookie("rembember_oid", "", time()-3600);
}

function rememberoid_form(&$a, &$o){
	$tpl = get_markup_template("field_checkbox.tpl");
	$html = replace_macros($tpl, array(
		'$field' => array('remember_oid', t("Autologin with this OpenId"), false,''),
	));
	
	$o = preg_replace("|<div *id=[\"']login_openid[\"']>|", "<div id='login_openid'>".$html, $o);

}
