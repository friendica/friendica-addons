<?php

declare(strict_types=1);

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\DI;

require dirname(__DIR__) . '/bootstrap.php';

DI::config()->set('openidconnect', 'client_id', 'client-id');
DI::config()->set('openidconnect', 'client_secret', 'secret');
DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
DI::cache()->set('openidconnect:provider_config', [
    'authorization_endpoint' => 'https://id.example/authorize',
], 600);

register_shutdown_function(static function (): void {
    $headers = function_exists('xdebug_get_headers') ? xdebug_get_headers() : headers_list();
    $locationHeader = '';

    foreach ($headers as $header) {
        if (str_starts_with($header, 'Location: ')) {
            $locationHeader = $header;
            break;
        }
    }

    echo json_encode([
        'location_header' => $locationHeader,
        'state_write' => DI::cache()->setCalls[1] ?? null,
    ], JSON_THROW_ON_ERROR);
});

$request = new AuthorizationRequest(new ProviderConfiguration());
$request->redirect(false, 'oauth/authorize?client_id=test', true);