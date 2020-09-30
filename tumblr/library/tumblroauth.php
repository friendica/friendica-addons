<?php

/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * The first PHP Library to support OAuth for Tumblr's REST API.  (Originally for Twitter, modified for Tumblr by Lucas)
 */

use Friendica\Security\OAuth1\OAuthConsumer;
use Friendica\Security\OAuth1\OAuthRequest;
use Friendica\Security\OAuth1\Signature\OAuthSignatureMethod_HMAC_SHA1;
use Friendica\Security\OAuth1\OAuthToken;
use Friendica\Security\OAuth1\OAuthUtil;

/**
 * Tumblr OAuth class
 */
class TumblrOAuth
{
	/* Set up the API root URL. */
	public $host = "https://api.tumblr.com/v2/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30;
	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;
	/* Response format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = TRUE;
	/* Set the useragent. */
	public $useragent = 'TumblrOAuth v0.2.0-beta2';

	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/**
	 * Contains the last HTTP headers returned.
	 * @var array
	 */
	public $http_header;
	/**
	 * Contains the last HTTP request info
	 * @var string
	 */
	public $http_info;

	/** @var OAuthToken */
	private $token;
	/** @var OAuthConsumer */
	private $consumer;
	/** @var \Friendica\Security\OAuth1\Signature\OAuthSignatureMethod_HMAC_SHA1 */
	private $sha1_method;

	/**
	 * Set API URLS
	 */
	function accessTokenURL()
	{
		return 'https://www.tumblr.com/oauth/access_token';
	}

	function authenticateURL()
	{
		return 'https://www.tumblr.com/oauth/authorize';
	}

	function authorizeURL()
	{
		return 'https://www.tumblr.com/oauth/authorize';
	}

	function requestTokenURL()
	{
		return 'https://www.tumblr.com/oauth/request_token';
	}

	function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
	{
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthToken($oauth_token, $oauth_token_secret);
		} else {
			$this->token = null;
		}
	}

	/**
	 * Get a request_token from Tumblr
	 *
	 * @param callback $oauth_callback
	 * @return array
	 */
	function getRequestToken($oauth_callback = null)
	{
		$parameters = [];
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}

		$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * Get the authorize URL
	 *
	 * @param array $token
	 * @param bool $sign_in_with_tumblr
	 * @return string
	 */
	function getAuthorizeURL($token, $sign_in_with_tumblr = TRUE)
	{
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}

		if (empty($sign_in_with_tumblr)) {
			return $this->authorizeURL() . "?oauth_token={$token}";
		} else {
			return $this->authenticateURL() . "?oauth_token={$token}";
		}
	}

	/**
	 * Exchange request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @param bool $oauth_verifier
	 * @return array ("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham")
	 */
	function getAccessToken($oauth_verifier = FALSE)
	{
		$parameters = [];
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}

		$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);

		return $token;
	}

	/**
	 * One time exchange of username and password for access token and secret.
	 *
	 * @param string $username
	 * @param string $password
	 * @return array ("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham",
	 *                "x_auth_expires" => "0")
	 */
	function getXAuthToken($username, $password)
	{
		$parameters = [];
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);

		return $token;
	}

	/**
	 * GET wrapper for oAuthRequest.
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return mixed|string
	 */
	function get($url, $parameters = [])
	{
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}

		return $response;
	}

	/**
	 * POST wrapper for oAuthRequest.
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return mixed|string
	 */
	function post($url, $parameters = [])
	{
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}

		return $response;
	}

	/**
	 * DELETE wrapper for oAuthReqeust.
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return mixed|string
	 */
	function delete($url, $parameters = [])
	{
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}

		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @param string $url
	 * @param string $method
	 * @param array $parameters
	 * @return mixed|string
	 */
	function oAuthRequest($url, $method, $parameters)
	{
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}";
		}

		$request = OAuthRequest::from_consumer_and_token($this->consumer, $method, $url, $parameters, $this->token);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $url
	 * @param string $method
	 * @param mixed  $postfields
	 * @return string API results
	 */
	function http($url, $method, $postfields = null)
	{
		$this->http_info = [];
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
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

	/**
	 * Get the header info to store.
	 *
	 * @param resource $ch
	 * @param string $header
	 * @return int
	 */
	function getHeader($ch, $header)
	{
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}

		return strlen($header);
	}
}
