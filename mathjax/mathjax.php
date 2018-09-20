<?php
/**
 * Name: MathJax
 * Description: Addon for Friendika to include MathJax (LaTeX math syntax)
 * Version: 2.0
 * Author: Tobias Diekershoff <https://social.diekershoff.de/profile/tobias>
 * Author: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 * License: 3-clause BSD license
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function mathjax_install()
{
	Addon::registerHook('page_end'           , __FILE__, 'mathjax_page_end');
	Addon::registerHook('addon_settings'     , __FILE__, 'mathjax_settings');
	Addon::registerHook('addon_settings_post', __FILE__, 'mathjax_settings_post');
}

function mathjax_uninstall()
{
	Addon::unregisterHook('page_end'           , __FILE__, 'mathjax_page_end');
	Addon::unregisterHook('addon_settings'     , __FILE__, 'mathjax_settings');
	Addon::unregisterHook('addon_settings_post', __FILE__, 'mathjax_settings_post');

	// Legacy hooks
	Addon::unregisterHook('load_config'        , __FILE__, 'mathjax_load_config');
	Addon::unregisterHook('page_header'        , __FILE__, 'mathjax_page_header');
	Addon::unregisterHook('template_vars'      , __FILE__, 'mathjax_template_vars');
}

function mathjax_settings_post($a)
{
	if (!local_user()) {
		return;
	}

	if (empty($_POST['mathjax-submit'])) {
		return;
	}

	PConfig::set(local_user(), 'mathjax', 'use', intval($_POST['mathjax_use']));
}

function mathjax_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$use = PConfig::get(local_user(), 'mathjax', 'use', false);
	$usetext = $use ? ' checked="checked" ' : '';
	$s .= '<span id="settings_mathjax_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_mathjax_expanded\'); openClose(\'settings_mathjax_inflated\');">';
	$s .= '<h3>MathJax ' . L10n::t('Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_mathjax_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_mathjax_expanded\'); openClose(\'settings_mathjax_inflated\');">';
	$s .= '<h3>MathJax ' . L10n::t('Settings') . '</h3>';
	$s .= '</span>';
	$s .= '<p>' . L10n::t('The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.') . '</p>';
	$s .= '<label id="mathjax_label" for="mathjax_use">' . L10n::t('Use the MathJax renderer') . '</label>';
	$s .= '<input id="mathjax_use" type="checkbox" name="mathjax_use" value="1"' . $usetext . ' />';
	$s .= '<div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="mathjax-submit" name="mathjax-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div>';
	$s .= '</div>';
}

/*  we need to add one JavaScript include command to the html output
 *  note that you have to check the jsmath/easy/load.js too.
 */
function mathjax_page_end(App $a, &$b)
{
	//  if the visitor of the page is not a local_user, use MathJax
	//  otherwise check the users settings.
	$url = $a->get_baseurl() . '/addon/mathjax/asset/MathJax.js?config=TeX-MML-AM_CHTML';

	if (!local_user() || PConfig::get(local_user(), 'mathjax', 'use', false)) {
		$b .= <<<HTML
<script type="text/javascript" src="{$url}"></script>
<script type="text/javascript">
	document.addEventListener('postprocess_liveupdate', function () {
		MathJax.Hub.Queue(['Typeset', MathJax.Hub]);
	});
</script>
HTML;
	}
}
