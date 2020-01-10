<?php
/**
 * Name: Secure Mail
 * Description: Send notification mail encrypted with user-defined public GPG key
 * Version: 2.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */

use Friendica\App;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Renderer;
use Friendica\Registry\App as A;
use Friendica\Util\Emailer;

require_once __DIR__ . '/vendor/autoload.php';

function securemail_install()
{
	Hook::register('addon_settings', 'addon/securemail/securemail.php', 'securemail_settings');
	Hook::register('addon_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

	Hook::register('emailer_send_prepare', 'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

	Logger::log('installed securemail');
}

function securemail_uninstall()
{
	Hook::unregister('addon_settings', 'addon/securemail/securemail.php', 'securemail_settings');
	Hook::unregister('addon_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post');

	Hook::unregister('emailer_send_prepare', 'addon/securemail/securemail.php', 'securemail_emailer_send_prepare');

	Logger::log('removed securemail');
}

/**
 * @brief Build user settings form
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#addon_settings 'addon_settings' hook
 *
 * @param App    $a App instance
 * @param string $s output html
 *
 * @see   App
 */
function securemail_settings(App &$a, &$s)
{
	if (!local_user()) {
		return;
	}

	$enable = intval(PConfig::get(local_user(), 'securemail', 'enable'));
	$publickey = PConfig::get(local_user(), 'securemail', 'pkey');

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/securemail/');

	$s .= Renderer::replaceMacros($t, [
		'$title' => L10n::t('"Secure Mail" Settings'),
		'$submit' => L10n::t('Save Settings'),
		'$test' => L10n::t('Save and send test'), //NOTE: update also in 'post'
		'$enable' => ['securemail-enable', L10n::t('Enable Secure Mail'), $enable, ''],
		'$publickey' => ['securemail-pkey', L10n::t('Public key'), $publickey, L10n::t('Your public PGP key, ascii armored format'), 'rows="10"']
	]);
}

/**
 * @brief Handle data from user settings form
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#addon_settings_post 'addon_settings_post' hook
 *
 * @param App   $a App instance
 * @param array $b hook data
 *
 * @see   App
 */
function securemail_settings_post(App &$a, array &$b)
{
	if (!local_user()) {
		return;
	}

	if ($_POST['securemail-submit']) {
		PConfig::set(local_user(), 'securemail', 'pkey', trim($_POST['securemail-pkey']));
		$enable = (!empty($_POST['securemail-enable']) ? 1 : 0);
		PConfig::set(local_user(), 'securemail', 'enable', $enable);
		info(L10n::t('Secure Mail Settings saved.') . EOL);

		if ($_POST['securemail-submit'] == L10n::t('Save and send test')) {
			$sitename = Config::get('config', 'sitename');

			$hostname = A::baseUrl()->getHostname();
			if (strpos($hostname, ':')) {
				$hostname = substr($hostname, 0, strpos($hostname, ':'));
			}

			$sender_email = Config::get('config', 'sender_email');
			if (empty($sender_email)) {
				$sender_email = 'noreply@' . $hostname;
			}

			$subject = 'Friendica - Secure Mail - Test';
			$message = 'This is a test message from your Friendica Secure Mail addon.';

			$params = [
				'uid' => local_user(),
				'fromName' => $sitename,
				'fromEmail' => $sender_email,
				'toEmail' => $a->user['email'],
				'messageSubject' => $subject,
				'htmlVersion' => "<p>{$message}</p>",
				'textVersion' => $message,
			];

			// enable addon for test
			PConfig::set(local_user(), 'securemail', 'enable', 1);

			$res = Emailer::send($params);

			// revert to saved value
			PConfig::set(local_user(), 'securemail', 'enable', $enable);

			if ($res) {
				info(L10n::t('Test email sent') . EOL);
			} else {
				notice(L10n::t('There was an error sending the test email') . EOL);
			}
		}
	}
}

/**
 * @brief Encrypt notification emails text
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#emailer_send_prepare 'emailer_send_prepare' hook
 *
 * @param App   $a App instance
 * @param array $b hook data
 *
 * @see   App
 */
function securemail_emailer_send_prepare(App &$a, array &$b)
{
	if (empty($b['uid'])) {
		return;
	}

	$uid = $b['uid'];

	$enable_checked = PConfig::get($uid, 'securemail', 'enable');
	if (!$enable_checked) {
		return;
	}

	$public_key_ascii = PConfig::get($uid, 'securemail', 'pkey');

	preg_match('/-----BEGIN ([A-Za-z ]+)-----/', $public_key_ascii, $matches);
	$marker = empty($matches[1]) ? 'MESSAGE' : $matches[1];
	$public_key = OpenPGP::unarmor($public_key_ascii, $marker);

	$key = OpenPGP_Message::parse($public_key);

	$data = new OpenPGP_LiteralDataPacket($b['textVersion'], [
		'format' => 'u',
		'filename' => 'encrypted.gpg'
	]);
	$encrypted = OpenPGP_Crypt_Symmetric::encrypt($key, new OpenPGP_Message([$data]));
	$armored_encrypted = wordwrap(
		OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE'),
		64,
		"\n",
		true
	);

	$b['textVersion'] = $armored_encrypted;
	$b['htmlVersion'] = null;
}
