<?php

/**
 * Name: MathJax
 * Description: Addon for Friendika to include MathJax (LaTeX math syntax)
 * Version: 1.0
 * Author: Tobias Diekershoff <http://diekershoff.homeunix.net/friendika/profile/tobias>
 * License: 3-clause BSD license
 */

function mathjax_install() {
    register_hook('page_header', 'addon/mathjax/mathjax.php', 'mathjax_page_header');
    register_hook('plugin_settings', 'addon/mathjax/mathjax.php', 'mathjax_settings'); 
    register_hook('plugin_settings_post', 'addon/mathjax/mathjax.php', 'mathjax_settings_post');
    logger('installed js_math plugin');
}
function mathjax_uninstall() {
    unregister_hook('page_header', 'addon/mathjax/mathjax.php', 'mathjax_page_header');
    unregister_hook('plugin_settings', 'addon/mathjax/mathjax.php', 'mathjax_settings'); 
    unregister_hook('plugin_settings_post', 'addon/mathjax/mathjax.php', 'mathjax_settings_post');
}
function mathjax_settings_post ($a, $post) {
    if (! local_user())
        return;
    // don't check statusnet settings if statusnet submit button is not clicked
    if (!x($_POST,'mathjax-submit'))
        return;
    set_pconfig(local_user(),'mathjax','use',intval($_POST['mathjax_use']));
}
function mathjax_settings (&$a, &$s) {
    if (! local_user())
        return;
    $use = get_pconfig(local_user(),'mathjax','use');
    $usetext = (($use) ? ' checked="checked" ' : '');
    $s .= '<div class="settings-block">';
    $s .= '<h3>MathJax '.t('Settings').'</h3>';
    $s .= '<p>'.t('The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.').'</p>';
    $s .= '<label id="mathjax_label" for="mathjax_use">'.t('Use the MathJax renderer').'</label>';
    $s .= '<input id="mathjax_use" type="checkbox" name="mathjax_use" value="1"'. $usetext .' />';
    $s .= '<div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="mathjax-submit" name="mathjax-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
    $s .= '</div>';
}
/*  we need to add one JavaScript include command to the html output
 *  note that you have to check the jsmath/easy/load.js too.
 */
function mathjax_page_header($a, &$b) {
    //  if the visitor of the page is not a local_user, use MathJax
    //  otherwise check the users settings.
    $url = get_config ('mathjax','baseurl');
	if(! $url)
		$url = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    if (! local_user()) {
        $b .= '<script type="text/javascript" src="'.$url.'"></script>';
    } else {
        $use = get_pconfig(local_user(),'mathjax','use');
        if ($use) { 
            $b .= '<script type="text/javascript" src="'.$url.'"></script>';
        }
    }
}
function mathjax_plugin_admin_post (&$a) {
    $baseurl = ((x($_POST, 'baseurl')) ? trim($_POST['baseurl']) : 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
    set_config('mathjax','baseurl',$baseurl);
    info( t('Settings updated.'). EOL);
}
function mathjax_plugin_admin (&$a, &$o) {
	$t = get_markup_template( "admin.tpl", "addon/mathjax/" );
	if (get_config('mathjax','baseurl','') == '') {
		set_config('mathjax','baseurl','http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
	}

	$o = replace_macros( $t, array(
		'$submit' => t('Submit'),
		'$baseurl' => array('baseurl', t('MathJax Base URL'), get_config('mathjax','baseurl' ), t('The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax.')),
	));
}
