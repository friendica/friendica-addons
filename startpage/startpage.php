<?php
/**
 * Name: Start Page
 * Description: Set a preferred page to load on login from home page
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\PConfig;

function startpage_install() {
	Addon::registerHook('home_init', 'addon/startpage/startpage.php', 'startpage_home_init');
	Addon::registerHook('addon_settings', 'addon/startpage/startpage.php', 'startpage_settings');
	Addon::registerHook('addon_settings_post', 'addon/startpage/startpage.php', 'startpage_settings_post');
}

function startpage_uninstall()
{
	Addon::unregisterHook('home_init', 'addon/startpage/startpage.php', 'startpage_home_init');
	Addon::unregisterHook('addon_settings', 'addon/startpage/startpage.php', 'startpage_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/startpage/startpage.php', 'startpage_settings_post');
}

function startpage_home_init($a, $b)
{
	if (!local_user()) {
		return;
	}

	$page = PConfig::get(local_user(), 'startpage', 'startpage');
	if (strlen($page)) {
		goaway($page);
	}
	return;
}

/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function startpage_settings_post($a, $post)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['startpage-submit'])) {
		PConfig::set(local_user(), 'startpage', 'startpage', strip_tags(trim($_POST['startpage'])));
	}
}

/**
 *
 * Called from the Addon Setting form.
 * Add our own settings info to the page.
 *
 */
function startpage_settings(&$a, &$s)
{
	if (!local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/startpage/startpage.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$page = PConfig::get(local_user(), 'startpage', 'startpage');

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_startpage_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_startpage_expanded\'); openClose(\'settings_startpage_inflated\');">';
	$s .= '<h3>' . L10n::t('Startpage') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_startpage_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_startpage_expanded\'); openClose(\'settings_startpage_inflated\');">';
	$s .= '<h3>' . L10n::t('Startpage') . '</h3>';
	$s .= '</span>';
	$s .= '<div id="startpage-page-wrapper">';
	$s .= '<label id="startpage-page-label" for="startpage-page">' . L10n::t('Home page to load after login  - leave blank for profile wall') . '</label>';
	$s .= '<input id="startpage-page" type="text" name="startpage" value="' . $page . '" />';
	$s .= '</div><div class="clear"></div>';
	$s .= '<div id="startpage-desc">' . L10n::t('Examples: &quot;network&quot; or &quot;notifications/system&quot;') . '</div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="startpage-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';
}
