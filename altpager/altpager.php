<?php
/**
 * Name: Alternate Pagination
 * Description: Change pagination from using explicit page numbers to simple "newer" and "older" page links. This will speed up page load times.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 *
 */


function altpager_install() {

	register_hook('plugin_settings', 'addon/altpager/altpager.php', 'altpager_settings');
	register_hook('plugin_settings_post', 'addon/altpager/altpager.php', 'altpager_settings_post');

	logger("installed altpager");
}


function altpager_uninstall() {

	unregister_hook('plugin_settings', 'addon/altpager/altpager.php', 'altpager_settings');
	unregister_hook('plugin_settings_post', 'addon/altpager/altpager.php', 'altpager_settings_post');


	logger("removed altpager");
}



/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function altpager_settings_post($a,$post) {
	if(! local_user() || (! x($_POST,'altpager-submit')))
		return;

	set_pconfig(local_user(),'system','alt_pager',intval($_POST['altpager']));
	info( t('Altpager settings updated.') . EOL);
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function altpager_settings(&$a,&$s) {

	if(! local_user())
		return;

	$global = get_config("alt_pager", "global");
	if($global == 1)
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/altpager/altpager.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$altpager = get_pconfig(local_user(),'system','alt_pager');
	if($altpager === false)
		$altpager = 0;

	$checked = (($altpager) ? ' checked="checked" ' : '');
	
	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Alternate Pagination Setting') . '</h3>';
	$s .= '<div id="altpager-wrapper">';
	$s .= '<label id="altpager-label" for="altpager">' . t('Use links to "newer" and "older" pages in place of page numbers?') . '</label>';
	$s .= '<input id="altpager-input" type="checkbox" name="altpager" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="altpager-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}

function altpager_plugin_admin(&$a, &$o){
	$t = get_markup_template( "admin.tpl", "addon/altpager/" );
	$o = replace_macros($t, array(
		'$submit' => t('Submit'),
		'$global' => array('altpagerchoice', t('Global'), 1, t('Force global use of the alternate pager'),  get_config('alt_pager', 'global') == 1),
		'$individual' => array('altpagerchoice', t('Individual'), 2, t('Each user chooses whether to use the alternate pager'),  get_config('alt_pager', 'global') == 0)
	));
}

function altpager_plugin_admin_post(&$a){
	$choice	=	((x($_POST,'altpagerchoice'))		? notags(trim($_POST['altpagerchoice']))	: '');
	set_config('alt_pager','global',($choice == 1 ? 1 : 0));
	info( t('Settings updated.'). EOL );
}

