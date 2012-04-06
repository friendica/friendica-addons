<?php
/**
 * Name: Impressum
 * Description: Plugin to add contact information to the about page (/friendica)
 * Version: 1.1
 * Author: Tobias Diekershoff <https://diekershoff.homeunix.net/friendika/profile/tobias>
 * License: 3-clause BSD license
 */

function impressum_install() {
    register_hook('about_hook', 'addon/impressum/impressum.php', 'impressum_show');
    register_hook('page_end', 'addon/impressum/impressum.php', 'impressum_footer');
    logger("installed impressum plugin");
}

function impressum_uninstall() {
    unregister_hook('about_hook', 'addon/impressum/impressum.php', 'impressum_show');
    unregister_hook('page_end', 'addon/impressum/impressum.php', 'impressum_footer');
    logger("uninstalled impressum plugin");
}
function obfuscate_email ($s) {
    $s = str_replace('@','(at)',$s);
    $s = str_replace('.','(dot)',$s);
    return $s;
}
function impressum_footer($a, &$b) {
    $text = get_config('impressum','footer_text');
    if (! $text == '') {
        $a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/impressum/impressum.css" media="all" />\r\n';
        $b .= '<div id="impressum_footer">'.$text.'</div>';
    }
}
function impressum_show($a,&$b) {
    $b .= '<h3>'.t('Impressum').'</h3>';
    $owner = get_config('impressum', 'owner');
    $owner_profile = get_config('impressum','ownerprofile');
    $postal = get_config('impressum', 'postal');
    $notes = get_config('impressum', 'notes');
    $email = obfuscate_email( get_config('impressum','email') );
    if (strlen($owner)) {
        if (strlen($owner_profile)) {
            $tmp = '<a href="'.$owner_profile.'">'.$owner.'</a>';
        } else {
            $tmp = $owner;
        }
        if (strlen($email)) {
            $b .= '<p><strong>'.t('Site Owner').'</strong>: '. $tmp .'<br /><strong>'.t('Email Address').'</strong>: '.$email.'</p>';
        } else {
            $b .= '<p><strong>'.t('Site Owner').'</strong>: '. $tmp .'</p>';
        }
        if (strlen($postal)) {
            $b .= '<p><strong>'.t('Postal Address').'</strong><br />'. $postal .'</p>';
        }
        if (strlen($notes)) {
            $b .= '<p>'.$notes.'</p>';
        }
    } else {
        $b .= '<p>'.t('The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.').'</p>';
    }
}

function impressum_plugin_admin_post (&$a) {
    $owner = ((x($_POST, 'owner')) ? notags(trim($_POST['owner'])) : '');
    $ownerprofile = ((x($_POST, 'ownerprofile')) ? notags(trim($_POST['ownerprofile'])) : '');
    $postal = ((x($_POST, 'postal')) ? (trim($_POST['postal'])) : '');
    $notes = ((x($_POST, 'notes')) ? (trim($_POST['notes'])) : '');
    $email = ((x($_POST, 'email')) ? notags(trim($_POST['email'])) : '');
    $footer_text = ((x($_POST, 'footer_text')) ? (trim($_POST['footer_text'])) : '');
    set_config('impressum','owner',$owner);
    set_config('impressum','ownerprofile',$ownerprofile);
    set_config('impressum','postal',$postal);
    set_config('impressum','email',$email);
    set_config('impressum','notes',$notes);
    set_config('impressum','footer_text',$footer_text);
    info( t('Settings updated.'). EOL );
}
function impressum_plugin_admin (&$a, &$o) {
    $t = file_get_contents( dirname(__file__). "/admin.tpl" );
    $o = replace_macros($t, array(
        '$submit' => t('Submit'),
        '$owner' => array('owner', t('Site Owner'), get_config('impressum','owner'), t('The page operators name.')),
        '$ownerprofile' => array('ownerprofile', t('Site Owners Profile'), get_config('impressum','ownerprofile'), t('Profile address of the operator.')),
        '$postal' => array('postal', t('Postal Address'), get_config('impressum','postal'), t('How to contact the operator via snail mail.')),
        '$notes' => array('notes', t('Notes'), get_config('impressum','notes'), t('Additional notes that are displayed beneath the contact information.')),
        '$email' => array('email', t('Email Address'), get_config('impressum','email'), t('How to contact the operator via email. (will be displayed obfuscated)')),
        '$footer_text' => array('footer_text', t('Footer note'), get_config('impressum','footer_text'), t('Text for the footer.')),
    ));
}
