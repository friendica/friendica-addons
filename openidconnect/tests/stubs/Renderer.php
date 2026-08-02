<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Doubles\Core;

final class Renderer
{
    public static function getMarkupTemplate(string $template, string $basePath = ''): string
    {
        return $basePath . $template;
    }

    public static function replaceMacros(string $template, array $vars): string
    {
        return json_encode([
            'template' => $template,
            'vars' => $vars,
        ], JSON_THROW_ON_ERROR);
    }
}
