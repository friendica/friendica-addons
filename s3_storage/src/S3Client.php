<?php

namespace Friendica\Addon\s3_storage\src;

defined('AKEEBAENGINE') or define('AKEEBAENGINE', 1);

use Akeeba\Engine\Postproc\Connector\S3v4\Configuration;
use Akeeba\Engine\Postproc\Connector\S3v4\Connector;
use Akeeba\Engine\Postproc\Connector\S3v4\Exception\CannotDeleteFile;
use Akeeba\Engine\Postproc\Connector\S3v4\Input;
use Friendica\Core\Storage\Capability\ICanWriteToStorage;
use Friendica\Core\Storage\Exception\StorageException;
use Friendica\Util\Strings;

/**
 * A WebDav Backend Storage class
 */
class S3Client implements ICanWriteToStorage
{
	const NAME = 'S3';

	/** @var Connector */
	protected $connector;

	/** @var string The name of the bucket used for the backend */
	protected $bucket;

	public function __construct(Configuration $config, string $bucket)
	{
		$this->connector = new Connector($config);
		$this->bucket    = $bucket;
	}

	/**
	 * Split data ref and return file path
	 *
	 * @param string $reference Data reference
	 *
	 * @return string
	 */
	private function pathForRef(string $reference): string
	{
		$fold1 = substr($reference, 0, 2);
		$fold2 = substr($reference, 2, 2);
		$file  = substr($reference, 4);

		return implode('/', [$fold1, $fold2, $file]);
	}

	/** {@inheritDoc} */
	public function __toString(): string
	{
		return self::getName();
	}

	/** {@inheritDoc} */
	public static function getName(): string
	{
		return self::NAME;
	}

	/** {@inheritDoc} */
	public function get(string $reference): string
	{
		try {
			return $this->connector->getObject($this->bucket, $this->pathForRef($reference), false);
		} catch (\RuntimeException $exception) {
			throw new StorageException(sprintf('Cannot get reference %s', $reference), $exception->getCode(), $exception);
		}
	}

	/** {@inheritDoc} */
	public function put(string $data, string $reference = ""): string
	{
		if ($reference === '') {
			try {
				$reference = Strings::getRandomHex();
			} catch (\Exception $exception) {
				throw new StorageException('S3 storage failed to generate a random hex', $exception->getCode(), $exception);
			}
		}

		try {
			$input = Input::createFromData($data);
			$this->connector->putObject($input, $this->bucket, $this->pathForRef($reference));
			return $reference;
		} catch (\Exception $exception) {
			throw new StorageException(sprintf('Cannot put data for reference %s', $reference), $exception->getCode(), $exception);
		}
	}

	/** {@inheritDoc} */
	public function delete(string $reference)
	{
		try {
			$this->connector->deleteObject($this->bucket, $this->pathForRef($reference));
		} catch (CannotDeleteFile $exception) {
			throw new StorageException(sprintf('Cannot delete reference %s', $reference), $exception->getCode(), $exception);
		}
	}
}
