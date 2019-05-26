<?php
/**
 * Name: PHP Mailer SMTP
 * Description: Connects to a SMTP server based on the config
 * Version: 0.2
 * Author: Marcus Mueller
 * Maintainer: Hypolite Petovan <hypolite@friendica.mrpetovan.com>
 */

use Friendica\App;
use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Util\Config\ConfigFileLoader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
 * @param array $b
 */
function phpmailer_emailer_send_prepare(App $a, array &$b)
{
	require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
	require_once __DIR__ . '/phpmailer/src/SMTP.php';
	require_once __DIR__ . '/phpmailer/src/Exception.php';

	// Passing `true` enables exceptions
	$mail = new PHPMailer(true);
	try {
		if (Config::get('phpmailer', 'smtp')) {
			// Set mailer to use SMTP
			$mail->isSMTP();

			// Setup encoding.
			$mail->CharSet = 'UTF-8';
			$mail->Encoding = 'base64';

			// Specify main and backup SMTP servers
			$mail->Host = Config::get('phpmailer', 'smtp_server');
			$mail->Port = Config::get('phpmailer', 'smtp_port');

			if (Config::get('system', 'smtp_secure') && Config::get('phpmailer', 'smtp_port_s')) {
				$mail->SMTPSecure = Config::get('phpmailer', 'smtp_secure');
				$mail->Port = Config::get('phpmailer', 'smtp_port_s');
			}

			if (Config::get('phpmailer', 'smtp_username') && Config::get('phpmailer', 'smtp_password')) {
				$mail->SMTPAuth = true;
				$mail->Username = Config::get('phpmailer', 'smtp_username');
				$mail->Password = Config::get('phpmailer', 'smtp_password');
			}

			if (Config::get('phpmailer', 'smtp_from')) {
				$mail->setFrom(Config::get('phpmailer', 'smtp_from'), $b['fromName']);
			}
		} else {
			$mail->setFrom($b['fromEmail'], $b['fromName']);
		}

		// subject
		$mail->Subject = $b['messageSubject'];

		if (!empty($b['toEmail'])) {
			$mail->addAddress($b['toEmail']);
		}

		// html version
		if (!empty($b['htmlVersion'])) {
			$mail->isHTML(true);
			$mail->Body = $b['htmlVersion'];
			$mail->AltBody = $b['textVersion'];
		} else {
			// add text
			$mail->Body = $b['textVersion'];
		}

		if (!empty($b['replyTo'])) {
			$mail->addReplyTo($b['replyTo'], $b['fromName']);
		}

		// additional headers
		if (!empty($b['additionalMailHeader'])) {
			foreach (explode("\n", trim($b['additionalMailHeader'])) as $header_line) {
				list($name, $value) = explode(':', $header_line, 2);
				$mail->addCustomHeader(trim($name), trim($value));
			}
		}

		$b['sent'] = $mail->send();
	} catch (Exception $e) {
		$a->getLogger()->error('PHPMailer error', ['ErrorInfo' => $mail->ErrorInfo, 'code' => $e->getCode(), 'message' => $e->getMessage()]);
	}
}
