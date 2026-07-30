<?php

// Copyright (C) 2010-2026, the Friendica project
// SPDX-FileCopyrightText: 2010-2026 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace FriendicaAddon\convert\Tests\Unit;

use UnitConvertor;
use PHPUnit\Framework\TestCase;

class UnitConvertorTest extends TestCase
{
	public function testClassExists(): void
	{
		self::assertTrue(class_exists(UnitConvertor::class));
	}
}
