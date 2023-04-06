<?php

/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * The first PHP Library to support OAuth for Tumblr's REST API.  (Originally for Twitter, modified for Tumblr by Lucas)
 */

use Friendica\DI;
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
	/* Contains the last HTTP status code returned. */
	public $http_code;

	/** @var OAuthConsumer */
	private $consumer;
	/** @var \Friendica\Security\OAuth1\Signature\OAuthSignatureMethod_HMAC_SHA1 */
	private $sha1_method;

	// API URLs
	const accessTokenURL  = 'https://www.tumblr.com/oauth/access_token';
	const authorizeURL    = 'https://www.tumblr.com/oauth/authorize';
	const requestTokenURL = 'https://www.tumblr.com/oauth/request_token';

	function __construct(string $consumer_key, string $consumer_secret)
	{
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer    = new OAuthConsumer($consumer_key, $consumer_secret);
	}

	/**
	 * Get a request_token from Tumblr
	 *
	 * @param string $oauth_callback
	 * @return array
	 */
	function getRequestToken(string $oauth_callback): array
	{
		$request = $this->oAuthRequest(self::requestTokenURL, ['oauth_callback' => $oauth_callback]);
		return OAuthUtil::parse_parameters($request);
	}

	/**
	 * Get the authorize URL
	 *
	 * @param string $oauth_token
	 * @return string
	 */
	function getAuthorizeURL(string $oauth_token): string
	{
		return self::authorizeURL . "?oauth_token={$oauth_token}";
	}

	/**
	 * Exchange request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @param string $oauth_verifier
	 * @param string $request_token
	 * @param string $request_token_secret
	 * @return array ("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham")
	 */
	function getAccessToken(string $oauth_verifier, string $request_token, string $request_token_secret): array
	{
		$token = new OAuthToken($request_token, $request_token_secret);

		$parameters = [];
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}

		$request = $this->oAuthRequest(self::accessTokenURL, $parameters, $token);
		return OAuthUtil::parse_parameters($request);
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @param string     $url
	 * @param array      $parameters
	 * @param OAuthToken $token $name
	 * @return string
	 */
	private function oAuthRequest(string $url, array $parameters, OAuthToken $token = null): string
	{
		$request = OAuthRequest::from_consumer_and_token($this->consumer, 'GET', $url, $parameters, $token);
		$request->sign_request($this->sha1_method, $this->consumer, $token);

		$curlResult = DI::httpClient()->get($request->to_url());
		$this->http_code = $curlResult->getReturnCode();
		if ($curlResult->isSuccess()) {
			return $curlResult->getBody();
		}
		return '';
	}
}
