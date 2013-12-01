<?php
/**
 * Name: unhosted remote storage
 * Description: Expose in user XRD the link to external user's unhosted-enabled storage
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */
 
 function uhremotestorage_install() {
	register_hook('personal_xrd', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_personal_xrd');
	register_hook('plugin_settings', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_settings');
	register_hook('plugin_settings_post', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_settings_post');

	logger("installed uhremotestorage");
}


function uhremotestorage_uninstall() {

	unregister_hook('personal_xrd', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_personal_xrd');
	unregister_hook('plugin_settings', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_settings');
	unregister_hook('plugin_settings_post', 'addon/uhremotestorage/uhremotestorage.php', 'uhremotestorage_settings_post');

	logger("removed uhremotestorage");
}

function uhremotestorage_personal_xrd($a, &$b){
	list($user, $host) = explode("@",$_GET['uri']);
	$user = str_replace("acct:","",$user);
	$r = q("SELECT uid FROM user WHERE nickname='%s'", dbesc($user));
	$uid = $r[0]['uid'];
	
	$url = get_pconfig($uid,'uhremotestorage','unhoestedurl');
	$auth = get_pconfig($uid,'uhremotestorage','unhoestedauth');
	$api = get_pconfig($uid,'uhremotestorage','unhoestedapi');
	
	if ($url){
		$b['xml'] = str_replace(
			'</XRD>', 
			"\t".'<Link rel="remoteStorage" template="'.$url.'" api="'.$api.'" auth="'.$auth.'" ></Link>'."\n</XRD>",
			$b['xml']
		);
	}
}

function uhremotestorage_settings_post($a, $post){
	if(! local_user())
		return;
	set_pconfig(local_user(),'uhremotestorage','unhoestedurl',$_POST['unhoestedurl']);
	set_pconfig(local_user(),'uhremotestorage','unhoestedauth',$_POST['unhoestedauth']);
	set_pconfig(local_user(),'uhremotestorage','unhoestedapi',$_POST['unhoestedapi']);
}

function uhremotestorage_settings($a, &$s){
	if(! local_user())
		return;
	
	$uid = $a->user['nickname'] ."@". $a->get_hostname();
	
	$url = get_pconfig(local_user(),'uhremotestorage','unhoestedurl');
	$auth = get_pconfig(local_user(),'uhremotestorage','unhoestedauth');
	$api = get_pconfig(local_user(),'uhremotestorage','unhoestedapi');
	
	
	$arr_api = array(
		'WebDAV' => "WebDAV",
		'simple' => "simple WebDAV",
		'CouchDB' => "CouchDB",
	);
	/* experimental apis */
	/*
	$api = array_merge($api, array(
		'git' => "git",
		'tahoe-lafs' => 'tahoe-lafs',
		'camlistore' => 'camlistore',
		'AmazonS3' => 'AmazonS3',
		'GoogleDocs' => 'GoogleDocs',
		'Dropbox' => 'Dropbox',
	);
	*/
	$tpl = get_markup_template("settings.tpl", "addon/uhremotestorage/");
	$s .= replace_macros($tpl, array(
		'$title' => 'Unhosted remote storage',
		'$desc' => sprintf( t('Allow to use your friendica id (%s) to connecto to external unhosted-enabled storage (like ownCloud). See <a href="http://www.w3.org/community/unhosted/wiki/RemoteStorage#WebFinger">RemoteStorage WebFinger</a>'), $uid ),
		'$url'	=> array( 'unhoestedurl', t('Template URL (with {category})'), $url, 'If your are using ownCloud, your unhosted url will be like <tt>http://<i>HOST</i>/apps/remoteStorage/WebDAV.php/<i>USER</i>/remoteStorage/{category}</tt>'),
		'$auth'	=> array( 'unhoestedauth', t('OAuth end-point'), $auth, 'If your are using ownCloud, your OAuth endpoint will be like <tt>http://<i>HOST</i>/apps/remoteStorage/auth.php/<i>USER</i></tt>'),
		'$api'	=> array( 'unhoestedapi', t('Api'), $api, 'If your are using ownCloud, your api will be <tt>WebDAV</tt>', $arr_api),
		
		'$submit' => t('Save Settings'),
	));
	
}
