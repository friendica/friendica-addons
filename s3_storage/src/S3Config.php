<?php

namespace Friendica\Addon\s3_storage\src;

defined('AKEEBAENGINE') or define('AKEEBAENGINE', 1);

use Akeeba\S3\Configuration;
use Akeeba\S3\Connector;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Core\Storage\Capability\ICanConfigureStorage;

/**
 * The S3 Backend Storage configuration class
 */
class S3Config implements ICanConfigureStorage
{
	public const NAME = 'S3Config';

	public const DEFAULT_REGION    = 'us-east-1';
	public const DEFAULT_ENDPOINT  = 's3.amazonaws.com';
	public const DEFAULT_SIGMETHOD = 'v2';
	public const DEFAULT_BUCKET    = 'friendica';

	/** @var string[] */
	public const SIGNATURE_METHODS = ['v2', 'v4'];

	/** @var L10n */
	private $l10n;

	/** @var IManageConfigValues */
	private $config;

	/** @var string */
	private $endpoint;

	/** @var string */
	private $accessKey;

	/** @var string */
	private $secretKey;

	/** @var string */
	private $signatureMethod;

	/** @var string */
	private $bucket;

	/** @var string */
	private $region;

	/** @var bool */
	private $legacy;

	/** @var bool */
	private $dualStack;

	public function __construct(L10n $l10n, IManageConfigValues $config)
	{
		$this->l10n   = $l10n;
		$this->config = $config;

		$this->accessKey       = (string) $this->config->get('s3', 'access_key', '');
		$this->secretKey       = (string) $this->config->get('s3', 'secret_key', '');
		$this->signatureMethod = (string) $this->config->get('s3', 'signature_method', self::DEFAULT_SIGMETHOD);
		$this->bucket          = (string) $this->config->get('s3', 'bucket', self::DEFAULT_BUCKET);
		$this->legacy          = !empty($this->config->get('s3', 'legacy'));
		$this->dualStack       = !empty($this->config->get('s3', 'dual_stack'));
		$this->region          = (string) $this->config->get('s3', 'region', '');
		$this->endpoint        = (string) $this->config->get('s3', 'endpoint', '');
	}

	/**
	 * Returns the whole configuration as a Akeeba compatible configuration instance
	 *
	 * @return Configuration
	 */
	public function getConfig(): Configuration
	{
		return $this->createConfiguration(
			$this->accessKey,
			$this->secretKey,
			$this->endpoint,
			$this->signatureMethod,
			$this->region,
			$this->legacy,
			$this->dualStack,
		);
	}

	/**
	 * Builds an Akeeba configuration.
	 *
	 * The order of the calls matters, because Akeeba resets values on its own:
	 * a custom endpoint forces v2 signatures, switching to v2 signatures clears the region,
	 * and path style access is ignored as long as the endpoint still points to Amazon.
	 */
	private function createConfiguration(string $accessKey, string $secretKey, string $endpoint, string $signatureMethod, string $region, bool $legacy, bool $dualStack): Configuration
	{
		$config = new Configuration($accessKey, $secretKey);

		if ($endpoint !== '') {
			$config->setEndpoint($endpoint);
		}

		if ($signatureMethod !== '') {
			$config->setSignatureMethod($signatureMethod);
		}

		// v2 signatures don't use a region at all, v4 signatures don't accept an empty one.
		if ($config->getSignatureMethod() === 'v4') {
			$config->setRegion($region !== '' ? $region : self::DEFAULT_REGION);
		}

		$config->setUseLegacyPathStyle($legacy);
		$config->setUseDualstackUrl($dualStack);

		return $config;
	}

	protected function createConnector(Configuration $config): Connector
	{
		return new Connector($config);
	}

