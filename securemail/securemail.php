<?php
/**
 * Name: Secure Mail
 * Description: Send notification mail encrypted with user-defined public GPG key
 * Version: 2.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */

require_once 'include/Emailer.php';

/* because the fraking openpgp-php is in composer, require libs in composer
 * and then don't use autoloader to load classes... */
$path = __DIR__ . '/vendor/phpseclib/phpseclib/phpseclib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
/* so, we don't use the autoloader and include what we need */
$path = __DIR__ . '/vendor/singpolyma/openpgp-php/lib';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'openpgp.php';
require_once 'openpgp_crypt_symmetric.php';


function securemail_install() {
    register_hook('plugin_settings', 'addon/securemail/securemail.php', 'securemail_settings');
    register_hook('plugin_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

    register_hook('emailer_send_prepare', 'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

    logger('installed securemail');
}

function securemail_uninstall() {
    unregister_hook('plugin_settings', 'addon/securemail/securemail.php', 'securemail_settings');
    unregister_hook('plugin_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

    unregister_hook('emailer_send_prepare', 'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

    logger('removed securemail');
}

/**
 * @brief Build user settings form
 *
 * @link https://github.com/friendica/friendica/blob/develop/doc/Plugins.md#plugin_settings 'plugin_settings' hook
 *
 * @param App $a App instance
 * @param string $s output html
 *
 * @see App
 */
function securemail_settings(App &$a, &$s){
    if (!local_user()) {
        return;
    }

    $enable = intval(get_pconfig(local_user(), 'securemail', 'enable'));
    $publickey = get_pconfig(local_user(), 'securemail', 'pkey');

    $t = get_markup_template('admin.tpl', 'addon/securemail/');

    $s = replace_macros($t, array(
        '$title' => t('"Secure Mail" Settings'),
        '$submit' => t('Save Settings'),
        '$test' => t('Save and send test'), //NOTE: update also in 'post'
        '$enable' => array('securemail-enable', t('Enable Secure Mail'), $enable, ''),
        '$publickey' => array('securemail-pkey', t('Public key'), $publickey, t('Your public PGP key, ascii armored format'), 'rows="10"')
    ));
}

/**
 * @brief Handle data from user settings form
 *
 * @link https://github.com/friendica/friendica/blob/develop/doc/Plugins.md#plugin_settings_post 'plugin_settings_post' hook
 *
 * @param App $a App instance
 * @param array $b hook data
 *
 * @see App
 */
function securemail_settings_post(App &$a, array &$b){

    if (!local_user()) {
        return;
    }

    if ($_POST['securemail-submit']) {
        set_pconfig(local_user(), 'securemail', 'pkey', trim($_POST['securemail-pkey']));
        $enable = ((x($_POST, 'securemail-enable')) ? 1 : 0);
        set_pconfig(local_user(), 'securemail', 'enable', $enable);
        info(t('Secure Mail Settings saved.') . EOL);

        if ($_POST['securemail-submit'] == t('Save and send test')) {
            $sitename = $a->config['sitename'];

            $hostname = $a->get_hostname();
            if (strpos($hostname, ':')) {
                $hostname = substr($hostname, 0, strpos($hostname, ':'));
            }

            $sender_email = $a->config['sender_email'];
            if (empty($sender_email)) {
                $sender_email = 'noreply@' . $hostname;
            }

            $subject = 'Friendica - Secure Mail - Test';
            $message = 'This is a test message from your Friendica Secure Mail addon.';

            $params = array(
                'uid' => local_user(),
                'fromName' => $sitename,
                'fromEmail' => $sender_email,
                'toEmail' => $a->user['email'],
                'messageSubject' => $subject,
                'htmlVersion' => "<p>{$message}</p>",
                'textVersion' => $message,
            );

            // enable addon for test
            set_pconfig(local_user(), 'securemail', 'enable', 1);

            $res = Emailer::send($params);

            // revert to saved value
            set_pconfig(local_user(), 'securemail', 'enable', $enable);

            if ($res) {
                info(t('Test email sent') . EOL);
            } else {
                notice(t('There was an error sending the test email') . EOL);
            }
        }
    }
}

/**
 * @brief Encrypt notification emails text
 *
 * @link https://github.com/friendica/friendica/blob/develop/doc/Plugins.md#emailer_send_prepare 'emailer_send_prepare' hook
 *
 * @param App $a App instance
 * @param array $b hook data
 *
 * @see App
 */
function securemail_emailer_send_prepare(App &$a, array &$b) {
    if (!x($b, 'uid')) {
        return;
    }

    $uid = $b['uid'];

    $enable_checked = get_pconfig($uid, 'securemail', 'enable');
    if (!$enable_checked) {
        return;
    }

    $public_key_ascii = get_pconfig($uid, 'securemail', 'pkey');

    preg_match('/-----BEGIN ([A-Za-z ]+)-----/', $public_key_ascii, $matches);
    $marker = (empty($matches[1])) ? 'MESSAGE' : $matches[1];
    $public_key = OpenPGP::unarmor($public_key_ascii, $marker);

    $key = OpenPGP_Message::parse($public_key);

    $data = new OpenPGP_LiteralDataPacket($b['textVersion'], array(
        'format' => 'u',
        'filename' => 'encrypted.gpg'
    ));
    $encrypted = OpenPGP_Crypt_Symmetric::encrypt($key, new OpenPGP_Message(array($data)));
    $armored_encrypted = wordwrap(
        OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE'),
        64,
        "\n",
        true
    );

    $b['textVersion'] = $armored_encrypted;
    $b['htmlVersion'] = null;
}


/**
 * add addon composer autoloader maps to system autoloader

function securemail_autoloader() {

    $loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

    $map = require __DIR__ . '/vendor/composer/autoload_namespaces.php';
    foreach ($map as $namespace => $path) {
        $loader->set($namespace, $path);
    }

    $map = require __DIR__ . '/vendor/composer/autoload_psr4.php';
    foreach ($map as $namespace => $path) {
        $loader->setPsr4($namespace, $path);
    }

    $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';
    if ($classMap) {
        $loader->addClassMap($classMap);
    }
}
securemail_autoloader();

*/
