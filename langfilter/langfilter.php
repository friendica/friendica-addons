<?php
/*
 * Name: Language Filter
 * Version: 0.1
 * Description: Filters out postings in languages not spoken by the users
 * Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
 * License: MIT
 */

require_once 'library/langdet/Text/LanguageDetect.php';

/* Define the hooks we want to use
 * that is, we have settings, we need to save the settings and we want
 * to modify the content of a posting when friendica prepares it.
 */
function langfilter_install()
{
    register_hook('prepare_body',         'addon/langfilter/langfilter.php', 'langfilter_prepare_body', 10);
    register_hook('plugin_settings',      'addon/langfilter/langfilter.php', 'langfilter_addon_settings');
    register_hook('plugin_settings_post', 'addon/langfilter/langfilter.php', 'langfilter_addon_settings_post');
}
function langfilter_uninstall()
{
    unregister_hook('prepare_body',         'addon/langfilter/langfilter.php', 'langfilter_prepare_body');
    unregister_hook('plugin_settings',      'addon/langfilter/langfilter.php', 'langfilter_addon_settings');
    unregister_hook('plugin_settings_post', 'addon/langfilter/langfilter.php', 'langfilter_addon_settings_post');
}

/* The settings
 * 1st check if somebody logged in is calling
 * 2nd get the current settings
 * 3rd parse a SMARTY3 template, replacing some translateable strings for the form
 */
function langfilter_addon_settings(&$a, &$s)
{
    if (!local_user()) {
        return;
    }

    $enable_checked = (intval(get_pconfig(local_user(), 'langfilter', 'disable')) ? '' : ' checked="checked" ');
    $languages = get_pconfig(local_user(), 'langfilter', 'languages');
    $minconfidence = get_pconfig(local_user(), 'langfilter', 'minconfidence') * 100;
    if (!$languages) {
        $languages = 'en,de,fr,it,es';
    }

    $t = get_markup_template('settings.tpl', 'addon/langfilter/');
    $s .= replace_macros($t, array(
        '$title' => t('Language Filter'),
        '$intro' => t('This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings.'),
        '$enabled' => array('langfilter_enable', t('Use the language filter'), $enable_checked, ''),
        '$languages' => array('langfilter_languages', t('I speak'), $languages, t('List of abbreviations (iso2 codes) for languages you speak, comma separated. For example "de,it".')),
        '$minconfidence' => array('langfilter_minconfidence', t('Minimum confidence in language detection'), $minconfidence, t('Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.')),
        '$submit' => t('Save Settings'),
    ));

    return;
}
/* Save the settings
 * 1st check it's a logged in user calling
 * 2nd check the langfilter form is to be saved
 * 3rd save the settings to the DB for later usage
 */
function langfilter_addon_settings_post(&$a, &$b)
{
    if (!local_user()) {
        return;
    }

    if ($_POST['langfilter-settings-submit']) {
        set_pconfig(local_user(), 'langfilter', 'languages', trim($_POST['langfilter_languages']));
        $enable = ((x($_POST, 'langfilter_enable')) ? intval($_POST['langfilter_enable']) : 0);
        $disable = 1 - $enable;
        set_pconfig(local_user(), 'langfilter', 'disable', $disable);
        $minconfidence = 0 + $_POST['langfilter_minconfidence'];
        if (!$minconfidence) {
            $minconfidence = 0;
        } elseif ($minconfidence < 0) {
            $minconfidence = 0;
        } elseif ($minconfidence > 100) {
            $minconfidence = 100;
        }
        set_pconfig(local_user(), 'langfilter', 'minconfidence', $minconfidence / 100.0);
        info(t('Language Filter Settings saved.').EOL);
    }
}
/* Actually filter postings by their language
 * 1st check if the user wants to filter postings
 * 2nd get the user settings which languages shall be not filtered out
 * 3rd extract the language of a posting
 * 4th if the determined language does not fit to the spoken languages
 *     of the user, then collapse the posting, but provide a link to
 *     expand it again.
 */
function langfilter_prepare_body(&$a, &$b)
{
    $logged_user = local_user();
    if (!$logged_user) {
        return;
    }

    // Never filter own messages
    // TODO: find a better way to extract this
    $logged_user_profile = $a->config['system']['url'].'/profile/'.$a->user['nickname'];
    if ($logged_user_profile == $b['item']['author-link']) {
        return;
    }

    // Don't filter if language filter is disabled
    if (get_pconfig($logged_user, 'langfilter', 'disable')) {
        return;
    }

    $spoken_config = get_pconfig(local_user(), 'langfilter', 'languages');
    $minconfidence = get_pconfig(local_user(), 'langfilter', 'minconfidence');

    // Don't filter if no spoken languages are configured
    if (!$spoken_config) {
        return;
    }
    $spoken_languages = explode(',', $spoken_config);

    // Extract the language of the post
    $opts = $b['item']['postopts'];
    if (!$opts) {
        return;
    } // no options associated to post
    if (!preg_match('/\blang=([^;]*);([^:]*)/', $opts, $matches)) {
        return;
    } // no lang options associated to post

    $lang = $matches[1];
    $confidence = $matches[2];

    // Do not filter if language detection confidence is too low
    if ($minconfidence && $confidence < $minconfidence) {
        return;
    }

    $iso2 = Text_LanguageDetect_ISO639::nameToCode2($lang);

    if (!$iso2) {
        return;
    }
    $spoken = in_array($iso2, $spoken_languages);

    if (!$spoken) {
        $rnd = random_string(8);
        $b['html'] = '<div id="langfilter-wrap-'.$rnd.'" class="fakelink" onclick=openClose(\'langfilter-'.$rnd.'\'); >'.sprintf(t('unspoken language %s - Click to open/close'), $lang).'</div><div id="langfilter-'.$rnd.'" style="display: none; " >'.$b['html'].'</div>';
    }
}
