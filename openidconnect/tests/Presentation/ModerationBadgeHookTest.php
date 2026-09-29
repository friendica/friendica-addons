<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\ModerationBadgeHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;

final class ModerationBadgeHookTest extends DiTestCase
{
	private function mockDbaWithRows(array $rows): Database&\PHPUnit\Framework\MockObject\MockObject
	{
		$dba = $this->mockDba();
		$dba->method('p')->willReturn('stmt');
		$dba->method('fetch')->willReturnOnConsecutiveCalls(...[...$rows, false]);
		$dba->method('close')->willReturn(true);

		return $dba;
	}

	public function testAppendReturnsEarlyWhenRouteIsNotModerationUsers(): void
	{
		$dba = $this->mockDba();
		$dba->expects(self::never())->method('p');
		$this->initDi([Database::class => $dba]);
		$this->args->method('getCommand')->willReturn('settings/addons');

		$output = '<div>initial</div>';
		(new ModerationBadgeHook())->append($output);

		self::assertSame('<div>initial</div>', $output);
	}

	public function testAppendReturnsEarlyWhenNoOidcUsersAreFound(): void
	{
		$this->initDi([Database::class => $this->mockDbaWithRows([])]);
		$this->args->method('getCommand')->willReturn('moderation/users');

		$output = '<div>initial</div>';
		(new ModerationBadgeHook())->append($output);

		self::assertSame('<div>initial</div>', $output);
	}

	public function testAppendInjectsBadgeScriptAndRegistersStylesheetForModerationUsers(): void
	{
		$this->initDi([Database::class => $this->mockDbaWithRows([['uid' => 42], ['uid' => 7]])]);
		$this->page->expects(self::once())->method('registerStylesheet')
			->with(self::stringContains('addon.css'));
		$this->args->method('getCommand')->willReturn('moderation/users');

		$output = '<table id="users"><tbody></tbody></table>';
		(new ModerationBadgeHook())->append($output);

		self::assertStringContainsString('var uids = [42,7];', $output);
		self::assertStringContainsString('openidconnect-sso-badge', $output);
		self::assertStringContainsString('OpenID Connect SSO', $output);
	}
}
