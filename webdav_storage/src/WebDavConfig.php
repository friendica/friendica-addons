<?php

namespace Friendica\Addon\webdav_storage\src;

use Friendica\Core\Config\IConfig;
use Friendica\Core\L10n;
use Friendica\Model\Storage\IStorageConfiguration;
use Friendica\Network\HTTPClientOptions;
use Friendica\Network\IHTTPClient;

/**
 * The WebDav Backend Storage configuration class
 */
class WebDavConfig implements IStorageConfiguration
{
	const NAME = 'WebDav';

	/** @var L10n */
	private $l10n;

	/** @var IConfig */
	private $config;

	/** @var string */
	private $url;

	/** @var IHTTPClient */
	private $client;

	/** @var array */
	private $authOptions;

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @return array
	 */
	public function getAuthOptions(): array
	{
		return $this->authOptions;
	}

	public function __construct(L10n $l10n, IConfig $config, IHTTPClient $client)
	{
		$this->l10n   = $l10n;
		$this->config = $config;
		$this->client = $client;

		$this->authOptions = null;

		if (!empty($this->config->get('webdav', 'username'))) {
			$this->authOptions = [
				$this->config->get('webdav', 'username'),
				(string)$this->config->get('webdav', 'password', ''),
				$this->config->get('webdav', 'auth_type', 'basic')
			];
		}

		$this->url = $this->config->get('webdav', 'url');
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions(): array
	{
		$auths = [
			''       => 'None',
			'basic'  => 'Basic',
			'digest' => 'Digest',
		];

		return [
			'url' => [
				'input',
				$this->l10n->t('URL'),
				$this->url,
				$this->l10n->t('URL to the Webdav endpoint, where files can be saved'),
				true
			],
			'username' => [
				'input',
				$this->l10n->t('Username'),
				$this->config->get('webdav', 'username', ''),
				$this->l10n->t('Username to authenticate to the Webdav endpoint')
			],
			'password' => [
				'password',
				$this->l10n->t('Password'),
				$this->config->get('webdav', 'username', ''),
				$this->l10n->t('Password to authenticate to the Webdav endpoint')
			],
			'auth_type' => [
				'select',
				$this->l10n->t('Authentication type'),
				$this->config->get('webdav', 'auth_type', ''),
				$this->l10n->t('authentication type to the Webdav endpoint'),
				$auths,
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function saveOptions(array $data): array
	{
		$url      = $data['url']      ?? '';
		$username = $data['username'] ?? '';
		$password = $data['password'] ?? '';

		$auths = [
			''       => 'None',
			'basic'  => 'Basic',
			'digest' => 'Digest',
		];

		$authType = $data['auth_type'] ?? '';
		if (!key_exists($authType, $auths)) {
			return [
				'auth_type' => $this->l10n->t('Authentication type is invalid.'),
			];
		}

		$options = null;

		if (!empty($username)) {
			$options = [
				$username,
				$password,
				$authType
			];
		}

		if (!$this->client->head($url, [HTTPClientOptions::AUTH => $options])->isSuccess()) {
			return [
				'url' => $this->l10n->t('url is either invalid or not reachable'),
			];
		}

		$this->config->set('webdav', 'url', $url);
		$this->config->set('webdav', 'username', $username);
		$this->config->set('webdav', 'password', $password);
		$this->config->set('webdav', 'auth_type', $authType);

		$this->url = $url;

		return [];
	}
}
