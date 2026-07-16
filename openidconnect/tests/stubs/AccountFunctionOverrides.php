<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Account;

function tempnam(string $directory, string $prefix): string|false
{
    if (!empty($GLOBALS['forceTempnamFalse'])) {
        return false;
    }

    return \tempnam($directory, $prefix);
}

function realpath(string $path): string|false
{
    if (!empty($GLOBALS['forceRealpathFalse'])) {
        return false;
    }

    return \realpath($path);
}

function get_headers(string $url, bool $associative = false, $context = null): array|false
{
    if (array_key_exists('__test_get_headers_result', $GLOBALS)) {
        $result = $GLOBALS['__test_get_headers_result'];
        unset($GLOBALS['__test_get_headers_result']);
        return $result;
    }

    return false;
}

function file_put_contents(string $filename, mixed $data, int $flags = 0, $context = null): int|false
{
    if (!empty($GLOBALS['forceFilePutContentsFalse'])) {
        return false;
    }

    if (array_key_exists('__test_file_put_contents_fail', $GLOBALS)) {
        unset($GLOBALS['__test_file_put_contents_fail']);
        return false;
    }

    return \file_put_contents($filename, $data, $flags);
}

function unlink(string $filename): bool
{
    if (!empty($GLOBALS['forceUnlinkThrow'])) {
        throw new \RuntimeException('forced unlink failure');
    }

    if (array_key_exists('__test_unlink_fail', $GLOBALS)) {
        unset($GLOBALS['__test_unlink_fail']);
        return false;
    }

    return \unlink($filename);
}