	public function getBucket(): string
	{
		return $this->bucket;
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions(): array
	{
		$sigMethods = array_combine(self::SIGNATURE_METHODS, self::SIGNATURE_METHODS);

		return [
			'access_key' => [
				'password',
				$this->l10n->t('Access Key'),
				// Never send the stored credentials back to the browser, an empty field keeps them.
				'',
				$this->l10n->t('Set the Access Key of the S3 storage (leave empty to keep the current one)'),
			],
			'secret_key' => [
				'password',
				$this->l10n->t('Secret Key'),
				'',
				$this->l10n->t('Set the Secret Key of the S3 storage (leave empty to keep the current one)'),
			],
			'bucket' => [
				'input',
				$this->l10n->t('Bucket'),
				$this->bucket,
				$this->l10n->t('The S3 Bucket (name), you want to use with Friendica'),
				true,
			],
			'signature_method' => [
				'select',
				$this->l10n->t('Signature Method'),
				$this->signatureMethod,
				$this->l10n->t('Set the signature method to use. Most S3 compatible services need v4.'),
				$sigMethods,
			],
			'endpoint' => [
				'input',
				$this->l10n->t("Amazon S3 compatible endpoint (leave empty for '%s')", self::DEFAULT_ENDPOINT),
				$this->endpoint,
				$this->l10n->t('Set the S3 endpoint. Do NOT use a protocol (You can use a custom endpoint to access third party services which offer S3 compatibility, e.g. OwnCloud, Google Storage etc.)'),
			],
			'region' => [
				'input',
				$this->l10n->t("AWS region (leave empty for '%s')", self::DEFAULT_REGION),
				$this->region,
				$this->l10n->t('The AWS region is mandatory for v4 signatures'),
			],
			'dualstack_url' => [
				'checkbox',
				$this->l10n->t('Use the dualstack URL (which will ship traffic over ipv6 in most cases)'),
				$this->dualStack,
				$this->l10n->t('For more information on these endpoints please read https://docs.aws.amazon.com/AmazonS3/latest/dev/dual-stack-endpoints.html'),
			],
			'legacy' => [
				'checkbox',
				$this->l10n->t('Use legacy, path-style access to the bucket'),
				$this->legacy,
				$this->l10n->t('When it\'s turned off (default) we use virtual hosting stylepaths which are RECOMMENDED BY AMAZON per http://docs.aws.amazon.com/AmazonS3/latest/API/APIRest.html'),
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function saveOptions(array $data): array
	{
		// An empty credential field means "keep the currently stored one".
		$accessKey       = ($data['access_key'] ?? '') !== '' ? (string) $data['access_key'] : $this->accessKey;
		$secretKey       = ($data['secret_key'] ?? '') !== '' ? (string) $data['secret_key'] : $this->secretKey;
		$bucket          = trim((string) ($data['bucket'] ?? ''));
		$endpoint        = trim((string) ($data['endpoint'] ?? ''));
		$region          = trim((string) ($data['region'] ?? ''));
		$signatureMethod = (string) ($data['signature_method'] ?? self::DEFAULT_SIGMETHOD);
		$legacy          = !empty($data['legacy']);
		$dualStack       = !empty($data['dualstack_url']);

		if ($accessKey === '') {
			return ['access_key' => $this->l10n->t('Invalid input')];
		}
		if ($secretKey === '') {
			return ['secret_key' => $this->l10n->t('Invalid input')];
		}
		if ($bucket === '') {
			return ['bucket' => $this->l10n->t('Invalid input')];
		}
		if (!in_array($signatureMethod, self::SIGNATURE_METHODS, true)) {
			return ['signature_method' => $this->l10n->t('Invalid input')];
		}

		try {
			$s3Config = $this->createConfiguration($accessKey, $secretKey, $endpoint, $signatureMethod, $region, $legacy, $dualStack);
		} catch (\Exception $exception) {
			return ['endpoint' => $exception->getMessage()];
		}

		try {
			// A single object listing only needs the permissions the storage backend needs anyway.
			$this->createConnector($s3Config)->getBucket($bucket, null, null, 1);
		} catch (\Exception $exception) {
			return ['bucket' => $exception->getMessage()];
		}

		$this->config->set('s3', 'access_key', $this->accessKey = $accessKey);
		$this->config->set('s3', 'secret_key', $this->secretKey = $secretKey);
		$this->config->set('s3', 'bucket', $this->bucket = $bucket);
		$this->config->set('s3', 'signature_method', $this->signatureMethod = $s3Config->getSignatureMethod());

		$this->legacy = $s3Config->getUseLegacyPathStyle();
		if ($this->legacy) {
			$this->config->set('s3', 'legacy', '1');
		} else {
			$this->config->delete('s3', 'legacy');
		}

		$this->dualStack = $s3Config->getDualstackUrl();
		if ($this->dualStack) {
			$this->config->set('s3', 'dual_stack', '1');
		} else {
			$this->config->delete('s3', 'dual_stack');
		}

		$this->endpoint = $endpoint;
		if ($this->endpoint !== '') {
			$this->config->set('s3', 'endpoint', $this->endpoint);
		} else {
			$this->config->delete('s3', 'endpoint');
		}

		$this->region = $s3Config->getRegion();
		if ($this->region !== '') {
			$this->config->set('s3', 'region', $this->region);
		} else {
			$this->config->delete('s3', 'region');
		}

		return [];
	}
}
