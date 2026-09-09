<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Route;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\BaseModule;
use Friendica\Database\DBA;
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
		if (!BaseModule::checkFormSecurityToken('openidconnect_unlink', SettingsHook::UNLINK_TOKEN_FIELD)) {
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
			if ($this->hasOnlyGeneratedPassword($uid)) {
				DI::sysmsg()->addNotice(DI::l10n()->t('Please set a password for your account before unlinking OpenID Connect, otherwise you will not be able to log in anymore.'));
				DI::baseUrl()->redirect('settings/account');
				return;
			}

			$this->accountLinker->unlink($uid);
			DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account link removed.'));
		} else {
			DI::sysmsg()->addNotice(DI::l10n()->t('No OpenID Connect link found for this account.'));
		}

		DI::baseUrl()->redirect('settings/account');
	}

	/**
	 * True if the account was auto-created by OpenID Connect and the user
	 * never replaced the random, never-shown password it was created with.
	 */
	private function hasOnlyGeneratedPassword(int $uid): bool
	{
		$generatedHash = DI::pConfig()->get($uid, 'openidconnect', 'oidc_generated_password_hash', '');
		if ($generatedHash === '') {
			return false;
		}

		$user = DBA::selectFirst('user', ['password'], ['uid' => $uid]);

		return $user && $user['password'] === $generatedHash;
	}
}
