<?php
/**
 * Name: PHP Mailer SMTP
 * Description: Connects to a SMTP server based on the config
 * Version: 0.1
 * Author: Marcus Mueller
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function phpmailer_install()
{
	Addon::registerHook(
		'emailer_send_prepare',
		__FILE__,
		'phpmailer_emailer_send_prepare'
	);
}

function phpmailer_uninstall()
{
	Addon::unregisterHook(
		'emailer_send_prepare',
		__FILE__,
		'phpmailer_emailer_send_prepare'
	);
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
			/*
			// Enable verbose debug output
			$mail->SMTPDebug = 2;
			*/
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
				$mail->setFrom(Config::get('phpmailer', 'smtp_from'), Config::get('config', 'sitename'));
			}
		}

		// subject
		$mail->Subject = $b['messageSubject'];

		// add text
		$mail->AltBody = $b['textVersion'];

		if (!empty($b['toEmail'])) {
			$mail->addAddress($b['toEmail']);
		}

		// html version
		if (!empty($b['htmlVersion'])) {
			$mail->isHTML(true);
			$mail->Body = $b['htmlVersion'];
		}

		/*
		// additional headers
		if (!empty($b['additionalMailHeader'])) {
			$mail->addCustomHeader($b['additionalMailHeader']);
		}
		*/

		$mail->send();
	} catch (Exception $e) {
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
		die();
	}
}
