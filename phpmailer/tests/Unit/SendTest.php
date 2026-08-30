<?php

// Copyright (C) 2010-2026, the Friendica project
// SPDX-FileCopyrightText: 2010-2026 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace FriendicaAddon\phpmailer\Tests\Unit;

use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Object\EMail\IEmail;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

require_once __DIR__ . '/../../vendor/autoload.php';

class SendTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		if (!function_exists('phpmailer_send')) {
			require_once __DIR__ . '/../../phpmailer.php';
		}
	}

	private function config(): IManageConfigValues
	{
		$config = $this->createMock(IManageConfigValues::class);
		$config->method('get')->willReturnCallback(fn (string $cat, ?string $key = null, $default = null) => $default);

		return $config;
	}

	private function email(): IEmail
	{
		$email = $this->createMock(IEmail::class);
		$email->method('getFromName')->willReturn('Friendica');
		$email->method('getFromAddress')->willReturn('noreply@example.com');
		$email->method('getToAddress')->willReturn('user@maildomain.com');
		$email->method('getSubject')->willReturn('You have been inactive');
		$email->method('getMessage')->willReturn('body');
		$email->method('getReplyTo')->willReturn('');
		$email->method('getAdditionalMailHeader')->willReturn([]);

		return $email;
	}

	/**
	 * A mailer that fails the way the transport does, after the message has been composed.
	 */
	private function failingMailer(Throwable $throwable): PHPMailer
	{
		return new class ($throwable) extends PHPMailer {
			public function __construct(private readonly Throwable $throwable)
			{
				parent::__construct(true);
			}

			public function postSend(): bool
			{
				throw $this->throwable;
			}
		};
	}

	public function testASentMailIsReported(): void
	{
		$mailer = new class () extends PHPMailer {
			public function postSend(): bool
			{
				return true;
			}
		};

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::never())->method('error');

		self::assertTrue(phpmailer_send($mailer, $this->email(), $this->config(), $logger));
	}

	/**
	 * Regression test for the report in
	 * https://forum.friendi.ca/display/bf69967c-146a-9124-384b-abd643568130:
	 * a recipient whose domain is gone made the exception escape into the worker.
	 */
	public function testARejectedRecipientIsCaughtAndLogged(): void
	{
		$message = 'SMTP Error: The following recipients failed: user@maildomain.com: '
			. '<user@maildomain.com>: Recipient address rejected: Domain not found';

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('The mail could not be sent', self::callback(function (array $context) use ($message): bool {
				self::assertSame('user@maildomain.com', $context['to']);
				self::assertSame('You have been inactive', $context['subject']);
				self::assertSame($message, $context['message']);

				return true;
			}));

		$sent = phpmailer_send($this->failingMailer(new Exception($message)), $this->email(), $this->config(), $logger);

		self::assertFalse($sent);
	}

	/**
	 * Not every failure arrives as a PHPMailer exception, and none of them may reach the worker.
	 */
	public function testAnyThrowableIsCaught(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())->method('error');

		$sent = phpmailer_send($this->failingMailer(new RuntimeException('stream broke')), $this->email(), $this->config(), $logger);

		self::assertFalse($sent);
	}
}
