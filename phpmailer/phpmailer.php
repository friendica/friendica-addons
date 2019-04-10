<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Name: PHP Mailer SMTP
 * Description: Connects to a SMTP server based on the config
 * Version: 0.1
 * Author: Marcus Mueller <http://mat.exon.name>
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
		'addon/phpmailer/phpmailer.php',
		'phpmailer_emailer_send_prepare'
	);
}

function phpmailer_uninstall()
{
	Addon::unregisterHook(
		'emailer_send_prepare',
		'addon/phpmailer/phpmailer.php',
		'phpmailer_emailer_send_prepare'
	);
}

function phpmailer_module()
{
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
		if (!empty($a->config['system']['smtp']) && (bool)$a->config['system']['smtp'] === true) {
			// Set mailer to use SMTP
			$mail->isSMTP();
			/*
			// Enable verbose debug output
			$mail->SMTPDebug = 2;
			*/
			// Specify main and backup SMTP servers
			$mail->Host = $a->config['system']['smtp_server'];
			$mail->Port = $a->config['system']['smtp_port'];

			if (!empty($a->config['system']['smtp_secure']) && (bool)$a->config['system']['smtp_secure'] !== '') {
				$mail->SMTPSecure = $a->config['system']['smtp_secure'];
				$mail->Port = $a->config['system']['smtp_port_s'];
			}

			if (!empty($a->config['system']['smtp_username']) && !empty($a->config['system']['smtp_password'])) {
				$mail->SMTPAuth = true;
				$mail->Username = $a->config['system']['smtp_username'];
				$mail->Password = $a->config['system']['smtp_password'];
			}

			if (!empty($a->config['system']['smtp_from']) && !empty($a->config['system']['smtp_domain'])) {
				$mail->setFrom($a->config['system']['smtp_from'], $a->config['sitename']);
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
