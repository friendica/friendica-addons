<?php
/**
 * Name: Impressum
 * Description: Addon to add contact information to the about page (/friendica)
 * Version: 1.3
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * License: 3-clause BSD license
 */

use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\ConfigFileLoader;
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
    DI::baseUrl()->redirect('friendica/');
}

function obfuscate_email ($s) {
    $s = str_replace('@','(at)',$s);
    $s = str_replace('.','(dot)',$s);
    return $s;
}
function impressum_footer($a, &$b) {
    $text = ProxyUtils::proxifyHtml(BBCode::convert(DI::config()->get('impressum','footer_text')));

    if (! $text == '') {
        DI::page()['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.DI::baseUrl()->get().'/addon/impressum/impressum.css" media="all" />';
        $b .= '<div class="clear"></div>';
        $b .= '<div id="impressum_footer">'.$text.'</div>';
    }
}

function impressum_load_config(\Friendica\App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('impressum'));
}

function impressum_show($a,&$b) {
    $b .= '<h3>'.DI::l10n()->t('Impressum').'</h3>';
    $owner = DI::config()->get('impressum', 'owner');
    $owner_profile = DI::config()->get('impressum','ownerprofile');
    $postal = ProxyUtils::proxifyHtml(BBCode::convert(DI::config()->get('impressum', 'postal')));
    $notes = ProxyUtils::proxifyHtml(BBCode::convert(DI::config()->get('impressum', 'notes')));
    $email = obfuscate_email( DI::config()->get('impressum','email') );
    if (strlen($owner)) {
        if (strlen($owner_profile)) {
            $tmp = '<a href="'.$owner_profile.'">'.$owner.'</a>';
        } else {
            $tmp = $owner;
        }
        if (strlen($email)) {
            $b .= '<p><strong>'.DI::l10n()->t('Site Owner').'</strong>: '. $tmp .'<br /><strong>'.DI::l10n()->t('Email Address').'</strong>: '.$email.'</p>';
        } else {
            $b .= '<p><strong>'.DI::l10n()->t('Site Owner').'</strong>: '. $tmp .'</p>';
        }
        if (strlen($postal)) {
            $b .= '<p><strong>'.DI::l10n()->t('Postal Address').'</strong><br />'. $postal .'</p>';
        }
        if (strlen($notes)) {
            $b .= '<p>'.$notes.'</p>';
        }
    } else {
        $b .= '<p>'.DI::l10n()->t('The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.').'</p>';
    }
}

function impressum_addon_admin_post (&$a) {
    $owner = (!empty($_POST['owner']) ? Strings::escapeTags(trim($_POST['owner'])) : '');
    $ownerprofile = (!empty($_POST['ownerprofile']) ? Strings::escapeTags(trim($_POST['ownerprofile'])) : '');
    $postal = (!empty($_POST['postal']) ? (trim($_POST['postal'])) : '');
    $notes = (!empty($_POST['notes']) ? (trim($_POST['notes'])) : '');
    $email = (!empty($_POST['email']) ? Strings::escapeTags(trim($_POST['email'])) : '');
    $footer_text = (!empty($_POST['footer_text']) ? (trim($_POST['footer_text'])) : '');
    DI::config()->set('impressum','owner',strip_tags($owner));
    DI::config()->set('impressum','ownerprofile',strip_tags($ownerprofile));
    DI::config()->set('impressum','postal',strip_tags($postal));
    DI::config()->set('impressum','email',strip_tags($email));
    DI::config()->set('impressum','notes',strip_tags($notes));
    DI::config()->set('impressum','footer_text',strip_tags($footer_text));
}
function impressum_addon_admin (&$a, &$o) {
    $t = Renderer::getMarkupTemplate( "admin.tpl", "addon/impressum/" );
    $o = Renderer::replaceMacros($t, [
        '$submit' => DI::l10n()->t('Save Settings'),
        '$owner' => ['owner', DI::l10n()->t('Site Owner'), DI::config()->get('impressum','owner'), DI::l10n()->t('The page operators name.')],
        '$ownerprofile' => ['ownerprofile', DI::l10n()->t('Site Owners Profile'), DI::config()->get('impressum','ownerprofile'), DI::l10n()->t('Profile address of the operator.')],
        '$postal' => ['postal', DI::l10n()->t('Postal Address'), DI::config()->get('impressum','postal'), DI::l10n()->t('How to contact the operator via snail mail. You can use BBCode here.')],
        '$notes' => ['notes', DI::l10n()->t('Notes'), DI::config()->get('impressum','notes'), DI::l10n()->t('Additional notes that are displayed beneath the contact information. You can use BBCode here.')],
        '$email' => ['email', DI::l10n()->t('Email Address'), DI::config()->get('impressum','email'), DI::l10n()->t('How to contact the operator via email. (will be displayed obfuscated)')],
        '$footer_text' => ['footer_text', DI::l10n()->t('Footer note'), DI::config()->get('impressum','footer_text'), DI::l10n()->t('Text for the footer. You can use BBCode here.')],
    ]);
}
