<?php

declare(strict_types=1);

namespace Friendica\Database;

final class DBA
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $users = [];

    public static function resetTestState(): void
    {
        self::$users = [];
    }

    public static function selectFirst(string $table, array $fields, array $condition): array
    {
        if ($table !== 'user') {
            return [];
        }

        $openid = (string)($condition['openid'] ?? '');
        if ($openid === '') {
            return [];
        }

        foreach (self::$users as $uid => $row) {
            if ((string)($row['openid'] ?? '') === $openid) {
                return ['uid' => $uid];
            }
        }

        return [];
    }

    public static function update(string $table, array $fields, array $condition): bool
    {
        if ($table !== 'user') {
            return true;
        }

        $uid = (int)($condition['uid'] ?? 0);
        if ($uid <= 0) {
            return true;
        }

        self::$users[$uid] = array_merge(self::$users[$uid] ?? ['uid' => $uid], $fields);
        return true;
    }
}
