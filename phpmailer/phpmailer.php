<?php

/**
 * Name: PHP Mailer SMTP
 * Description: Connects to a SMTP server based on the config
 * Version: 0.2
 * Author: Marcus Mueller
 * Maintainer: Hypolite Petovan <hypolite@friendica.mrpetovan.com>
 */

use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\Hook;
use Friendica\DI;
use Friendica\Object\EMail\IEmail;
use Friendica\Core\Config\Util\ConfigFileManager;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function phpmailer_install()
{
	Hook::register('load_config', __FILE__, 'phpmailer_load_config');
	Hook::register('emailer_send_prepare', __FILE__, 'phpmailer_emailer_send_prepare', 5);
}

function phpmailer_load_config(ConfigFileManager $loader)
{
	DI::appHelper()->getConfigCache()->load($loader->loadAddonConfig('phpmailer'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

/**
 * Applies the SMTP transport settings to the mailer.
 *
 * @return bool True if SMTP relaying is enabled
 */
function phpmailer_setup_smtp(PHPMailer $mailer, IManageConfigValues $config): bool
{
	if (!$config->get('phpmailer', 'smtp')) {
		return false;
	}

	$mailer->isSMTP();

	$mailer->Host = $config->get('phpmailer', 'smtp_server');
	$mailer->Port = $config->get('phpmailer', 'smtp_port');

	// '' (none), 'ssl' (implicit TLS) or 'tls' (STARTTLS).
	$smtpSecure = strtolower(trim((string) $config->get('phpmailer', 'smtp_secure', '')));
	if (in_array($smtpSecure, [PHPMailer::ENCRYPTION_SMTPS, PHPMailer::ENCRYPTION_STARTTLS], true)) {
		$mailer->SMTPSecure = $smtpSecure;

		// The secure port replaces smtp_port as soon as encryption is enabled.
		if ($config->get('phpmailer', 'smtp_port_s')) {
			$mailer->Port = $config->get('phpmailer', 'smtp_port_s');
		}
	}

	if ($config->get('phpmailer', 'smtp_username') && $config->get('phpmailer', 'smtp_password')) {
		$mailer->SMTPAuth = true;
		$mailer->Username = $config->get('phpmailer', 'smtp_username');
		$mailer->Password = $config->get('phpmailer', 'smtp_password');
	}

	return true;
}

/**
 * @param null|IEmail $email
 */
function phpmailer_emailer_send_prepare(?IEmail &$email)
{
	if ($email === null) {
		return;
	}

	// Passing `true` enables exceptions
	if (phpmailer_send(new PHPMailer(true), $email, DI::config(), DI::logger())) {
		$email = null;
	}
}

/**
 * Composes the mail and hands it to the transport.
 *
 * Nothing thrown in here leaves this function.
 * A single unreachable recipient must not take down the worker that runs the cron hooks.
 */
function phpmailer_send(PHPMailer $mailer, IEmail $email, IManageConfigValues $config, LoggerInterface $logger): bool
{
	try {
		// Setup encoding.
		$mailer->CharSet  = 'UTF-8';
		$mailer->Encoding = 'base64';

		if (phpmailer_setup_smtp($mailer, $config) && $config->get('phpmailer', 'smtp_from')) {
			$mailer->setFrom($config->get('phpmailer', 'smtp_from'), $email->getFromName());
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
				// Set the "Message-ID" header for PHP-Mailer directly
				if (strtolower($name) === 'message-id') {
					// implode all values to one entry, because there's only one value possible
					$mailer->MessageID = trim(implode("", $values));
				} else {
					$mailer->addCustomHeader(trim($name), trim(implode("\n", $values)));
				}
			}
		}

		return $mailer->send();
	} catch (Throwable $throwable) {
		// ErrorInfo carries the SMTP conversation, down to the reply that refused the mail.
		// The stack trace is the same worker frames every time and says nothing about the failure.
		$logger->error('The mail could not be sent', [
			'to'        => $email->getToAddress(),
			'subject'   => $email->getSubject(),
			'ErrorInfo' => $mailer->ErrorInfo,
			'message'   => $throwable->getMessage(),
		]);

		return false;
	}
}
