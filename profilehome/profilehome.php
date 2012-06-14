<?php
/**
 * Name: Profile home
 * Description: Redirect from homepage to a profile
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */



function profilehome_install() {
	register_hook('home_content', 'addon/profilehome/profilehome.php', 'profilehome_home');
	logger("installed profilehome");
}

function profilehome_uninstall() {
	unregister_hook('home_content', 'addon/profilehome/profilehome.php', 'profilehome_home');
	logger("removed profilehome");
}

function profilehome_home(&$a, &$o){
    $user = get_config("profilehome","user");
    if ($user!==false)	goaway($a->get_baseurl()."/profile/".$user);
}

function profilehome_plugin_admin(&$a, &$o){
    $r =  q("SELECT nickname, username FROM user WHERE verified=1 AND account_removed=0 AND account_expired=0");
    $users = array("##no##"=>"No redirect (use default home)"); 
    foreach ($r as $u) {
        $users[$u['nickname']] = $u['username']." (".$u['nickname'].")";
    }
    
    $user = get_config("profilehome","user");
    
    $t = file_get_contents(dirname(__file__)."/admin.tpl");
	$o = '<input type="hidden" name="form_security_token" value="' .get_form_security_token("profilehomesave") .'">';
	$o .= replace_macros( $t, array(
		'$submit' => t('Submit'),
		'$user' => array('user', t('Profile to use as home page'), $user, "", $users),
	));
}

function profilehome_plugin_admin_post(&$a){
    check_form_security_token('profilehomesave');
    
    $user = ((x($_POST, 'user')) ? notags(trim($_POST['user'])) : false);
    if ($user=='##no##') $user=false;
	set_config('profilehome', 'user', $user);
    info( t('Profile home settings updated.') .EOL);
}