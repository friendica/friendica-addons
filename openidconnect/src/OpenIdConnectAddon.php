<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect;

use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\CallbackHandler;
use Friendica\Addon\OpenIdConnect\Lifecycle\AddonLifecycle;
use Friendica\Addon\OpenIdConnect\Presentation\AdminHook;
use Friendica\Addon\OpenIdConnect\Presentation\LoginHook;
use Friendica\Addon\OpenIdConnect\Presentation\ModerationBadgeHook;
use Friendica\Addon\OpenIdConnect\Presentation\SettingsHook;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Route\AccountLinkRoutes;
use Friendica\Addon\OpenIdConnect\Route\RevokeRoute;
use Friendica\Addon\OpenIdConnect\Session\LogoutHandler;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\DI;

final class OpenIdConnectAddon
{
	private AddonLifecycle $lifecycle;
	private ProviderConfiguration $providerConfiguration;
	private AuthorizationRequest $authorizationRequest;
	private CallbackHandler $callbackHandler;
	private RevokeRoute $revokeRoute;
	private AccountLinkRoutes $accountLinkRoutes;
	private LogoutHandler $logoutHandler;
	private LoginHook $loginHook;
	private SettingsHook $settingsHook;
	private ModerationBadgeHook $moderationBadgeHook;
	private AdminHook $adminHook;

	public function __construct(
		?AddonLifecycle $lifecycle = null,
		?ProviderConfiguration $providerConfiguration = null,
		?AuthorizationRequest $authorizationRequest = null,
		?CallbackHandler $callbackHandler = null,
		?RevokeRoute $revokeRoute = null,
		?AccountLinkRoutes $accountLinkRoutes = null,
		?LogoutHandler $logoutHandler = null,
		?LoginHook $loginHook = null,
		?SettingsHook $settingsHook = null,
		?ModerationBadgeHook $moderationBadgeHook = null,
		?AdminHook $adminHook = null,
	) {
		$this->lifecycle = $lifecycle ?? new AddonLifecycle();
		$this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
		$this->authorizationRequest = $authorizationRequest ?? new AuthorizationRequest($this->providerConfiguration);
		$this->callbackHandler = $callbackHandler ?? new CallbackHandler(null, $this->providerConfiguration);
		$this->revokeRoute = $revokeRoute ?? new RevokeRoute($this->providerConfiguration);
		$this->accountLinkRoutes = $accountLinkRoutes ?? new AccountLinkRoutes($this->providerConfiguration, $this->authorizationRequest);
		$this->logoutHandler = $logoutHandler ?? new LogoutHandler($this->providerConfiguration);
		$this->loginHook = $loginHook ?? new LoginHook($this->providerConfiguration);
		$this->settingsHook = $settingsHook ?? new SettingsHook();
		$this->moderationBadgeHook = $moderationBadgeHook ?? new ModerationBadgeHook();
		$this->adminHook = $adminHook ?? new AdminHook();
	}

	public function install(): void
	{
		$this->lifecycle->install();
	}

	public function uninstall(): void
	{
		$this->lifecycle->uninstall();
	}

	public function loadConfig(ConfigFileManager $loader): void
	{
		$this->lifecycle->loadConfig($loader);
	}

	public function dispatch(string $route): void
	{
		switch ($route) {
			case 'auth':
				$this->authorizationRequest->redirect(false, $_GET['return_path'] ?? '');
				break;
			case 'callback':
				$this->callbackHandler->handle($_GET);
				break;
			case 'revoke':
				$this->revokeRoute->handle();
				break;
			case 'link':
				$this->accountLinkRoutes->begin();
				break;
			case 'unlink':
				$this->accountLinkRoutes->unlink();
				break;
			default:
				DI::logger()->warning('openidconnect: unknown route requested', ['route' => $route]);
				break;
		}
	}

	public function beginAccountLink(): void
	{
		$this->accountLinkRoutes->begin();
	}

	public function unlinkAccount(): void
	{
		$this->accountLinkRoutes->unlink();
	}

	public function logout(): void
	{
		$this->logoutHandler->handle();
	}

	public function revoke(): void
	{
		$this->revokeRoute->handle();
	}

	public function ssoInitiate(string &$output): void
	{
		$this->loginHook->append($output);
	}

	public function pageEnd(string &$output): void
	{
		if (!$this->providerConfiguration->isConfigured()) {
			return;
		}

		$this->moderationBadgeHook->append($output);
	}

	public function addonSettings(array &$data): void
	{
		$this->settingsHook->append($data);
	}

	public function addonAdmin(string &$output): void
	{
		$this->adminHook->render($output);
	}

	public function addonAdminPost(array $post): void
	{
		$this->adminHook->save($post);
		DI::baseUrl()->redirect('admin/addons/openidconnect');
	}
}
