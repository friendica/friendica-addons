<?php

// Copyright (C) 2010-2026, the Friendica project
// SPDX-FileCopyrightText: 2010-2026 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace FriendicaAddon\phpmailer\Tests\Unit;

use Friendica\Core\Config\Capability\IManageConfigValues;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class SetupSmtpTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		if (!function_exists('phpmailer_setup_smtp')) {
			require_once __DIR__ . '/../../phpmailer.php';
		}
	}

	private function config(array $values): IManageConfigValues
	{
		$config = $this->createMock(IManageConfigValues::class);
		$config->method('get')->willReturnCallback(fn (string $cat, ?string $key = null, $default = null) => $values[$key] ?? $default);

		return $config;
	}

	public function testSmtpDisabledLeavesTheMailerAlone(): void
	{
		$mailer = new PHPMailer(true);

		self::assertFalse(phpmailer_setup_smtp($mailer, $this->config(['smtp' => false])));
		self::assertSame('mail', $mailer->Mailer);
	}

	public function testPlainSmtpUsesTheConfiguredPort(): void
	{
		$mailer = new PHPMailer(true);

		self::assertTrue(phpmailer_setup_smtp($mailer, $this->config([
			'smtp'        => true,
			'smtp_server' => 'smtp.example.com',
			'smtp_port'   => 25,
			'smtp_port_s' => 465,
		])));

		self::assertSame('smtp', $mailer->Mailer);
		self::assertSame('smtp.example.com', $mailer->Host);
		self::assertSame(25, $mailer->Port);
		self::assertSame('', $mailer->SMTPSecure);
	}

	/**
	 * Regression test for #15997: the encryption was read from the wrong config category,
	 * so it never made it to the mailer.
	 */
	public function testEncryptionIsReadFromTheAddonConfig(): void
	{
		$mailer = new PHPMailer(true);

		phpmailer_setup_smtp($mailer, $this->config([
			'smtp'        => true,
			'smtp_server' => 'smtp.example.com',
			'smtp_port'   => 25,
			'smtp_secure' => 'ssl',
			'smtp_port_s' => 465,
		]));

		self::assertSame(PHPMailer::ENCRYPTION_SMTPS, $mailer->SMTPSecure);
		self::assertSame(465, $mailer->Port);
	}

	public function testStartTlsKeepsItsOwnPort(): void
	{
		$mailer = new PHPMailer(true);

		phpmailer_setup_smtp($mailer, $this->config([
			'smtp'        => true,
			'smtp_server' => 'smtp.example.com',
			'smtp_port'   => 25,
			'smtp_secure' => 'TLS',
			'smtp_port_s' => 587,
		]));

		self::assertSame(PHPMailer::ENCRYPTION_STARTTLS, $mailer->SMTPSecure);
		self::assertSame(587, $mailer->Port);
	}

	public function testUnknownEncryptionIsIgnored(): void
	{
		$mailer = new PHPMailer(true);

		phpmailer_setup_smtp($mailer, $this->config([
			'smtp'        => true,
			'smtp_server' => 'smtp.example.com',
			'smtp_port'   => 25,
			'smtp_secure' => 'starttls',
			'smtp_port_s' => 465,
		]));

		self::assertSame('', $mailer->SMTPSecure);
		self::assertSame(25, $mailer->Port);
	}

	public function testAuthenticationNeedsBothCredentials(): void
	{
		$mailer = new PHPMailer(true);

		phpmailer_setup_smtp($mailer, $this->config([
			'smtp'          => true,
			'smtp_server'   => 'smtp.example.com',
			'smtp_port'     => 25,
			'smtp_username' => 'user',
		]));

		self::assertFalse($mailer->SMTPAuth);

		$mailer = new PHPMailer(true);

		phpmailer_setup_smtp($mailer, $this->config([
			'smtp'          => true,
			'smtp_server'   => 'smtp.example.com',
			'smtp_port'     => 25,
			'smtp_username' => 'user',
			'smtp_password' => 'secret',
		]));

		self::assertTrue($mailer->SMTPAuth);
		self::assertSame('user', $mailer->Username);
		self::assertSame('secret', $mailer->Password);
	}
}
