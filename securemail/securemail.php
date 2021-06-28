<?php
/**
 * Name: Secure Mail
 * Description: Send notification mail encrypted with user-defined public GPG key
 * Version: 2.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrixxm>
 */

use Friendica\Addon\securemail\SecureTestEmail;
use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Object\EMail\IEmail;

require_once __DIR__ . '/vendor/autoload.php';

function securemail_install()
{
	Hook::register('addon_settings', 'addon/securemail/securemail.php', 'securemail_settings');
	Hook::register('addon_settings_post', 'addon/securemail/securemail.php', 'securemail_settings_post', 10);

	Hook::register('emailer_send_prepare', 'addon/securemail/securemail.php', 'securemail_emailer_send_prepare', 10);

	Logger::log('installed securemail');
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

	$enable = intval(DI::pConfig()->get(local_user(), 'securemail', 'enable'));
	$publickey = DI::pConfig()->get(local_user(), 'securemail', 'pkey');

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/securemail/');

	$s .= Renderer::replaceMacros($t, [
		'$title' => DI::l10n()->t('"Secure Mail" Settings'),
		'$submit' => DI::l10n()->t('Save Settings'),
		'$test' => DI::l10n()->t('Save and send test'), //NOTE: update also in 'post'
		'$enable' => ['securemail-enable', DI::l10n()->t('Enable Secure Mail'), $enable, ''],
		'$publickey' => ['securemail-pkey', DI::l10n()->t('Public key'), $publickey, DI::l10n()->t('Your public PGP key, ascii armored format')]
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
		DI::pConfig()->set(local_user(), 'securemail', 'pkey', trim($_POST['securemail-pkey']));
		$enable = (!empty($_POST['securemail-enable']) ? 1 : 0);
		DI::pConfig()->set(local_user(), 'securemail', 'enable', $enable);

		if ($_POST['securemail-submit'] == DI::l10n()->t('Save and send test')) {

			$res = DI::emailer()->send(new SecureTestEmail(DI::app(), DI::config(), DI::pConfig(), DI::baseUrl()));

			// revert to saved value
			DI::pConfig()->set(local_user(), 'securemail', 'enable', $enable);

			if ($res) {
				info(DI::l10n()->t('Test email sent') . EOL);
			} else {
				notice(DI::l10n()->t('There was an error sending the test email') . EOL);
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
 * @param IEmail $email Email
 *
 * @see   App
 */
function securemail_emailer_send_prepare(App &$a, IEmail &$email)
{
	if (empty($email->getRecipientUid())) {
		return;
	}

	$uid = $email->getRecipientUid();

	$enable_checked = DI::pConfig()->get($uid, 'securemail', 'enable');
	if (!$enable_checked) {
		DI::logger()->debug('No securemail enabled.');
		return;
	}

	$public_key_ascii = DI::pConfig()->get($uid, 'securemail', 'pkey');

	preg_match('/-----BEGIN ([A-Za-z ]+)-----/', $public_key_ascii, $matches);
	$marker = empty($matches[1]) ? 'MESSAGE' : $matches[1];
	$public_key = OpenPGP::unarmor($public_key_ascii, $marker);

	$key = OpenPGP_Message::parse($public_key);

	$data = new OpenPGP_LiteralDataPacket($email->getMessage(true), [
		'format' => 'u',
		'filename' => 'encrypted.gpg'
	]);

	try {
		$encrypted = OpenPGP_Crypt_Symmetric::encrypt($key, new OpenPGP_Message([$data]));
		$armored_encrypted = wordwrap(
			OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE'),
			64,
			"\n",
			true
		);

		$email = $email->withMessage($armored_encrypted, null);

	} catch (Exception $e) {
		DI::logger()->warning('Encryption failed.', ['email' => $email, 'exception' => $e]);
	}
}
