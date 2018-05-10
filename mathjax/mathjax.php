<?php
/**
 * Name: MathJax
 * Description: Addon for Friendika to include MathJax (LaTeX math syntax)
 * Version: 1.1
 * Author: Tobias Diekershoff <https://social.diekershoff.de/profile/tobias>
 * License: 3-clause BSD license
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function mathjax_install() {
	Addon::registerHook('load_config', 'addon/mathjax/mathjax.php', 'mathjax_load_config');
    Addon::registerHook('page_header', 'addon/mathjax/mathjax.php', 'mathjax_page_header');
    Addon::registerHook('addon_settings', 'addon/mathjax/mathjax.php', 'mathjax_settings');
    Addon::registerHook('addon_settings_post', 'addon/mathjax/mathjax.php', 'mathjax_settings_post');
    Addon::registerHook('template_vars', 'addon/mathjax/mathjax.php', 'mathjax_template_vars');
    logger('installed js_math addon');
}

function mathjax_uninstall() {
	Addon::unregisterHook('load_config', 'addon/mathjax/mathjax.php', 'mathjax_load_config');
    Addon::unregisterHook('page_header', 'addon/mathjax/mathjax.php', 'mathjax_page_header');
    Addon::unregisterHook('addon_settings', 'addon/mathjax/mathjax.php', 'mathjax_settings');
    Addon::unregisterHook('addon_settings_post', 'addon/mathjax/mathjax.php', 'mathjax_settings_post');
    Addon::unregisterHook('template_vars', 'addon/mathjax/mathjax.php', 'mathjax_template_vars');
}

function mathjax_load_config(\Friendica\App $a)
{
	$a->loadConfigFile(__DIR__. '/config/mathjax.ini.php');
}

function mathjax_template_vars($a, &$arr)
{
    if (!array_key_exists('addon_hooks',$arr['vars']))
    {
	$arr['vars']['addon_hooks'] = array();
    }
    $arr['vars']['addon_hooks'][] = "mathjax";
}

function mathjax_settings_post ($a, $post) {
    if (! local_user())
        return;
    if (!x($_POST,'mathjax-submit'))
        return;
    PConfig::set(local_user(),'mathjax','use',intval($_POST['mathjax_use']));
}
function mathjax_settings (&$a, &$s) {
    if (! local_user())
        return;
    $use = PConfig::get(local_user(),'mathjax','use');
    $usetext = (($use) ? ' checked="checked" ' : '');
    $s .= '<span id="settings_mathjax_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_mathjax_expanded\'); openClose(\'settings_mathjax_inflated\');">';
    $s .= '<h3>MathJax '.L10n::t('Settings').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_mathjax_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_mathjax_expanded\'); openClose(\'settings_mathjax_inflated\');">';
    $s .= '<h3>MathJax '.L10n::t('Settings').'</h3>';
    $s .= '</span>';
    $s .= '<p>'.L10n::t('The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.').'</p>';
    $s .= '<label id="mathjax_label" for="mathjax_use">'.L10n::t('Use the MathJax renderer').'</label>';
    $s .= '<input id="mathjax_use" type="checkbox" name="mathjax_use" value="1"'. $usetext .' />';
    $s .= '<div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="mathjax-submit" name="mathjax-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
    $s .= '</div>';
}
/*  we need to add one JavaScript include command to the html output
 *  note that you have to check the jsmath/easy/load.js too.
 */
function mathjax_page_header(App $a, array &$b) {
    //  if the visitor of the page is not a local_user, use MathJax
    //  otherwise check the users settings.
    $url = Config::get ('mathjax','baseurl');
		if(! $url) {
			$url = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-MML-AM_CHTML';
		}
    if (! local_user()) {
        $b .= '<script type="text/javascript" src="'.$url.'" async></script>';
    } else {
        $use = PConfig::get(local_user(),'mathjax','use');
        if ($use) {
            $b .= '<script type="text/javascript" src="'.$url.'" async></script>';
        }
    }
}
function mathjax_addon_admin_post (&$a) {
    $baseurl = ((x($_POST, 'mjbaseurl')) ? trim($_POST['mjbaseurl']) : 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-MML-AM_CHTML');
    Config::set('mathjax','baseurl',$baseurl);
    info(L10n::t('Settings updated.'). EOL);
}
function mathjax_addon_admin (App $a, &$o) {
	$t = get_markup_template( "admin.tpl", "addon/mathjax/" );

	if (Config::get('mathjax','baseurl','') == '') {
		Config::set('mathjax','baseurl','https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-MML-AM_CHTML');
	}

	$o = replace_macros( $t, [
		'$submit' => L10n::t('Save Settings'),
		'$mjbaseurl' => ['mjbaseurl', L10n::t('MathJax Base URL'), Config::get('mathjax','baseurl' ), L10n::t('The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax.'), 'required']
	]);
}
