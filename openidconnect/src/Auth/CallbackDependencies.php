<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;

final readonly class CallbackDependencies
{
    public function __construct(
        public AuthorizationRequest $authorizationRequest,
        public TokenClient $tokenClient,
        public CallbackIdentityVerifier $identityVerifier,
        public CallbackLinkCompleter $linkCompleter,
        public UserProvisioner $userProvisioner,
    ) {
    }
}
