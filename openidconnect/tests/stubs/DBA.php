<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Doubles\Database;

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

    /**
     * @var list<array{table: string, condition: array<string, mixed>}>
     */
    public static array $deleteCalls = [];
    /**
     * @var list<array{table: string, fields: array<string, mixed>, condition: array<string, mixed>}>
     */
    public static array $updateCalls = [];
    public static array $pRows = [];
    public static int $pCallCount = 0;

    public static ?\Throwable $nextSelectException = null;

    public static function resetTestState(): void
    {
        self::$users = [];
        self::$contacts = [];
        self::$lastInsertId = 0;
		self::$deleteCalls = [];
        self::$updateCalls = [];
        self::$pRows = [];
        self::$pCallCount = 0;
        self::$nextSelectException = null;
    }

    public static function p(string $sql, mixed ...$params): object
    {
        self::$pCallCount++;
        return new class(self::$pRows)
        {
            /** @var array<int, array<string, mixed>> */
            private array $rows;
            private int $position = 0;

            /** @param array<int, array<string, mixed>> $rows */
            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function next(): array|false
            {
                if (!isset($this->rows[$this->position])) {
                    return false;
                }

                return $this->rows[$this->position++];
            }
        };
    }

    public static function fetch(object $result): array|false
    {
        if (method_exists($result, 'next')) {
            return $result->next();
        }

        return false;
    }

    public static function close(object $result): void
    {
    }

	public static function delete(string $table, array $condition): bool
	{
		self::$deleteCalls[] = ['table' => $table, 'condition' => $condition];
		return true;
	}

    public static function selectFirst(string $table, array $fields, array $condition): array
    {
        if (self::$nextSelectException !== null) {
            $exception = self::$nextSelectException;
            self::$nextSelectException = null;
            throw $exception;
        }

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
        self::$updateCalls[] = ['table' => $table, 'fields' => $fields, 'condition' => $condition];

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
