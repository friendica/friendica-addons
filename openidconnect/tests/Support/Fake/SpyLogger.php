<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support\Fake;

use Psr\Log\AbstractLogger;

/**
 * Records every log call so tests can assert on level and message.
 */
final class SpyLogger extends AbstractLogger
{
    /** @var array<int, array{level: mixed, message: string, context: array}> */
    public array $records = [];

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = ['level' => $level, 'message' => (string)$message, 'context' => $context];
    }

    public function hasRecordMatching(string $level, string $needle): bool
    {
        foreach ($this->records as $record) {
            if ($record['level'] === $level && str_contains($record['message'], $needle)) {
                return true;
            }
        }

        return false;
    }
}
