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
	
	$davurl = get_pconfig($b['user']['uid'],'uhremotestorage','davurl');
	if ($davurl){
		$b['xml'] = str_replace(
			'</XRD>', 
			"\t".'<Link rel="http://unhosted.org/spec/dav/0.1" href="'.$davurl.'"/>'."\n</XRD>",
			$b['xml']
		);
	}
}

function uhremotestorage_settings_post($a, $post){
	if(! local_user())
		return;
	set_pconfig(local_user(),'uhremotestorage','davurl',$_POST['unhoestedurl']);
}

function uhremotestorage_settings($a, &$s){
	if(! local_user())
		return;
	
	$uid = $a->user['nickname'] ."@". $a->get_hostname();
	
	$davurl = get_pconfig(local_user(),'uhremotestorage','davurl');
	
	$tpl = file_get_contents(dirname(__file__)."/settings.tpl");
	$s .= replace_macros($tpl, array(
		'$title' => 'Unhosted remote storage',
		'$desc' => sprintf( t('Allow to use your friendika id (%s) to connecto to external unhosted-enabled storage (like ownCloud)'), $uid ),
		'$url'	=> array( 'unhoestedurl', t('Unhosted DAV storage url'), $davurl, 'If your are using ownCloud, your unhosted url will be like <tt>http://<i>HOST</i>/apps/remoteStorage/compat.php/<i>USER</i>/remoteStorage/</tt>'),
		'$submit' => t('Submit'),
	));
	
}
