<?php
/**
 * Name: PHP Mailer SMTP
 * Description: Connects to a SMTP server based on the config
 * Version: 0.2
 * Author: Marcus Mueller
 * Maintainer: Hypolite Petovan <hypolite@friendica.mrpetovan.com>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;
use Friendica\Object\EMail\IEmail;
use Friendica\Util\ConfigFileLoader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function phpmailer_install()
{
	Hook::register('load_config'         , __FILE__, 'phpmailer_load_config');
	Hook::register('emailer_send_prepare', __FILE__, 'phpmailer_emailer_send_prepare');
}

function phpmailer_load_config(App $a, ConfigFileLoader $loader)
{
	$a->getConfigCache()->load($loader->loadAddonConfig('phpmailer'));
}

/**
 * @param App $a
 * @param IEmail $email
 */
function phpmailer_emailer_send_prepare(App $a, IEmail &$email)
{
	// Passing `true` enables exceptions
	$mailer = new PHPMailer(true);
	try {
		if (DI::config()->get('phpmailer', 'smtp')) {
			// Set mailer to use SMTP
			$mailer->isSMTP();

			// Setup encoding.
			$mailer->CharSet  = 'UTF-8';
			$mailer->Encoding = 'base64';

			// Specify main and backup SMTP servers
			$mailer->Host = DI::config()->get('phpmailer', 'smtp_server');
			$mailer->Port = DI::config()->get('phpmailer', 'smtp_port');

			if (DI::config()->get('system', 'smtp_secure') && DI::config()->get('phpmailer', 'smtp_port_s')) {
				$mailer->SMTPSecure = DI::config()->get('phpmailer', 'smtp_secure');
				$mailer->Port       = DI::config()->get('phpmailer', 'smtp_port_s');
			}

			if (DI::config()->get('phpmailer', 'smtp_username') && DI::config()->get('phpmailer', 'smtp_password')) {
				$mailer->SMTPAuth = true;
				$mailer->Username = DI::config()->get('phpmailer', 'smtp_username');
				$mailer->Password = DI::config()->get('phpmailer', 'smtp_password');
			}

			if (DI::config()->get('phpmailer', 'smtp_from')) {
				$mailer->setFrom(DI::config()->get('phpmailer', 'smtp_from'), $email->getFromName());
			}
		} else {
			$mailer->setFrom($email->getFromAddress(), $email->getFromName());
		}

		// subject
		$mailer->Subject = $email->getSubject();

		if (!empty($email->getToAddress())) {
			$mailer->addAddress($email->getToAddress());
		}

		// html version
		if (!empty($email->getMessage())) {
			$mailer->isHTML(true);
			$mailer->Body    = $email->getMessage();
			$mailer->AltBody = $email->getMessage(true);
		} else {
			// add text
			$mailer->Body = $email->getMessage(true);
		}

		if (!empty($email->getReplyTo())) {
			$mailer->addReplyTo($email->getReplyTo(), $email->getFromName());
		}

		// additional headers
		if (!empty($email->getAdditionalMailHeader())) {
			foreach ($email->getAdditionalMailHeader() as $name => $values) {
				// Skip the "Message-ID" header because PHP-Mailer is using its own
				if ($name == 'Message-Id') {
					continue;
				}
				$mailer->addCustomHeader(trim($name), trim(implode("\n", $values)));
			}
		}

		if ($mailer->send()) {
			$email = null;
		}
	} catch (Exception $e) {
		DI::logger()->error('PHPMailer error', ['email' => $email, 'ErrorInfo' => $mailer->ErrorInfo, 'exception' => $e]);
	}
}
