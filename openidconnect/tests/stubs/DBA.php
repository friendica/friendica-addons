<?php

declare(strict_types=1);

namespace Friendica\Database;

final class DBA
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $users = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $contacts = [];

    private static int $lastInsertId = 0;

    public static function resetTestState(): void
    {
        self::$users = [];
        self::$contacts = [];
        self::$lastInsertId = 0;
    }

    public static function selectFirst(string $table, array $fields, array $condition): array
    {
        if ($table === 'user') {
            foreach (self::$users as $uid => $row) {
                if (isset($condition['uid']) && (int)$condition['uid'] !== $uid) {
                    continue;
                }

                if (array_key_exists('openid', $condition) && (string)($row['openid'] ?? '') !== (string)$condition['openid']) {
                    continue;
                }

                if (array_key_exists('email', $condition) && (string)($row['email'] ?? '') !== (string)$condition['email']) {
                    continue;
                }

                return self::projectFields(array_merge(['uid' => $uid], $row), $fields);
            }

            return [];
        }

        if ($table === 'contact') {
            foreach (self::$contacts as $id => $row) {
                if (isset($condition['uid']) && (int)$condition['uid'] !== (int)($row['uid'] ?? 0)) {
                    continue;
                }

                if (array_key_exists('self', $condition) && (bool)$condition['self'] !== (bool)($row['self'] ?? false)) {
                    continue;
                }

                return self::projectFields(array_merge(['id' => $id], $row), $fields);
            }

            return [];
        }

        return [];
    }

    public static function exists(string $table, array $condition): bool
    {
        return self::selectFirst($table, [], $condition) !== [];
    }

    public static function update(string $table, array $fields, array $condition): bool
    {
        if ($table === 'contact') {
            $id = (int)($condition['id'] ?? 0);
            if ($id > 0) {
                self::$contacts[$id] = array_merge(self::$contacts[$id] ?? ['id' => $id], $fields);
            }

            return true;
        }

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

    public static function transaction(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public static function lastInsertId(): int
    {
        return self::$lastInsertId;
    }

    public static function createUser(array $fields): array
    {
        $uid = self::$lastInsertId + 1;
        self::$lastInsertId = $uid;
        self::$users[$uid] = array_merge(['uid' => $uid], $fields);

        return self::$users[$uid];
    }

    public static function seedUser(array $fields): void
    {
        $uid = (int)($fields['uid'] ?? (self::$lastInsertId + 1));
        self::$lastInsertId = max(self::$lastInsertId, $uid);
        self::$users[$uid] = array_merge(['uid' => $uid], $fields);
    }

    public static function seedContact(array $fields): void
    {
        $id = (int)($fields['id'] ?? (count(self::$contacts) + 1));
        self::$contacts[$id] = array_merge(['id' => $id], $fields);
    }

    private static function projectFields(array $row, array $fields): array
    {
        if ($fields === []) {
            return $row;
        }

        $projected = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $row)) {
                $projected[$field] = $row[$field];
            }
        }

        return $projected;
    }
}
