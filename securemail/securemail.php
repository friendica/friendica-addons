<?php
/**
 * Name: Secure Mail
 * Description: Send notification mail encrypted with user-defined public GPG key
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */
 require_once 'php-gpg/libs/GPG.php';

 function securemail_install() {
    register_hook('plugin_settings', 'addon/securemail/securemail.php', 'securemail_settings');
    register_hook('plugin_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

    register_hook('emailer_send_prepare',  'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

    logger("installed securemail");
}

function securemail_uninstall() {
    unregister_hook('plugin_settings', 'addon/securemail/securemail.php', 'securemail_settings');
    unregister_hook('plugin_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

    unregister_hook('emailer_send_prepare',  'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

    logger("removed securemail");
}


function securemail_settings(&$a,&$s){
    if(! local_user())
	    return;

    $enable_checked = (intval(get_pconfig(local_user(),'securemail','enable')) ? ' checked="checked"' : '');
    $publickey = get_pconfig(local_user(),'securemail','pkey');

    # all of this should be in a template...
    $s .= '<span id="settings_securemail_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_securemail_expanded\'); openClose(\'settings_securemail_inflated\');">';
    $s .= '<h3>' . t('"Secure Mail" Settings').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_securemail_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_securemail_expanded\'); openClose(\'settings_securemail_inflated\');">';
    $s .= '<h3>' . t('"Secure Mail" Settings').'</h3>';
    $s .= '</span>';
    $s .= '<div id="securemail-wrapper">';

    $s .= '<input id="securemail-enable" type="checkbox" name="securemail-enable" value="1"'.$enable_checked.' />';
    $s .= '<label id="securemail-enable-label" for="securemail-enable">'.t('Enable Secure Mail').'</label>';

    $s .= '<div class="clear"></div>';
    $s .= '<label id="securemail-label" for="securemail-pkey">'.t('Public key').' </label>';
    $s .= '<textarea id="securemail-pkey" name="securemail-pkey">'.$publickey.'</textarea>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="securemail-submit" name="securemail-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
    $s .= '</div>';

    return;
}
function securemail_settings_post(&$a, &$b){

    if(! local_user())
        return;

    if($_POST['securemail-submit']) {
		set_pconfig(local_user(),'securemail','pkey',trim($_POST['securemail-pkey']));
		$enable = ((x($_POST,'securemail-enable')) ? 1 : 0);
		set_pconfig(local_user(),'securemail','enable', $enable);
		info( t('Secure Mail Settings saved.') . EOL);
    }
}

function securemail_emailer_send_prepare(&$a, &$b) {
    if (!x($b,'uid')) return;
	$uid = $b['uid'];

    $enable_checked = get_pconfig($uid,'securemail','enable');
    if (!$enable_checked) return;

    $public_key_ascii = get_pconfig($uid,'securemail','pkey');

    $gpg = new GPG();

    # create an instance of a GPG public key object based on ASCII key
    $pub_key = new GPG_Public_Key($public_key_ascii);

    # using the key, encrypt your plain text using the public key
    $txt_encrypted = $gpg->encrypt($pub_key,$b['textVersion']);
    #$html_encrypted = $gpg->encrypt($pub_key,$b['htmlVersion']);

    $b['textVersion'] = $txt_encrypted;
    $b['htmlVersion'] = null;
}
