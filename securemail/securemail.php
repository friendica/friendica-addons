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

	Logger::notice('installed securemail');
}

/**
 * @brief Build user settings form
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#addon_settings 'addon_settings' hook
 *
 * @param array $data
 *
 * @see   App
 */
function securemail_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$enabled   = intval(DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'securemail', 'enable'));
	$publickey = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'securemail', 'pkey');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/securemail/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'   => ['securemail-enable', DI::l10n()->t('Enable Secure Mail'), $enabled],
		'$publickey' => ['securemail-pkey', DI::l10n()->t('Public key'), $publickey, DI::l10n()->t('Your public PGP key, ascii armored format')]
	]);

	$data = [
		'addon'  => 'securemail',
		'title'  => DI::l10n()->t('"Secure Mail" Settings'),
		'html'   => $html,
		'submit' => [
			'securemail-submit' => DI::l10n()->t('Save Settings'),
			'securemail-test'   => DI::l10n()->t('Save and send test'),
		],
	];
}

/**
 * @brief Handle data from user settings form
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#addon_settings_post 'addon_settings_post' hook
 *
 * @param array $b hook data
 *
 * @see   App
 */
function securemail_settings_post(array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['securemail-submit']) || !empty($_POST['securemail-test'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'securemail', 'pkey', trim($_POST['securemail-pkey']));
		$enable = (!empty($_POST['securemail-enable']) ? 1 : 0);
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'securemail', 'enable', $enable);

		if (!empty($_POST['securemail-test'])) {
			$res = DI::emailer()->send(new SecureTestEmail(DI::app(), DI::config(), DI::pConfig(), DI::baseUrl()));

			// revert to saved value
			DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'securemail', 'enable', $enable);

			if ($res) {
				DI::sysmsg()->addInfo(DI::l10n()->t('Test email sent'));
			} else {
				DI::sysmsg()->addNotice(DI::l10n()->t('There was an error sending the test email'));
			}
		}
	}
}

/**
 * @brief Encrypt notification emails text
 *
 * @link  https://github.com/friendica/friendica/blob/develop/doc/Addons.md#emailer_send_prepare 'emailer_send_prepare' hook
 *
 * @param IEmail $email Email
 *
 * @see   App
 */
function securemail_emailer_send_prepare(IEmail &$email)
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
