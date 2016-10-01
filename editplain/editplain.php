<?php
/**
 * Name: Editplain
 * Description: This addon is deprecated and has been replaced with the "Advanced Features" setting.  Admins should remove this addon when their core code is updated to include advanced feature settings.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Status: Unsupported.
 */
function editplain_install()
{
    register_hook('plugin_settings', 'addon/editplain/editplain.php', 'editplain_settings');
    register_hook('plugin_settings_post', 'addon/editplain/editplain.php', 'editplain_settings_post');

    logger('installed editplain');
}

function editplain_uninstall()
{
    unregister_hook('plugin_settings', 'addon/editplain/editplain.php', 'editplain_settings');
    unregister_hook('plugin_settings_post', 'addon/editplain/editplain.php', 'editplain_settings_post');

    logger('removed editplain');
}

/**
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 */
function editplain_settings_post($a, $post)
{
    if (!local_user() || (!x($_POST, 'editplain-submit'))) {
        return;
    }
    set_pconfig(local_user(), 'system', 'plaintext', intval($_POST['editplain']));

    info(t('Editplain settings updated.').EOL);
}

/**
 * Called from the Plugin Setting form.
 * Add our own settings info to the page.
 */
function editplain_settings(&$a, &$s)
{
    if (!local_user()) {
        return;
    }

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="'.$a->get_baseurl().'/addon/editplain/editplain.css'.'" media="all" />'."\r\n";

    /* Get the current state of our config variable */

    $enabled = get_pconfig(local_user(), 'system', 'plaintext');
    $checked = (($enabled) ? ' checked="checked" ' : '');

    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>'.t('Editplain Settings').'</h3>';
    $s .= '<div id="editplain-enable-wrapper">';
    $s .= '<label id="editplain-enable-label" for="editplain-checkbox">'.t('Disable richtext status editor').'</label>';
    $s .= '<input id="editplain-checkbox" type="checkbox" name="editplain" value="1" '.$checked.'/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" name="editplain-submit" class="settings-submit" value="'.t('Save Settings').'" /></div></div>';
}
