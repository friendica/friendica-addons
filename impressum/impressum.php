<?php
/**
 * Name: Impressum
 * Description: Addon to add contact information to the about page (/friendica)
 * Version: 1.3
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * License: 3-clause BSD license
 */

use Friendica\Content\Text\BBCode;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Util\Proxy as ProxyUtils;
use Friendica\Util\Strings;

function impressum_install() {
	Hook::register('load_config', 'addon/impressum/impressum.php', 'impressum_load_config');
    Hook::register('about_hook', 'addon/impressum/impressum.php', 'impressum_show');
    Hook::register('page_end', 'addon/impressum/impressum.php', 'impressum_footer');
    Logger::log("installed impressum Addon");
}

function impressum_uninstall() {
	Hook::unregister('load_config', 'addon/impressum/impressum.php', 'impressum_load_config');
    Hook::unregister('about_hook', 'addon/impressum/impressum.php', 'impressum_show');
    Hook::unregister('page_end', 'addon/impressum/impressum.php', 'impressum_footer');
    Logger::log("uninstalled impressum Addon");
}

function impressum_module() {
}
function impressum_content() {
    $a = get_app();
    $a->internalRedirect('friendica/');
}

function obfuscate_email ($s) {
    $s = str_replace('@','(at)',$s);
    $s = str_replace('.','(dot)',$s);
    return $s;
}
function impressum_footer($a, &$b) {
    $text = ProxyUtils::proxifyHtml(BBCode::convert(Config::get('impressum','footer_text')));

    if (! $text == '') {
        $a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->getBaseURL().'/addon/impressum/impressum.css" media="all" />';
        $b .= '<div class="clear"></div>';
        $b .= '<div id="impressum_footer">'.$text.'</div>';
    }
}

function impressum_load_config(\Friendica\App $a, Config\ConfigCacheLoader $loader)
{
	$a->getConfig()->loadConfigArray($loader->loadConfigFile('impressum', true));
}

function impressum_show($a,&$b) {
    $b .= '<h3>'.L10n::t('Impressum').'</h3>';
    $owner = Config::get('impressum', 'owner');
    $owner_profile = Config::get('impressum','ownerprofile');
    $postal = ProxyUtils::proxifyHtml(BBCode::convert(Config::get('impressum', 'postal')));
    $notes = ProxyUtils::proxifyHtml(BBCode::convert(Config::get('impressum', 'notes')));
    $email = obfuscate_email( Config::get('impressum','email') );
    if (strlen($owner)) {
        if (strlen($owner_profile)) {
            $tmp = '<a href="'.$owner_profile.'">'.$owner.'</a>';
        } else {
            $tmp = $owner;
        }
        if (strlen($email)) {
            $b .= '<p><strong>'.L10n::t('Site Owner').'</strong>: '. $tmp .'<br /><strong>'.L10n::t('Email Address').'</strong>: '.$email.'</p>';
        } else {
            $b .= '<p><strong>'.L10n::t('Site Owner').'</strong>: '. $tmp .'</p>';
        }
        if (strlen($postal)) {
            $b .= '<p><strong>'.L10n::t('Postal Address').'</strong><br />'. $postal .'</p>';
        }
        if (strlen($notes)) {
            $b .= '<p>'.$notes.'</p>';
        }
    } else {
        $b .= '<p>'.L10n::t('The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.').'</p>';
    }
}

function impressum_addon_admin_post (&$a) {
    $owner = (!empty($_POST['owner']) ? Strings::escapeTags(trim($_POST['owner'])) : '');
    $ownerprofile = (!empty($_POST['ownerprofile']) ? Strings::escapeTags(trim($_POST['ownerprofile'])) : '');
    $postal = (!empty($_POST['postal']) ? (trim($_POST['postal'])) : '');
    $notes = (!empty($_POST['notes']) ? (trim($_POST['notes'])) : '');
    $email = (!empty($_POST['email']) ? Strings::escapeTags(trim($_POST['email'])) : '');
    $footer_text = (!empty($_POST['footer_text']) ? (trim($_POST['footer_text'])) : '');
    Config::set('impressum','owner',strip_tags($owner));
    Config::set('impressum','ownerprofile',strip_tags($ownerprofile));
    Config::set('impressum','postal',strip_tags($postal));
    Config::set('impressum','email',strip_tags($email));
    Config::set('impressum','notes',strip_tags($notes));
    Config::set('impressum','footer_text',strip_tags($footer_text));
    info(L10n::t('Settings updated.'). EOL );
}
function impressum_addon_admin (&$a, &$o) {
    $t = Renderer::getMarkupTemplate( "admin.tpl", "addon/impressum/" );
    $o = Renderer::replaceMacros($t, [
        '$submit' => L10n::t('Save Settings'),
        '$owner' => ['owner', L10n::t('Site Owner'), Config::get('impressum','owner'), L10n::t('The page operators name.')],
        '$ownerprofile' => ['ownerprofile', L10n::t('Site Owners Profile'), Config::get('impressum','ownerprofile'), L10n::t('Profile address of the operator.')],
        '$postal' => ['postal', L10n::t('Postal Address'), Config::get('impressum','postal'), L10n::t('How to contact the operator via snail mail. You can use BBCode here.')],
        '$notes' => ['notes', L10n::t('Notes'), Config::get('impressum','notes'), L10n::t('Additional notes that are displayed beneath the contact information. You can use BBCode here.')],
        '$email' => ['email', L10n::t('Email Address'), Config::get('impressum','email'), L10n::t('How to contact the operator via email. (will be displayed obfuscated)')],
        '$footer_text' => ['footer_text', L10n::t('Footer note'), Config::get('impressum','footer_text'), L10n::t('Text for the footer. You can use BBCode here.')],
    ]);
}
