<?php

namespace Friendica\Addon\s3_storage\src;

defined('AKEEBAENGINE') or define('AKEEBAENGINE', 1);

use Akeeba\Engine\Postproc\Connector\S3v4\Configuration;
use Akeeba\Engine\Postproc\Connector\S3v4\Connector;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Core\Storage\Capability\ICanConfigureStorage;
use ParagonIE\HiddenString\HiddenString;

/**
 * The WebDav Backend Storage configuration class
 */
class S3Config implements ICanConfigureStorage
{
	const NAME = 'S3Config';

	const DEFAULT_REGION    = 'us-east-1';
	const DEFAULT_ENDPOINT  = 's3.amazonaws.com';
	const DEFAULT_SIGMETHOD = 'v2';
	const DEFAULT_BUCKET    = 'friendica';

	/** @var L10n */
	private $l10n;

	/** @var IManageConfigValues */
	private $config;

	/** @var string */
	private $endpoint;

	/** @var HiddenString */
	private $accessKey;

	/** @var HiddenString */
	private $secretKey;

	/** @var string */
	private $signatureMethod;

	/** @var ?string */
	private $bucket;

	/** @var ?string */
	private $region;

	/** @var bool */
	private $legacy;

	/** @var bool */
	private $dualStack;

	public function __construct(L10n $l10n, IManageConfigValues $config)
	{
		$this->l10n   = $l10n;
		$this->config = $config;

		$this->accessKey       = new HiddenString($this->config->get('s3', 'access_key', ''));
		$this->secretKey       = new HiddenString($this->config->get('s3', 'secret_key', ''));
		$this->signatureMethod = $this->config->get('s3', 'signature_method', self::DEFAULT_SIGMETHOD);
		$this->bucket          = $this->config->get('s3', 'bucket', self::DEFAULT_BUCKET);
		$this->legacy          = !empty($this->config->get('s3', 'legacy'));
		$this->dualStack       = !empty($this->config->get('s3', 'dual_stack'));
		$this->region          = $this->config->get('s3', 'region');
		$this->endpoint        = $this->config->get('s3', 'endpoint');
	}

	/**
	 * Returns the whole configuration as a Akeeba compatible configuration instance
	 *
	 * @return Configuration
	 */
	public function getConfig(): Configuration
	{
		$config = new Configuration($this->accessKey, $this->secretKey);
		$config->setUseLegacyPathStyle($this->legacy ?? false);
		$config->setUseDualstackUrl($this->dualStack ?? false);

		if (!empty($this->region)) {
			$config->setRegion($this->region);
		}
		if (!empty($this->endpoint)) {
			$config->setEndpoint($this->endpoint);
		}
		if (!empty($this->signatureMethod) && empty($this->endpoint)) {
			$config->setSignatureMethod($this->signatureMethod);
		}

		return $config;
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
		$sigMethods = [
			'v2' => 'v2',
			'v4' => 'v4',
		];

		return [
			'access_key' => [
				'password',
				$this->l10n->t('Access Key'),
				$this->accessKey,
				$this->l10n->t('Set the Access Key of the S3 storage'),
				true,
			],
			'secret_key' => [
				'password',
				$this->l10n->t('Secret Key'),
				$this->secretKey,
				$this->l10n->t('Set the Secret Key of the S3 storage'),
				true,
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
				$this->l10n->t('Set the signature method to use (BEWARE: v4 will be automatically set to v2 in case the endpoint isn\'t amazon)'),
				$sigMethods,
			],
			'endpoint' => [
				'input',
				$this->l10n->t("Amazon S3 compatible endpoint (leave empty for '%s')", self::DEFAULT_ENDPOINT),
				$this->endpoint,
				$this->l10n->t('Set the S3 endpoint. Do NOT use a protocol (You can use a custom endpoint with v2 signatures to access third party services which offer S3 compatibility, e.g. OwnCloud, Google Storage etc.)'),
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
		$feedback = [];

		if (empty($data['access_key']) || empty($data['secret_key']) || empty($data['bucket'])) {
			return [
				'access_key' => $this->l10n->t('Invalid input')
			];
		}

		$s3Config = new Configuration(
			$data['access_key'],
			$data['secret_key']
		);

		$bucket = $data['bucket'];

		if (!empty($data['endpoint'])) {
			try {
				$s3Config->setEndpoint($data['endpoint']);
			} catch (\Exception $exception) {
				$feedback['endpoint'] = $exception->getMessage();
			}
		}
		if (!empty($data['region'])) {
			try {
				$s3Config->setRegion($data['region']);
			} catch (\Exception $exception) {
				$feedback['region'] = $exception->getMessage();
			}
		}

		try {
			$s3Config->setUseLegacyPathStyle((bool)$data['legacy'] ?? false);
		} catch (\Exception $exception) {
			$feedback['legacy'] = $exception->getMessage();
		}
		try {
			$s3Config->setUseDualstackUrl((bool)$data['dualstack_url'] ?? false);
		} catch (\Exception $exception) {
			$feedback['dualstack_url'] = $exception->getMessage();
		}
		try {
			$s3Config->setSignatureMethod($data['signature_method'] ?? self::DEFAULT_SIGMETHOD);
		} catch (\Exception $exception) {
			$feedback['signature_method'] = $this->l10n->t("error '%s', message '%s'", $exception->getCode(), $exception->getMessage());
		}

		try {
			$connector = new Connector($s3Config);
			$buckets   = $connector->listBuckets();
			if (!in_array($bucket, $buckets)) {
				return [
					'bucket' => $this->l10n->t('Bucket %s cannot be not found, possible buckets: %s', $bucket, implode(', ', $buckets))
				];
			}
			$connector->getBucket($bucket);
		} catch (\Exception $exception) {
			return [
				'bucket' => $exception->getMessage()
			];
		}

		$this->config->set('s3', 'access_key', ($this->accessKey = new HiddenString($data['access_key']))->getString());
		$this->config->set('s3', 'secret_key', ($this->secretKey = new HiddenString($data['secret_key']))->getString());
		$this->config->set('s3', 'bucket', ($this->bucket = $bucket));

		if ($s3Config->getUseLegacyPathStyle()) {
			$this->config->set('s3', 'legacy', '1');
		} else {
			$this->config->delete('s3', 'legacy');
		}
		if ($s3Config->getDualstackUrl()) {
			$this->config->set('s3', 'dual_stack', '1');
		} else {
			$this->config->delete('s3', 'dual_stack');
		}
		$this->config->set('s3','signature_method', $s3Config->getSignatureMethod());

		if (!empty($data['endpoint'])) {
			$this->config->set('s3', 'endpoint', $s3Config->getEndpoint());
		} else {
			$this->config->delete('s3', 'endpoint');
		}

		if (!empty($data['region'])) {
			$this->config->set('s3', 'region', $s3Config->getRegion());
		} else {
			$this->config->delete('s3', 'region');
		}

		return $feedback;
	}
}
