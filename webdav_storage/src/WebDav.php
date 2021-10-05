<?php

namespace Friendica\Addon\webdav_storage\src;

use Exception;
use Friendica\Model\Storage\IWritableStorage;
use Friendica\Model\Storage\ReferenceStorageException;
use Friendica\Model\Storage\StorageException;
use Friendica\Network\HTTPClientOptions;
use Friendica\Network\IHTTPClient;
use Friendica\Util\Strings;
use Psr\Log\LoggerInterface;

/**
 * A WebDav Backend Storage class
 */
class WebDav implements IWritableStorage
{
	const NAME = 'WebDav';

	/** @var string */
	private $url;

	/** @var IHTTPClient */
	private $client;

	/** @var LoggerInterface */
	private $logger;

	/** @var array */
	private $authOptions;

	/**
	 * WebDav constructor
	 *
	 * @param string          $url         The full URL to the webdav endpoint (including the subdirectories)
	 * @param array           $authOptions The authentication options for the http calls ( ['username', 'password', 'auth_type'] )
	 * @param IHTTPClient     $client      The http client for communicating with the WebDav endpoint
	 * @param LoggerInterface $logger      The standard logging class
	 */
	public function __construct(string $url, array $authOptions, IHTTPClient $client, LoggerInterface $logger)
	{
		$this->client = $client;
		$this->logger = $logger;

		$this->authOptions = $authOptions;
		$this->url         = $url;
	}

	/**
	 * Split data ref and return file path
	 *
	 * @param string $reference Data reference
	 *
	 * @return string[]
	 */
	private function pathForRef(string $reference): array
	{
		$fold1 = substr($reference, 0, 2);
		$fold2 = substr($reference, 2, 2);
		$file  = substr($reference, 4);

		return [$this->encodePath(implode('/', [$fold1, $fold2, $file])), implode('/', [$fold1, $fold2]), $file];
	}

	/**
	 * URL encodes the given path but keeps the slashes
	 *
	 * @param string $path to encode
	 *
	 * @return string encoded path
	 */
	protected function encodePath(string $path): string
	{
		// slashes need to stay
		return str_replace('%2F', '/', rawurlencode($path));
	}

	/**
	 * Checks if the URL exists
	 *
	 * @param string $uri the URL to check
	 *
	 * @return bool true in case the file/folder exists
	 */
	protected function exists(string $uri): bool
	{
		return $this->client->head($uri, [HTTPClientOptions::AUTH => $this->authOptions])->getReturnCode() == 200;
	}

	/**
	 * Checks if a folder has items left
	 *
	 * @param string $uri the URL to check
	 *
	 * @return bool true in case there are items left in the folder
	 */
	protected function hasItems(string $uri): bool
	{
		$dom               = new \DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
		$root              = $dom->createElementNS('DAV:', 'd:propfind');
		$prop              = $dom->createElement('d:allprop');

		$dom->appendChild($root)->appendChild($prop);

		$opts = [
			HTTPClientOptions::AUTH    => $this->authOptions,
			HTTPClientOptions::HEADERS => ['Depth' => 1, 'Prefer' => 'return-minimal', 'Content-Type' => 'application/xml'],
			HTTPClientOptions::BODY    => $dom->saveXML(),
		];

		$response = $this->client->request('propfind', $uri, $opts);

		$responseDoc = new \DOMDocument();
		$responseDoc->loadXML($response->getBody());
		$responseDoc->formatOutput = true;

		$xpath = new \DOMXPath($responseDoc);
		$xpath->registerNamespace('d', 'DAV');
		$result = $xpath->query('//d:multistatus/d:response');

		// returns at least its own directory, so >1
		return $result !== false && count($result) > 1;
	}

	/**
	 * Creates a DAV-collection (= folder) for the given uri
	 *
	 * @param string $uri The uri for creating a DAV-collection
	 *
	 * @return bool true in case the creation was successful (not immutable!)
	 */
	protected function mkcol(string $uri): bool
	{
		return $this->client->request('mkcol', $uri, [HTTPClientOptions::AUTH => $this->authOptions])
							->getReturnCode() == 200;
	}

	/**
	 * Checks if the given path exists and if not creates it
	 *
	 * @param string $fullPath the full path (the folder structure after the hostname)
	 */
	protected function checkAndCreatePath(string $fullPath): void
	{
		$finalUrl = $this->url . '/' . trim($fullPath, '/');

		if ($this->exists($finalUrl)) {
			return;
		}

		$pathParts = explode('/', trim($fullPath, '/'));
		$path      = '';

		foreach ($pathParts as $part) {
			$path .= '/' . $part;
			$partUrl = $this->url . $path;
			if (!$this->exists($partUrl)) {
				$this->mkcol($partUrl);
			}
		}
	}

	/**
	 * Checks recursively, if paths are empty and deletes them
	 *
	 * @param string $fullPath the full path (the folder structure after the hostname)
	 *
	 * @throws StorageException In case a directory cannot get deleted
	 */
	protected function checkAndDeletePath(string $fullPath): void
	{
		$pathParts = explode('/', trim($fullPath, '/'));
		$partURL   = '/' . implode('/', $pathParts);

		foreach ($pathParts as $pathPart) {
			$checkUrl = $this->url . $partURL;
			if (!empty($partURL) && !$this->hasItems($checkUrl)) {
				$response = $this->client->request('delete', $checkUrl, [HTTPClientOptions::AUTH => $this->authOptions]);

				if (!$response->isSuccess()) {
					if ($response->getReturnCode() == "404") {
						$this->logger->warning('Directory already deleted.', ['uri' => $checkUrl]);
					} else {
						throw new StorageException(sprintf('Unpredicted error for %s: %s', $checkUrl, $response->getError()), $response->getReturnCode());
					}
				}
			}

			$partURL = substr($partURL, 0, -strlen('/' . $pathPart));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $reference): string
	{
		$file = $this->pathForRef($reference);

		$response = $this->client->request('get', $this->url . '/' . $file[0], [HTTPClientOptions::AUTH => $this->authOptions]);

		if (!$response->isSuccess()) {
			throw new ReferenceStorageException(sprintf('Invalid reference %s', $reference));
		}

		return $response->getBody();
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $data, string $reference = ""): string
	{
		if ($reference === '') {
			try {
				$reference = Strings::getRandomHex();
			} catch (Exception $exception) {
				throw new StorageException('Webdav storage failed to generate a random hex', $exception->getCode(), $exception);
			}
		}
		$file = $this->pathForRef($reference);

		$this->checkAndCreatePath($file[1]);

		$opts = [
			HTTPClientOptions::BODY => $data,
			HTTPClientOptions::AUTH => $this->authOptions,
		];

		$this->client->request('put', $this->url . '/' . $file[0], $opts);

		return $reference;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $reference)
	{
		$file = $this->pathForRef($reference);

		$response = $this->client->request('delete', $this->url . '/' . $file[0], [HTTPClientOptions::AUTH => $this->authOptions]);

		if (!$response->isSuccess()) {
			throw new ReferenceStorageException(sprintf('Invalid reference %s', $reference));
		}

		$this->checkAndDeletePath($file[1]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString()
	{
		return self::getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getName(): string
	{
		return self::NAME;
	}
}
