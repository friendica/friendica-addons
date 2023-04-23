<?php

/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * The first PHP Library to support OAuth for Tumblr's REST API.  (Originally for Twitter, modified for Tumblr by Lucas)
 */

use Friendica\Core\Logger;
use Friendica\DI;
use Friendica\Security\OAuth1\OAuthConsumer;
use Friendica\Security\OAuth1\OAuthRequest;
use Friendica\Security\OAuth1\Signature\OAuthSignatureMethod_HMAC_SHA1;
use Friendica\Security\OAuth1\OAuthToken;
use Friendica\Security\OAuth1\OAuthUtil;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\ResponseInterface;

/**
 * Tumblr OAuth class
 */
class TumblrOAuth
{
	private $consumer_key;
	private $consumer_secret;
	private $oauth_token;
	private $oauth_token_secret;

	/** @var GuzzleHttp\Client */
	private $client;

	// API URLs
	const accessTokenURL  = 'https://www.tumblr.com/oauth/access_token';
	const authorizeURL    = 'https://www.tumblr.com/oauth/authorize';
	const requestTokenURL = 'https://www.tumblr.com/oauth/request_token';

	function __construct(string $consumer_key, string $consumer_secret, string $oauth_token = '', string $oauth_token_secret = '')
	{
		$this->consumer_key       = $consumer_key;
		$this->consumer_secret    = $consumer_secret;
		$this->oauth_token        = $oauth_token;
		$this->oauth_token_secret = $oauth_token_secret;

		if (empty($this->oauth_token) || empty($this->oauth_token_secret)) {
			return;
		}

		$stack = HandlerStack::create();

		$middleware = new Oauth1([
			'consumer_key'    => $this->consumer_key,
			'consumer_secret' => $this->consumer_secret,
			'token'           => $this->oauth_token,
			'token_secret'    => $this->oauth_token_secret
		]);
		$stack->push($middleware);

		$this->client = new Client([
			'base_uri' => 'https://api.tumblr.com/v2/',
			'handler' => $stack
		]);
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
		if (empty($request)) {
			return [];
		}
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
		if (empty($request)) {
			return [];
		}
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
		$consumer    = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
		$sha1_method = new OAuthSignatureMethod_HMAC_SHA1();

		$request = OAuthRequest::from_consumer_and_token($consumer, 'GET', $url, $parameters, $token);
		$request->sign_request($sha1_method, $consumer, $token);

		$curlResult = DI::httpClient()->get($request->to_url());
		if ($curlResult->isSuccess()) {
			return $curlResult->getBody();
		}
		return '';
	}

	/**
	 * OAuth get from a given url with given parameters
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return stdClass
	 */
	public function get(string $url, array $parameters = []): stdClass
	{
		if (!empty($parameters)) {
			$url .= '?' . http_build_query($parameters);
		}

		try {
			$response = $this->client->get($url, ['auth' => 'oauth']);
		} catch (RequestException $exception) {
			$response = $exception->getResponse();
			Logger::notice('Get failed', ['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
		}

		return $this->formatResponse($response);
	}

	/**
	 * OAuth Post to a given url with given parameters
	 *
	 * @param string $url
	 * @param array $parameter
	 * @return stdClass
	 */
	public function post(string $url, array $parameter): stdClass
	{
		try {
			$response = $this->client->post($url, ['auth' => 'oauth', 'json' => $parameter]);
		} catch (RequestException $exception) {
			$response = $exception->getResponse();
			Logger::notice('Post failed', ['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
		}

		return $this->formatResponse($response);
	}

	/**
	 * Convert the body in the given response to a class
	 *
	 * @param ResponseInterface|null $response
	 * @return stdClass
	 */
	private function formatResponse(ResponseInterface $response = null): stdClass
	{
		if (!is_null($response)) {
			$content = $response->getBody()->getContents();
			if (!empty($content)) {
				$result = json_decode($content);
			}
		}

		if (empty($result) || empty($result->meta)) {
			$result               = new stdClass;
			$result->meta         = new stdClass;
			$result->meta->status = 500;
			$result->meta->msg    = '';
			$result->response     = [];
			$result->errors       = [];
		}
		return $result;
	}
}