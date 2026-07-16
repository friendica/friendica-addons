<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Doubles\Model;

use Friendica\Addon\OpenIdConnect\Tests\Doubles\Database\DBA;

final class User
{
    public static ?\Throwable $nextCreateException = null;

    /** @var null|callable(array<string, mixed>): array<string, mixed> */
    public static $createHandler = null;

    public static function resetTestState(): void
    {
        self::$nextCreateException = null;
        self::$createHandler = null;
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public static function create(array $fields): array
    {
        if (self::$nextCreateException !== null) {
            $exception = self::$nextCreateException;
            self::$nextCreateException = null;
            throw $exception;
        }

        if (is_callable(self::$createHandler)) {
            return (self::$createHandler)($fields);
        }

        return DBA::createUser($fields);
    }
}

final class Contact
{
    /** @var array<int, array{0:int,1:string}> */
    public static array $avatarUpdates = [];

    public static function resetTestState(): void
    {
        self::$avatarUpdates = [];
    }

    public static function updateAvatar(int $contactId, string $filePath): void
    {
        self::$avatarUpdates[] = [$contactId, $filePath];
    }
}
