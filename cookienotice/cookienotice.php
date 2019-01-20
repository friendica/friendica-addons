<?php

/**
 * Name: Cookie Notice
 * Description: Configure, show and handle a simple cookie notice
 * Version: 1.0
 * Author: Peter Liebetrau <https://socivitas/profile/peerteer>
 * 
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;

function cookienotice_install()
{
    $file = 'addon/cookienotice/cookienotice.php';
    Addon::registerHook('page_content_top', $file, 'cookienotice_page_content_top');
    Addon::registerHook('page_end', $file, 'cookienotice_page_end');
    Addon::registerHook('addon_settings', $file, 'cookienotice_addon_settings');
    Addon::registerHook('addon_settings_post', $file, 'cookienotice_addon_settings_post');
}

function cookienotice_uninstall()
{
    $file = 'addon/cookienotice/cookienotice.php';
    Addon::unregisterHook('page_content_top', $file, 'cookienotice_page_content_top');
    Addon::unregisterHook('page_end', $file, 'cookienotice_page_end');
    Addon::unregisterHook('addon_settings', $file, 'cookienotice_addon_settings');
    Addon::unregisterHook('addon_settings_post', $file, 'cookienotice_addon_settings_post');
}

function cookienotice_addon_settings(&$a, &$s)
{
    if (!is_site_admin())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="/addon/cookienotice/cookienotice.css" media="all" />' . "\r\n";


    $text = Config::get('cookienotice', 'text');
    if (!$text) {
        $text = '';
    }
    $oktext = Config::get('cookienotice', 'oktext');
    if (!$oktext) {
        $oktext = '';
    }

    $t = get_markup_template("settings.tpl", "addon/cookienotice/");
    $s .= replace_macros($t, [
        '$title'       => L10n::t('"cookienotice" Settings'),
        '$description' => L10n::t('<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button.'),
        '$text'        => ['cookienotice-text', L10n::t('Cookie Usage Notice'), $text, L10n::t('The cookie usage notice')],
        '$oktext'      => ['cookienotice-oktext', L10n::t('OK Button Text'), $oktext, L10n::t('The OK Button text')],
        '$submit'      => L10n::t('Save Settings')
    ]);

    return;
}

function cookienotice_addon_settings_post(&$a, &$b)
{

    if (!is_site_admin())
        return;

    if ($_POST['cookienotice-submit']) {
        Config::set('cookienotice', 'text', trim(strip_tags($_POST['cookienotice-text'])));
        Config::set('cookienotice', 'oktext', trim(strip_tags($_POST['cookienotice-oktext'])));
        info(L10n::t('cookienotice Settings saved.') . EOL);
    }
}

/**
 * adds the link and script to the page head
 * 
 * @param App $a
 * @param string $b - The page html before page_content_top
 */
function cookienotice_page_content_top($a, &$b)
{
    $head                = file_get_contents(__DIR__ . '/templates/head.tpl');
    $a->page['htmlhead'] .= $head;
}

/**
 * adds the html to page end
 * page_end hook function
 * 
 * @param App $a
 * @param string $b - The page html
 */
function cookienotice_page_end($a, &$b)
{

    $text   = (string) Config::get('cookienotice', 'text');
    $oktext = (string) Config::get('cookienotice', 'oktext');

    $page_end_tpl = get_markup_template("cookienotice.tpl", "addon/cookienotice/");

    $page_end = replace_macros($page_end_tpl, [
        '$text'   => $text,
        '$oktext' => $oktext,
    ]);

    $b .= $page_end;
}
