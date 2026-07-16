<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Route;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\BaseModule;
use Friendica\DI;

final class AccountLinkRoutes
{
	private ProviderConfiguration $providerConfiguration;
	private AuthorizationRequest $authorizationRequest;
	private AccountLinker $accountLinker;
	private $authorizationRedirectFn;

	public function __construct(
		?ProviderConfiguration $providerConfiguration = null,
		?AuthorizationRequest $authorizationRequest = null,
		?AccountLinker $accountLinker = null,
		?callable $authorizationRedirectFn = null,
	) {
		$this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
		$this->authorizationRequest = $authorizationRequest ?? new AuthorizationRequest($this->providerConfiguration);
		$this->accountLinker = $accountLinker ?? new AccountLinker();
		$this->authorizationRedirectFn = $authorizationRedirectFn ?? [$this->authorizationRequest, 'redirect'];
	}

	public function begin(): void
	{
		if (!$this->providerConfiguration->isConfigured()) {
			DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect is not configured.'));
			DI::baseUrl()->redirect('settings/account');
			return;
		}

		$uid = DI::userSession()->getLocalUserId();
		if (!$uid) {
			DI::sysmsg()->addNotice(DI::l10n()->t('You must be logged in to link your account.'));
			DI::baseUrl()->redirect('login');
			return;
		}

		if ($this->accountLinker->get($uid)) {
			DI::sysmsg()->addNotice(DI::l10n()->t('Your account is already linked to an OpenID Connect provider.'));
			DI::baseUrl()->redirect('settings/account');
			return;
		}

		($this->authorizationRedirectFn)(true, 'settings/account');
	}

	public function unlink(): void
	{
		if (!BaseModule::checkFormSecurityToken('openidconnect_unlink')) {
			DI::baseUrl()->redirect('settings/account');
			return;
		}

		$uid = DI::userSession()->getLocalUserId();
		DI::logger()->debug('openidconnect_unlink_account', ['uid' => $uid]);

		if (!$uid) {
			DI::logger()->warning('openidconnect_unlink_account: not logged in');
			DI::baseUrl()->redirect('login');
			return;
		}

		$linkedAccount = $this->accountLinker->get($uid);
		DI::logger()->debug('openidconnect_unlink_account linked', [
			'uid' => (int)$uid,
			'has_link' => !empty($linkedAccount),
			'has_link_id' => !empty($linkedAccount['sub']),
		]);

		if ($linkedAccount) {
			$this->accountLinker->unlink($uid);
			DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account link removed.'));
		} else {
			DI::sysmsg()->addNotice(DI::l10n()->t('No OpenID Connect link found for this account.'));
		}

		DI::baseUrl()->redirect('settings/account');
	}
}
