<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

final class TestDoubleIsolationTest extends AddonTestCase
{
	public function testShippedTestDoublesDoNotDeclareCoreFriendicaNamespaces(): void
	{
		$stubFiles = glob(__DIR__ . '/../stubs/*.php');
		self::assertIsArray($stubFiles);
		self::assertNotSame([], $stubFiles);

		$forbiddenNamespaceFragments = [
			'namespace Friendica\\Database;',
			'namespace Friendica\\Model;',
			'namespace Friendica\\Core;',
			'namespace Friendica;',
		];

		foreach ($stubFiles as $stubFile) {
			$contents = file_get_contents($stubFile);
			self::assertIsString($contents);

			foreach ($forbiddenNamespaceFragments as $fragment) {
				self::assertStringNotContainsString(
					$fragment,
					$contents,
					basename($stubFile) . ' must not ship runtime-shadowing Friendica core namespaces.'
				);
			}
		}
	}
}