<?php

use Friendica\DI;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'twitteroauth.php';

/*
 * We have to alter the TwitterOAuth class a little bit to work with any GNU Social
 * installation abroad. Basically it's only make the API path variable and be happy.
 *
 * Thank you guys for the Twitter compatible API!
 */
class StatusNetOAuth extends TwitterOAuth
{
	function get_maxlength()
	{
		$config = $this->get($this->host . 'statusnet/config.json');
		return $config->site->textlimit;
	}

	function accessTokenURL()
	{
		return $this->host . 'oauth/access_token';
	}

	function authenticateURL()
	{
		return $this->host . 'oauth/authenticate';
	}

	function authorizeURL()
	{
		return $this->host . 'oauth/authorize';
	}

	function requestTokenURL()
	{
		return $this->host . 'oauth/request_token';
	}

	function __construct($apipath, $consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL)
	{
		parent::__construct($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
		$this->host = $apipath;
	}

	/**
	 * Make an HTTP request
	 *
	 * Copied here from the TwitterOAuth library and complemented by applying the proxy settings of Friendica
	 *
	 * @param string $method
	 * @param string $host
	 * @param string $path
	 * @param array  $parameters
	 *
	 * @return array|object API results
	 */
	function http($url, $method, $postfields = NULL)
	{
		$this->http_info = [];
		$ci = curl_init();
		/* Curl settings */
		$prx = DI::config()->get('system', 'proxy');
		if (strlen($prx)) {
			curl_setopt($ci, CURLOPT_HTTPPROXYTUNNEL, 1);
			curl_setopt($ci, CURLOPT_PROXY, $prx);
			$prxusr = DI::config()->get('system', 'proxyuser');
			if (strlen($prxusr)) {
				curl_setopt($ci, CURLOPT_PROXYUSERPWD, $prxusr);
			}
		}
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, ['Expect:']);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, [$this, 'getHeader']);
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close($ci);
		return $response;
	}
}
