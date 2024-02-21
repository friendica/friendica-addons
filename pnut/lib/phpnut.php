<?php

namespace phpnut;

/**
 * phpnut.php
 * pnut.io PHP library
 * https://github.com/pnut-api/phpnut
 *
 * This class handles a lower level type of access to pnut.io. It's ideal
 * for command line scripts and other places where you want full control
 * over what's happening, and you're at least a little familiar with oAuth.
 *
 * Alternatively you can use the EZphpnut class which automatically takes
 * care of a lot of the details like logging in, keeping track of tokens,
 * etc. EZphpnut assumes you're accessing pnut.io via a browser, whereas
 * this class tries to make no assumptions at all.
 */
class phpnut
{
    protected $_baseUrl = 'https://api.pnut.io/v1/';
    protected $_authUrl = 'https://pnut.io/oauth/';

    private $_authPostParams = [];

    // stores the access token after login
    private $_accessToken = null;

    // stores the App access token if we have it
    private $_appAccessToken = null;

    // stores the user ID returned when fetching the auth token
    private $_user_id = null;

    // stores the username returned when fetching the auth token
    private $_username = null;

    // The total number of requests you're allowed within the alloted time period
    private $_rateLimit = null;

    // The number of requests you have remaining within the alloted time period
    private $_rateLimitRemaining = null;

    // The number of seconds remaining in the alloted time period
    private $_rateLimitReset = null;

    // The scope the user has
    private $_scope = null;

    // token scopes
    private $_scopes = [];

    // debug info
    private $_last_request = null;
    private $_last_response = null;

    // ssl certification
    private $_sslCA = null;

    // the callback function to be called when an event is received from the stream
    private $_streamCallback = null;

    // the stream buffer
    private $_streamBuffer = '';

    // stores the curl handler for the current stream
    private $_currentStream = null;

    // stores the curl multi handler for the current stream
    private $_multiStream = null;

    // stores the number of failed connects, so we can back off multiple failures
    private $_connectFailCounter = 0;

    // stores the most recent stream url, so we can re-connect when needed
    private $_streamUrl = null;

    // keeps track of the last time we've received a packet from the api, if it's too long we'll reconnect
    private $_lastStreamActivity = null;

    // stores the headers received when connecting to the stream
    private $_streamHeaders = null;

    // response meta max_id data
    private $_maxid = null;

    // response meta min_id data
    private $_minid = null;

    // response meta more data
    private $_more = null;

    // response stream marker data
    private $_last_marker = null;

    // strip envelope response from returned value
    private $_stripResponseEnvelope = true;

    // if processing stream_markers or any fast stream, decrease $sleepFor
    public $streamingSleepFor = 20000;

    /**
     * Constructs an phpnut PHP object with the specified client ID and
     * client secret.
     * @param string $client_id The client ID you received from pnut.io when
     * creating your app.
     * @param string $client_secret The client secret you received from
     * pnut.io when creating your app.
     */
    public function __construct(?string $client_id_or_token=null, ?string $client_secret=null)
    {
        if (!$client_id_or_token) {
            if (isset($_ENV['PNUT_ACCESS_TOKEN'])) {
                $client_id_or_token = $_ENV['PNUT_ACCESS_TOKEN'];
            } elseif (defined('PNUT_ACCESS_TOKEN')) {
                $client_id_or_token = PNUT_ACCESS_TOKEN;
            } elseif (defined('PNUT_CLIENT_ID') && defined('PNUT_CLIENT_SECRET')) {
                $client_id_or_token = PNUT_CLIENT_ID;
                $client_secret = PNUT_CLIENT_SECRET;
            }
        }

        if (!$client_id_or_token) {
            throw new phpnutException('You must specify your pnut access token or client ID and secret');
        }

        if ($client_id_or_token && $client_secret) {
            $this->_clientId = $client_id_or_token;
            $this->_clientSecret = $client_secret;
        } else {
            $this->_accessToken = $client_id_or_token;
        }

        // if the digicert certificate exists in the same folder as this file,
        // remember that fact for later
        if (file_exists(__DIR__ . '/DigiCertHighAssuranceEVRootCA.pem')) {
            $this->_sslCA = __DIR__ . '/DigiCertHighAssuranceEVRootCA.pem';
        }
    }

    /**
     * Set whether or not to strip Envelope Response (meta) information
     * This option will be deprecated in the future. Is it to allow
     * a stepped migration path between code expecting the old behavior
     * and new behavior. When not stripped, you still can use the proper
     * method to pull the meta information. Please start converting your code ASAP
     */
    public function includeResponseEnvelope(): void
    {
        $this->_stripResponseEnvelope = false;
    }

    /**
     * Construct the proper Auth URL for the user to visit and either grant
     * or not access to your app. Usually you would place this as a link for
     * the user to client, or a redirect to send them to the auth URL.
     * Also can be called after authentication for additional scopes
     * @param string $callbackUri Where you want the user to be directed
     * after authenticating with pnut.io. This must be one of the URIs
     * allowed by your pnut.io application settings.
     * @param array $scope An array of scopes (permissions) you wish to obtain
     * from the user. If you don't specify anything, you'll only receive
     * access to the user's basic profile (the default).
     */
    public function getAuthUrl(?string $callback_uri=null, array|string|null $scope=null): string
    {
        if (empty($this->_clientId)) {
            throw new phpnutException('You must specify your pnut client ID');
        }

        if (is_null($callback_uri)) {
            if (defined('PNUT_REDIRECT_URI')) {
                $callback_uri = PNUT_REDIRECT_URI;
            } elseif (isset($_ENV['PNUT_REDIRECT_URI'])) {
                $callback_uri = $_ENV['PNUT_REDIRECT_URI'];
            } else {
                throw new phpnutException('You must specify your pnut callback URI');
            }
        }

        if (is_null($scope)) {
            if (defined('PNUT_APP_SCOPE')) {
                $scope = PNUT_APP_SCOPE;
            } elseif (isset($_ENV['PNUT_APP_SCOPE'])) {
                $scope = $_ENV['PNUT_APP_SCOPE'];
            } else {
                $scope = 'basic';
            }
        }

        if (is_array($scope)) {
            $scope = implode(',', $scope);
        }

        // construct an authorization url based on our client id and other data
        $data = [
            'client_id'=>$this->_clientId,
            'response_type'=>'code',
            'redirect_uri'=>$callback_uri,
            'scope'=>$scope,
        ];

        $url = $this->_authUrl;
        if ($this->_accessToken) {
            $url .= 'authorize?';
        } else {
            $url .= 'authenticate?';
        }
        $url .= $this->buildQueryString($data);

        // return the constructed url
        return $url;
    }

    /**
     * Call this after they return from the auth page, or anytime you need the
     * token. For example, you could store it in a database and use
     * setAccessToken() later on to return on behalf of the user.
     */
    public function getAccessToken(string $callback_uri)
    {
        // if there's no access token set, and they're returning from
        // the auth page with a code, use the code to get a token
        if (!$this->_accessToken && isset($_GET['code']) && $_GET['code'] !== '') {

            if (empty($this->_clientId) || empty($this->_clientSecret)) {
                throw new phpnutException('You must specify your Pnut client ID and client secret');
            }

            // construct the necessary elements to get a token
            $data = [
                'client_id'=>$this->_clientId,
                'client_secret'=>$this->_clientSecret,
                'grant_type'=>'authorization_code',
                'redirect_uri'=>$callback_uri,
                'code'=>$_GET['code'],
            ];

            // try and fetch the token with the above data
            $res = $this->httpReq(
                'post',
                "{$this->_baseUrl}oauth/access_token",
                $data
            );

            // store it for later
            $this->_accessToken = $res['access_token'];
            $this->_username = $res['username'];
            $this->_user_id = $res['user_id'];
        }

        // return what we have (this may be a token, or it may be nothing)
        return $this->_accessToken;
    }

    /**
     * Check the scope of current token to see if it has required scopes
     * has to be done after a check
     */
    public function checkScopes(array $app_scopes): int|array
    {
        if (count($this->_scopes) === 0) {
            return -1; // _scope is empty
        }
        $missing = [];
        foreach($app_scopes as $scope) {
            if (!in_array($scope, $this->_scopes)) {
                if ($scope === 'public_messages') {
                    // messages works for public_messages
                    if (in_array('messages', $this->_scopes)) {
                        // if we have messages in our scopes
                        continue;
                    }
                }
                $missing[] = $scope;
            }
        }
        // identify the ones missing
        if (count($missing) !== 0) {
            // do something
            return $missing;
        }
        return 0; // 0 missing
     }

    /**
     * Set the access token (eg: after retrieving it from offline storage)
     * @param string $token A valid access token you're previously received
     * from calling getAccessToken().
     */
    public function setAccessToken(?string $token=null): void
    {
        $this->_accessToken = $token;
    }

    /**
     * Deauthorize the current token (delete your authorization from the API)
     * Generally this is useful for logging users out from a web app, so they
     * don't get automatically logged back in the next time you redirect them
     * to the authorization URL.
     */
    public function deauthorizeToken()
    {
        return $this->httpReq('delete', "{$this->_baseUrl}token");
    }
    
    /**
     * Retrieve an app access token from the app.net API. This allows you
     * to access the API without going through the user access flow if you
     * just want to (eg) consume global. App access tokens are required for
     * some actions (like streaming global). DO NOT share the return value
     * of this function with any user (or save it in a cookie, etc). This
     * is considered secret info for your app only.
     * @return string The app access token
     */
    public function getAppAccessToken()
    {    
        if (empty($this->_clientId) || empty($this->_clientSecret)) {
            throw new phpnutException('You must specify your Pnut client ID and client secret');
        }

        // construct the necessary elements to get a token
        $data = [
            'client_id'=>$this->_clientId,
            'client_secret'=>$this->_clientSecret,
            'grant_type'=>'client_credentials',
        ];
        // try and fetch the token with the above data
        $res = $this->httpReq(
            'post',
            "{$this->_baseUrl}oauth/access_token",
            $data
        );
        // store it for later
        $this->_appAccessToken = $res['access_token'];
        $this->_accessToken = $res['access_token'];
        $this->_username = null;
        $this->_user_id = null;
        return $this->_accessToken;
    }

    /**
     * Returns the total number of requests you're allowed within the
     * alloted time period.
     * @see getRateLimitReset()
     */
    public function getRateLimit()
    {
        return $this->_rateLimit;
    }

    /**
     * The number of requests you have remaining within the alloted time period
     * @see getRateLimitReset()
     */
    public function getRateLimitRemaining()
    {
        return $this->_rateLimitRemaining;
    }

    /**
     * The number of seconds remaining in the alloted time period.
     * When this time is up you'll have getRateLimit() available again.
     */
    public function getRateLimitReset()
    {
        return $this->_rateLimitReset;
    }

    /**
     * The scope the user has
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Internal function, parses out important information pnut.io adds
     * to the headers.
     */
    protected function parseHeaders(string $response)
    {
        // take out the headers
        // set internal variables
        // return the body/content
        $this->_rateLimit = null;
        $this->_rateLimitRemaining = null;
        $this->_rateLimitReset = null;
        $this->_scope = null;

        $response = explode("\r\n\r\n", $response, 2);
        $headers = $response[0];

        if ($headers === 'HTTP/1.1 100 Continue') {
            $response = explode("\r\n\r\n", $response[1], 2);
            $headers = $response[0];
        }

        // this is not a good way to parse http headers
        // it will not (for example) take into account multiline headers
        // but what we're looking for is pretty basic, so we can ignore those shortcomings
        $headers = explode("\r\n", $headers);
        foreach ($headers as $header) {
            $header = explode(': ', $header, 2);
            if (count($header) < 2) {
                continue;
            }
            list($k, $v) = $header;
            switch ($k) {
                case 'X-RateLimit-Remaining':
                    $this->_rateLimitRemaining = $v;
                    break;
                case 'X-RateLimit-Limit':
                    $this->_rateLimit = $v;
                    break;
                case 'X-RateLimit-Reset':
                    $this->_rateLimitReset = $v;
                    break;
                case 'X-OAuth-Scopes':
                    $this->_scope = $v;
                    $this->_scopes = explode(',', $v);
                    break;
            }
        }
        return $response[1] ?? null;
    }

    /**
     * Internal function. Used to turn things like TRUE into 1, and then
     * calls http_build_query.
     */
    protected function buildQueryString(array $array): string
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $v = implode(',', $v);
            } elseif ($v === true) {
                $v = '1';
            }
            elseif ($v === false) {
                $v = '0';
            }
            unset($v);
        }
        return http_build_query($array);
    }


    /**
     * Internal function to handle all
     * HTTP requests (POST,PUT,GET,DELETE)
     */
    protected function httpReq(string $act, string $req, string|array $params=[], string $contentType='application/x-www-form-urlencoded')
    {
        $ch = curl_init($req);
        $headers = [];
        if($act !== 'get') {
            curl_setopt($ch, CURLOPT_POST, true);
            // if they passed an array, build a list of parameters from it
            if (is_array($params) && $act !== 'post-raw') {
                $params = $this->buildQueryString($params);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $headers[] = "Content-Type: {$contentType}";
        }
        if($act !== 'post' && $act !== 'post-raw') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($act));
        }
        if($act === 'get' && isset($params['access_token'])) {
            $headers[] = "Authorization: Bearer {$params['access_token']}";
        } elseif ($this->_accessToken) {
            $headers[] = "Authorization: Bearer {$this->_accessToken}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if ($this->_sslCA) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->_sslCA);
        }
        $this->_last_response = curl_exec($ch);
        $this->_last_request = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status === 0) {
            throw new phpnutException("Unable to connect to {$req}");
        }
        if ($this->_last_request === false) {
            if (!curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT)) {
                throw new phpnutException('SSL verification failed, connection terminated.');
            }
        }
        if ($this->_last_response) {
            $response = $this->parseHeaders($this->_last_response);
            if ($response) {
                $response = json_decode($response, true);

                if (isset($response['meta'])) {
                    if (isset($response['meta']['max_id'])) {
                        $this->_maxid = $response['meta']['max_id'];
                        $this->_minid = $response['meta']['min_id'];
                    }
                    if (isset($response['meta']['more'])) {
                        $this->_more = $response['meta']['more'];
                    }
                    if (isset($response['meta']['marker'])) {
                        $this->_last_marker = $response['meta']['marker'];
                    }
                }

                // look for errors
                if (isset($response['error'])) {
                    if (is_array($response['error'])) {
                        throw new phpnutException(
                            $response['error']['message'],
                            $response['error']['code']
                        );
                    } else {
                        throw new phpnutException($response['error']);
                    }
                } 

                // look for response migration errors
                elseif (isset($response['meta'], $response['meta']['error_message'])) {
                    throw new phpnutException(
                        $response['meta']['error_message'],
                        $response['meta']['code']
                    );
                }
            }
        }

        if ($http_status < 200 || $http_status >= 300) {
            throw new phpnutException("HTTP error {$http_status}");
        }

        // if we've received a migration response, handle it and return data only
        elseif ($this->_stripResponseEnvelope && isset($response['meta'], $response['data'])) {
            return $response['data'];
        }

        // else non response migration response, just return it
        elseif (isset($response)) {
            return $response;
        }

        else {
            throw new phpnutException('No response');
        }
    }


    /**
     * Get max_id from last meta response data envelope
     */
    public function getResponseMaxID()
    {
        return $this->_maxid;
    }

    /**
     * Get min_id from last meta response data envelope
     */
    public function getResponseMinID()
    {
        return $this->_minid;
    }

    /**
     * Get more from last meta response data envelope
     */
    public function getResponseMore()
    {
        return $this->_more;
    }

    /**
     * Get marker from last meta response data envelope
     */
    public function getResponseMarker()
    {
        return $this->_last_marker;
    }

    public function getLastRequest()
    {
        return $this->_last_request;
    }
    
    public function getLastResponse()
    {
        return $this->_last_response;
    }

    /**
     * Fetch API configuration object
     * @return array
     */
    public function getConfig()
    {
        return $this->httpReq('get', "{$this->_baseUrl}sys/config");
    }

    /**
     * Fetch basic API statistics
     * @return array
     */
    public function getStats()
    {
        return $this->httpReq('get', "{$this->_baseUrl}sys/stats");
    }

    /**
     * Process user content, message or post text.
     * Mentions and hashtags will be parsed out of the
     * text, as will bare URLs. To create a link in the text without using a
     * bare URL, include the anchor text in the object text and include a link
     * entity in the function call.
     * @param string $text The text of the user/message/post
     * @param array $data An associative array of optional post data. This
     * will likely change as the API evolves, as of this writing allowed keys are:
     * reply_to, and raw. "raw" may be a complex object represented
     * by an associative array.
     * @param array $params An associative array of optional data to be included
     * in the URL (such as 'include_raw')
     * @return array An associative array representing the post.
     */
    public function processText(string $text, array $data=[], array $params=[])
    {
        $data['text'] = $text;
        $json = json_encode($data);
        $qs = '';
        if (!empty($params)) {
            $qs = '?' . $this->buildQueryString($params);
        }
        return $this->httpReq(
            'post',
            $this->_baseUrl . 'text/process' . $qs,
            $json,
            'application/json'
        );
    }

    /**
     * Create a new Post object. Mentions and hashtags will be parsed out of the
     * post text, as will bare URLs. To create a link in a post without using a
     * bare URL, include the anchor text in the post's text and include a link
     * entity in the post creation call.
     * @param string $text The text of the post
     * @param array $data An associative array of optional post data. This
     * will likely change as the API evolves, as of this writing allowed keys are:
     * reply_to, is_nsfw, and raw. "raw" may be a complex object represented
     * by an associative array.
     * @param array $params An associative array of optional data to be included
     * in the URL (such as 'include_raw')
     * @return array An associative array representing the post.
     */
    public function createPost(string $text, array $data=[], array $params=[])
    {
        $data['text'] = $text;
        $json = json_encode($data);
        $qs = '';
        if (!empty($params)) {
            $qs = '?' . $this->buildQueryString($params);
        }
        return $this->httpReq(
            'post',
            $this->_baseUrl . 'posts' . $qs,
            $json,
            'application/json'
        );
    }

    /**
     * Create a new Post object. Mentions and hashtags will be parsed out of the
     * post text, as will bare URLs. To create a link in a post without using a
     * bare URL, include the anchor text in the post's text and include a link
     * entity in the post creation call.
     * @param integer $post_id The ID of the post to revise
     * @param string $text The new text of the post
     * @param array $data An associative array of optional post data. This
     * will likely change as the API evolves, as of this writing allowed keys are:
     * is_nsfw.
     * @param array $params An associative array of optional data to be included
     * in the URL (such as 'include_raw')
     * @return array An associative array representing the post.
     */
    public function revisePost(int $post_id, string $text, array $data=[], array $params=[])
    {
        $data['text'] = $text;
        $json = json_encode($data);
        $qs = '';
        if (!empty($params)) {
            $qs = '?' . $this->buildQueryString($params);
        }
        return $this->httpReq(
            'put',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . $qs,
            $json,
            'application/json'
        );
    }

    /**
     * Returns a specific Post.
     * @param integer $post_id The ID of the post to retrieve
     * @param array $params An associative array of optional general parameters.
     * @return array An associative array representing the post
     */
    public function getPost(int $post_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Returns a list of Posts.
     * @param array $post_ids The list of post IDs to retrieve
     * @param array $params An associative array of optional general parameters.
     * @return array An array of arrays representing the posts
     */
    public function getMultiplePosts(array $post_ids, array $params=[])
    {
        $params['ids'] = $post_ids;

        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts?' . $this->buildQueryString($params)
        );
    }

    /**
     * Delete a Post. The current user must be the same user who created the Post.
     * It returns the deleted Post on success.
     * @param integer $post_id The ID of the post to delete
     * @param array An associative array representing the post that was deleted
     */
    public function deletePost(int $post_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl . 'posts/' . urlencode($post_id)
        );
    }

    /**
     * Retrieve the Posts that are 'in reply to' a specific Post.
     * @param integer $post_id The ID of the post you want to retrieve replies for.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getPostThread(int $post_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/thread?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Retrieve revisions of a post. Currently only one can be created.
     * @param integer $post_id The ID of the post you want to retrieve previous revisions of.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getPostRevisions(int $post_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/revisions?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Get the most recent Posts created by a specific User in reverse
     * chronological order (most recent first).
     * @param mixed $user_id Either the ID of the user you wish to retrieve posts by,
     * or the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getUserPosts(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/posts?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Get the most recent Posts mentioning by a specific User in reverse
     * chronological order (newest first).
     * @param mixed $user_id Either the ID of the user who is being mentioned, or
     * the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getUserMentions(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/mentions?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Get the currently authenticated user's recent messages
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getUserMessages(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/me/messages?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Return the 20 most recent posts from the current User and
     * the Users they follow.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getUserStream(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/streams/me?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Retrieve a list of all public Posts on pnut.io, often referred to as the
     * global stream.
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getPublicPosts(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/streams/global?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Retrieve a list of "explore" streams
     * @return An array of associative arrays, each representing a single explore stream.
     */
    public function getPostExploreStreams()
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/streams/explore'
        );
    }

    /**
     * Retrieve a list of posts from an "explore" stream on pnut.io.
     * @param  string $slug [<description>]
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getPostExploreStream(string $slug, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/streams/explore/' . urlencode($slug) . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
    * Bookmark a post
    * @param integer $post_id The post ID to bookmark
    */
    public function bookmarkPost(int $post_id)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/bookmark'
        );
    }

    /**
    * Unbookmark a post
    * @param integer $post_id The post ID to unbookmark
    */
    public function unbookmarkPost(int $post_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/bookmark'
        );
    }

    /**
    * List the posts bookmarked by the current user
    * @param array $params An associative array of optional general parameters.
    * This will likely change as the API evolves, as of this writing allowed keys
    * are:    count, before_id, since_id, include_muted, include_deleted,
    * and include_post_raw.
    * See https://github.com/phpnut/api-spec/blob/master/resources/posts.md#general-parameters
    * @return array An array of associative arrays, each representing a single
    * user who has bookmarked a post
    */
    public function getBookmarked(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/bookmarks?'
                . $this->buildQueryString($params)
        );
    }

    /**
    * List the interactions with a post (bookmark, repost, reply)
    * @param integer $post_id the post ID to get interactions from
    * @param array $params optional parameters like filters or excludes
    * @return array An array of associative arrays, each representing one post interaction.
    */
    public function getPostInteractions(int $post_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/interactions?'
                . $this->buildQueryString($params)
        );
    }

    /**
    * List the bookmarks of a post
    * @param integer $post_id the post ID to get stars from
    * @return array An array of associative arrays, each representing one bookmark action.
    */
    public function getPostBookmarks(int $post_id)
    {
        return $this->getPostInteractions($post_id, ['filters'=>['bookmark']]);
    }

    /**
     * Returns an array of User objects of users who reposted the specified post.
     * @param integer $post_id the post ID to
     * @return array An array of associative arrays, each representing a single
     * user who reposted $post_id
     */
    public function getPostReposts(int $post_id)
    {
        return $this->getPostInteractions($post_id, ['filters'=>['repost']]);
    }

    /**
     * Repost an existing Post object.
     * @param integer $post_id The id of the post
     * @return the reposted post
     */
    public function repost(int $post_id)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/repost'
        );
    }

    /**
     * Delete a post that the user has reposted.
     * @param integer $post_id The id of the post
     * @return the un-reposted post
     */
    public function deleteRepost(int $post_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl . 'posts/' . urlencode($post_id) . '/repost'
        );
    }

    /**
     * Return Posts matching a specific #hashtag.
     * @param string $hashtag The hashtag you're looking for.
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: count, before_id, since_id, include_muted, include_deleted,
     * include_directed_posts, and include_raw.
     * @return An array of associative arrays, each representing a single post.
     */
    public function searchHashtags(string $hashtag, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/tags/' . urlencode($hashtag) . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * List the posts who match a specific search term
     * @param array $params a list of filter, search query, and general Post parameters
     * see: https://docs.pnut.io/resources/posts/search
     * @param string $query The search query. Supports
     * normal search terms. Searches post text.
     * @return array An array of associative arrays, each representing one post.
     * or false on error
     */
    public function searchPosts(array $params=[], string $query='', string $order='default')
    {
        if (!is_array($params)) {
            return false;
        }
        if (!empty($query)) {
            $params['q'] = $query;
        }
        if ($order === 'default') {
            if (!empty($query)) {
                $params['order'] = 'relevance';
            } else {
                $params['order'] = 'id';
            }
        }
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/search?' . $this->buildQueryString($params)
        );
    }

    /**
     * Return the 20 most recent posts for a stream using a valid Token
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: count, before_id, since_id, include_muted, include_deleted,
     * and include_post_raw.
     * @return An array of associative arrays, each representing a single post.
     */
    public function getUserPersonalStream(array $params=[])
    {
        if ($params['access_token']) {
            return $this->httpReq(
                'get',
                $this->_baseUrl . 'posts/streams/me?'
                    . $this->buildQueryString($params),
                $params
            );
        } else {
            return $this->httpReq(
                'get',
                $this->_baseUrl . 'posts/streams/me?'
                    . $this->buildQueryString($params)
            );
        }
    }
    
    /**
    * Return the 20 most recent Posts from the current User's personalized stream
    * and mentions stream merged into one stream.
    * @param array $params An associative array of optional general parameters.
    * This will likely change as the API evolves, as of this writing allowed keys
    * are: count, before_id, since_id, include_muted, include_deleted,
    * include_directed_posts, and include_raw.
    * @return An array of associative arrays, each representing a single post.
    */
    public function getUserUnifiedStream(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'posts/streams/unified?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * List User interactions
     */
    public function getMyActions(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/me/interactions?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Returns a specific user object.
     * @param mixed $user_id The ID of the user you want to retrieve, or the string "@-username", or the string
     * "me" to retrieve data for the users you're currently authenticated as.
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_user_raw.
     * @return array An associative array representing the user data.
     */
    public function getUser(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Returns multiple users request by an array of user ids
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_user_raw.
     * @return array An associative array representing the users data.
     */
    public function getUsers(array $user_arr, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users?ids=' . implode(',', $user_arr)
                    . '&' . $this->buildQueryString($params)
        );
    }

    /**
     * Add the specified user ID to the list of users followed.
     * Returns the User object of the user being followed.
     * @param integer $user_id The user ID of the user to follow.
     * @return array An associative array representing the user you just followed.
     */
    public function followUser(string|int $user_id)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/follow'
        );
    }

    /**
     * Removes the specified user ID to the list of users followed.
     * Returns the User object of the user being unfollowed.
     * @param integer $user_id The user ID of the user to unfollow.
     * @return array An associative array representing the user you just unfollowed.
     */
    public function unfollowUser(string|int $user_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/follow'
        );
    }

    /**
     * Returns an array of User objects the specified user is following.
     * @param mixed $user_id Either the ID of the user being followed, or
     * the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @return array An array of associative arrays, each representing a single
     * user following $user_id
     */
    public function getFollowing(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/following?'
                . $this->buildQueryString($params)
        );
    }
    
    /**
     * Returns an array of User ids the specified user is following.
     * @param mixed $user_id Either the ID of the user being followed, or
     * the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @return array user ids the specified user is following.
     */
    public function getFollowingIDs(string|int $user_id='me')
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}users/{$user_id}/following?include_user=0"
        );
    }
    
    /**
     * Returns an array of User objects for users following the specified user.
     * @param mixed $user_id Either the ID of the user being followed, or
     * the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @return array An array of associative arrays, each representing a single
     * user following $user_id
     */
    public function getFollowers(string|int $user_id='me', array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/followers?'
                . $this->buildQueryString($params)
        );
    }
    
    /**
     * Returns an array of User ids for users following the specified user.
     * @param mixed $user_id Either the ID of the user being followed, or
     * the string "me", which will retrieve posts for the user you're authenticated
     * as.
     * @return array user ids for users following the specified user
     */
    public function getFollowersIDs(string|int $user_id='me')
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}users/{$user_id}/followers?include_user=0"
        );
    }

    /**
     * Retrieve a user's user ID by specifying their username.
     * @param string $username The username of the user you want the ID of, without
     * an @ symbol at the beginning.
     * @return integer The user's user ID
     */
    public function getIdByUsername(string $username)
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}users/@{$username}?include_user=0"
        );
    }

    /**
     * Mute a user
     * @param integer $user_id The user ID to mute
     */
    public function muteUser(string|int $user_id)
    {
         return $this->httpReq(
            'put',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/mute'
        );
    }

    /**
     * Unmute a user
     * @param integer $user_id The user ID to unmute
     */
    public function unmuteUser(string|int $user_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/mute'
        );
    }

    /**
     * List the users muted by the current user
     * @return array An array of associative arrays, each representing one muted user.
     */
    public function getMuted()
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}users/me/muted"
        );
    }

    /**
     * Get a user object by username
     * @param string $name the @name to get
     * @return array representing one user
     */
    public function getUserByName(string $name)
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}users/@{$name}"
        );
    }

    /**
     * List the users who match a specific search term
     * @param string $search The search query. Supports @username or #tag searches as
     * well as normal search terms. Searches username, display name, bio information.
     * Does not search posts.
     * @return array An array of associative arrays, each representing one user.
     */
    public function searchUsers(array $params=[], string $query='')
    {
        if (!is_array($params)) {
            return false;
        }
        if ($query === '') {
            return false;
        }
        $params['q'] = $query;
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/search?q=' . $this->buildQueryString($params)
        );
    }

    /**
     * Update Profile Data via JSON
     * @data array containing user descriptors
     */
    public function updateUserData(array $data=[], array $params=[])
    {
        $json = json_encode($data);
        return $this->httpReq(
            'put',
            $this->_baseUrl . 'users/me?'
                . $this->buildQueryString($params),
            $json,
            'application/json'
        );
    }

    /**
     * Update a user image
     * @image path reference to image
     * @which avatar|cover
     */
    protected function updateUserImage(string $image, string $which='avatar')
    {
        $test = @getimagesize($image);
        if ($test && array_key_exists('mime', $test)) {
            $mimeType = $test['mime'];
        }
        $data = [
            $which => new CurlFile($image, $mimeType)
        ];
        return $this->httpReq(
            'post-raw',
            "{$this->_baseUrl}users/me/{$which}",
            $data,
            'multipart/form-data'
        );
    }

    public function updateUserAvatar($avatar)
    {
        return $this->updateUserImage('avatar', $avatar);
    }

    public function updateUserCover($cover)
    {
        return $this->updateUserImage('cover', $cover);
    }

    /**
     * Returns a Client object
     * @param string $client_id
     * @return array An array representing the client
     */
    public function getClient(string $client_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'clients/' . urlencode($client_id) . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Returns a list of truncated client details made by a user
     * @param string $user_id
     * @return array A list of arrays representing clients
     */
    public function getUserClients(string $user_id)
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/' . urlencode($user_id) . '/clients'
        );
    }

    /**
     * update stream marker
     */
    public function updateStreamMarker(array $data=[])
    {
        $json = json_encode($data);
        return $this->httpReq(
            'post',
            "{$this->_baseUrl}markers",
            $json,
            'application/json'
        );
    }

    /**
     * get a page of current user subscribed channels
     */
    public function getMyChannelSubscriptions(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/me/channels/subscribed?' . $this->buildQueryString($params)
        );
    }

    /**
     * get user channels
     */
    public function getMyChannels(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/me/channels?' . $this->buildQueryString($params)
        );
    }

    /**
     * create a channel
     * note: you cannot create a channel with type=io.pnut.core.pm (see createMessage)
     */
    public function createChannel(array $data=[])
    {
        $json = json_encode($data);
        return $this->httpReq(
            'post',
            "{$this->_baseUrl}channels",
            $json,
            'application/json'
        );
    }

    /**
     * get channelid info
     */
    public function getChannel(int $channelid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/' . $channelid . '?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * get an existing private message channel between multiple users
     * @param mixed $users Can be a comma- or space-separated string, or an array.
     * Usernames with @-symbol, or user ids.
     */
    public function getExistingPM(string|array $users, array $params=[])
    {
        if (is_string($users)) {
            $users = explode(',', str_replace(' ', ',', $users));
        }
        foreach($users as $key=>$user) {
            if (!is_numeric($user) && substr($user, 0, 1) !== '@') {
                $users[$key] = "@{$user}";
            }
        }
        $params['ids'] = $users;
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'users/me/channels/existing_pm?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * get multiple channels' info by an array of channelids
     */
    public function getChannels(array $channels, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels?ids=' . implode(',', $channels) . '&'
                . $this->buildQueryString($params)
        );
    }

    /**
     * update channelid
     */
    public function updateChannel(int $channelid, array $data=[])
    {
        $json = json_encode($data);
        return $this->httpReq(
            'put',
            "{$this->_baseUrl}channels/{$channelid}",
            $json,
            'application/json'
        );
    }

    /**
     * subscribe from channelid
     */
    public function channelSubscribe(int $channelid)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl.'channels/'.$channelid.'/subscribe'
        );
    }

    /**
     * unsubscribe from channelid
     */
    public function channelUnsubscribe(int $channelid)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'channels/'.$channelid.'/subscribe'
        );
    }

    /**
     * mute channelid
     */
    public function channelMute(int $channelid)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl.'channels/'.$channelid.'/mute'
        );
    }

    /**
     * unmute channelid
     */
    public function channelUnmute(int $channelid)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'channels/'.$channelid.'/mute'
        );
    }

    /**
     * get all user objects subscribed to channelid
     */
    public function getChannelSubscriptions(int $channelid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'channels/'.$channelid.'/subscribers?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * get all user IDs subscribed to channelid
     */
    public function getChannelSubscriptionsById(int $channelid)
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}channels/{$channelid}/subscribers?include_user=0"
        );
    }

    /**
     * Retrieve a list of "explore" streams
     * @return An array of associative arrays, each representing a single explore stream.
     */
    public function getChannelExploreStreams()
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/streams/explore'
        );
    }

    /**
     * Retrieve a list of channels from an "explore" stream on pnut.io.
     * @param  string $slug [<description>]
     * @param array $params An associative array of optional general parameters.
     * @return An array of associative arrays, each representing a single channel.
     */
    public function getChannelExploreStream(string $slug, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/streams/explore/' . urlencode($slug) . '?'
                . $this->buildQueryString($params)
        );
    }
    
    /**
     * mark channel inactive
     */
    public function deleteChannel(int $channelid)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'channels/'.$channelid
        );
    }

    /**
     * List the channels that match a specific search term
     * @param array $params a list of filter, search query, and general Channel parameters
     * see: https://docs.pnut.io/resources/channels/search
     * @param string $query The search query. Supports
     * normal search terms. Searches common channel raw.
     * @return array An array of associative arrays, each representing one channel.
     * or false on error
     */
    public function searchChannels(array $params=[], string $query='', string $order='default')
    {
        if (!empty($query)) {
            $params['q'] = $query;
        }
        if ($order === 'default') {
            if (!empty($query)) {
                $params['order'] = 'id';
            } else {
                $params['order'] = 'activity';
            }
        }
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/search?' . $this->buildQueryString($params)
        );
    }


    /**
     * get a page of messages in channelid
     */
    public function getMessages(int $channelid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'channels/'.$channelid.'/messages?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * get a page of messages in channelid in a thread
     */
    public function getMessageThread(int $channelid, int $messageid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'channels/'.$channelid.'/messages/'.$messageid.'/thread?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * get a page of sticky messages in channelid
     */
    public function getStickyMessages(int $channelid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'channels/'.$channelid.'/sticky_messages?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * sticky messsage
     */
    public function stickyMessage(int $channelid, int $messageid)
    {
        return $this->httpReq(
            'put',
            $this->_baseUrl.'channels/'.$channelid.'/messages/'.$messageid.'/sticky'
        );
    }

    /**
     * unsticky messsage
     */
    public function unstickyMessage(int $channelid, int $messageid)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'channels/'.$channelid.'/messages/'.$messageid.'/sticky'
        );
    }

    /**
     * create message
     * @param $channelid numeric or "pm" for auto-channel (type=io.pnut.core.pm)
     * @param array $data array('text'=>'YOUR_MESSAGE') If a type=io.pnut.core.pm, then "destinations" key can be set to address as an array of people to send this PM too
     * @param array $params query parameters
     */
    public function createMessage(string|int $channelid, array $data, array $params=[])
    {
        if (isset($data['destinations'])) {
            if (is_string($data['destinations'])) {
                $data['destinations'] = explode(',', str_replace(' ', ',', $data['destinations']));
            }
            foreach($data['destinations'] as $key=>$user) {
                if (!is_numeric($user) && substr($user, 0,1 ) !== '@') {
                    $data['destinations'][$key] = "@{$user}";
                }
            }
        }
        $json = json_encode($data);
        return $this->httpReq(
            'post',
            $this->_baseUrl.'channels/'.$channelid.'/messages?'
                . $this->buildQueryString($params),
            $json,
            'application/json'
        );
    }

    /**
     * get message
     */
    public function getMessage(int $channelid, int $messageid, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'channels/'.$channelid.'/messages/'.$messageid.'?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Returns a list of Messages.
     * @param array $message_ids The list of message IDs to retrieve
     * @param array $params An associative array of optional general parameters.
     * @return array An array of arrays representing the messages
     */
    public function getMultipleMessages(array $message_ids, array $params=[])
    {
        $params['ids'] = $message_ids;

        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/messages?' . $this->buildQueryString($params)
        );
    }

    /**
     * delete messsage
     */
    public function deleteMessage(int $channelid, int $messageid)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'channels/'.$channelid.'/messages/'.$messageid
        );
    }

    /**
     * List the messages that match a specific search term
     * @param array $params a list of filter, search query, and general Message parameters
     * see: https://docs.pnut.io/resources/messages/search
     * @param string $query The search query. Supports
     * normal search terms. Searches common channel raw.
     * @return array An array of associative arrays, each representing one channel.
     * or false on error
     */
    public function searchMessages(array $params=[], string $query='', string $order='default')
    {
        if (!is_array($params)) {
            return false;
        }
        if (!empty($query)) {
            $params['q'] = $query;
        }
        if ($order === 'default') {
            if (!empty($query)) {
                $params['order'] = 'id';
            } else {
                $params['order'] = 'relevance';
            }
        }
        return $this->httpReq(
            'get',
            $this->_baseUrl . 'channels/messages/search?'
                . $this->buildQueryString($params)
        );
    }

    /**
     * Upload a file to a user's file store
     * @param string $file A string containing the path of the file to upload.
     * @param array $data Additional data about the file you're uploading. At the
     * moment accepted keys are: mime-type, kind, type, name, public and raw.
     * - If you don't specify mime-type, phpnut will attempt to guess the mime type
     * based on the file, however this isn't always reliable.
     * - If you don't specify kind phpnut will attempt to determine if the file is
     * an image or not.
     * - If you don't specify name, phpnut will use the filename of the first
     * parameter.
     * - If you don't specify is_public, your file will be uploaded as a private file.
     * - Type is REQUIRED.
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_file_raw.
     * @return array An associative array representing the file
     */
    public function createFile($file, array $data, array $params=[])
    {
        if (!$file) {
            throw new PhpnutException('You must specify a path to a file');
        }
        if (!file_exists($file)) {
            throw new PhpnutException('File path specified does not exist');
        }
        if (!is_readable($file)) {
            throw new PhpnutException('File path specified is not readable');
        }
        if (!array_key_exists('type', $data) || !$data['type']) {
            throw new PhpnutException('Type is required when creating a file');
        }
        if (!array_key_exists('name', $data)) {
            $data['name'] = basename($file);
        }
        if (array_key_exists('mime-type', $data)) {
            $mimeType = $data['mime-type'];
            unset($data['mime-type']);
        } else {
            $mimeType = null;
        }
        if (!array_key_exists('kind', $data)) {
            $test = @getimagesize($path);
            if ($test && array_key_exists('mime', $test)) {
                $data['kind'] = 'image';
                if (!$mimeType) {
                    $mimeType = $test['mime'];
                }
            }
            else {
                $data['kind'] = 'other';
            }
        }
        if (!$mimeType) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file);
            finfo_close($finfo);
        }
        if (!$mimeType) {
            throw new PhpnutException('Unable to determine mime type of file, try specifying it explicitly');
        }
        $data['content'] = new \CurlFile($file, $mimeType);
        return $this->httpReq(
            'post-raw',
            "{$this->_baseUrl}files",
            $data,
            'multipart/form-data'
        );
    }

    public function createFilePlaceholder($file, array $params=[])
    {
        $name = basename($file);
        $data = [
            'raw' => $params['raw'],
            'kind' => $params['kind'],
            'name' => $name,
            'type' => $params['metadata']
        ];
        $json = json_encode($data);
        return $this->httpReq(
            'post',
            $this->_baseUrl.'files',
            $json,
            'application/json'
        );
    }

    public function updateFileContent(int $fileid, string $file)
    {
        $data = file_get_contents($file);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $this->httpReq(
            'put',
            $this->_baseUrl.'files/'.$fileid.'/content',
            $data,
            $mime
        );
    }

    /**
     * Allows for file rename and annotation changes.
     * @param integer $file_id The ID of the file to update
     * @param array $params An associative array of file parameters.
     * @return array An associative array representing the updated file
    */
    public function updateFile(int $file_id, array $params=[])
    {
        $data = [
            'raw' => $params['raw'],
            'name' => $params['name'],
        ];
        $json = json_encode($data);
        return $this->httpReq(
            'put',
            $this->_baseUrl.'files/'.urlencode($file_id),
            $json,
            'application/json'
        );
    }

    /**
     * Returns a specific File.
     * @param integer $file_id The ID of the file to retrieve
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_file_raw.
     * @return array An associative array representing the file
     */
    public function getFile(int $file_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'files/'.urlencode($file_id).'?'
                . $this->buildQueryString($params)
        );
    }

    public function getFileContent(int $file_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'files/'.urlencode($file_id).'/content?'
                . $this->buildQueryString($params)
        );
    }

    /** $file_key : derived_file_key */
    public function getDerivedFileContent(int $file_id, string $file_key, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'files/'.urlencode($file_id).'/content/'.urlencode($file_key).'?'.$this->buildQueryString($params)
        );
    }

    /**
     * Returns file objects.
     * @param array $file_ids The IDs of the files to retrieve
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_file_raw.
     * @return array An associative array representing the file data.
     */
    public function getFiles(array $file_ids, array $params=[])
    {
        $ids = '';
        foreach($file_ids as $id) {
            $ids .= $id . ',';
        }
        $params['ids'] = substr($ids, 0, -1);
        return $this->httpReq(
            'get',
            $this->_baseUrl.'files?'.$this->buildQueryString($params)
        );
    }

    /**
     * Returns a user's file objects.
     * @param array $params An associative array of optional general parameters.
     * This will likely change as the API evolves, as of this writing allowed keys
     * are: include_raw|include_file_raw|include_user_raw.
     * @return array An associative array representing the file data.
     */
    public function getUserFiles(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'users/me/files?'.$this->buildQueryString($params)
        );
    }

    /**
     * Delete a File. The current user must be the same user who created the File.
     * It returns the deleted File on success.
     * @param integer $file_id The ID of the file to delete
     * @return array An associative array representing the file that was deleted
     */
    public function deleteFile(int $file_id)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'files/'.urlencode($file_id)
        );
    }

    /**
     * Create a poll
     * @param array $data An associative array of the required parameters.
     * @param array $params An associative array of optional general parameters.
     * Allowed keys: include_raw,include_poll_raw, ...
     * @return array An associative array representing the poll
     */
    public function createPoll(array $data, array $params=[])
    {
        $json = json_encode($data);
        return $this->httpReq(
            'post',
            $this->_baseUrl.'polls?'.$this->buildQueryString($params), 
            $json,
            'application/json'
        );
    }

    /**
     * Responds to a poll.
     * @param integer $poll_id The ID of the poll to respond to
     * @param array list of positions for the poll response
     * @param array $params An associative array of optional general parameters.
     */
    public function respondToPoll(int $poll_id, array $positions, array $params=[])
    {
        $json = json_encode(['positions' => $positions]);
        return $this->httpReq(
            'put',
            $this->_baseUrl.'polls/'.urlencode($poll_id).'/response?'.$this->buildQueryString($params),
            $json, 
            'application/json'
        );
    }

    /**
     * Returns a specific Poll.
     * @param integer $poll_id The ID of the poll to retrieve
     * @param array $params An associative array of optional general parameters.
     * @return array An associative array representing the poll
     */
    public function getPoll(int $poll_id, array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'polls/'.urlencode($poll_id).'?'.$this->buildQueryString($params)
        );
    }

    /**
     * Returns a list of Polls.
     * @param array $poll_ids The list of poll IDs to retrieve
     * @param array $params An associative array of optional general parameters.
     * @return array An array of arrays representing the polls
     */
    public function getMultiplePolls(array $poll_ids, array $params=[])
    {
        $params['ids'] = $poll_ids;

        return $this->httpReq(
            'get',
            $this->_baseUrl . 'polls?' . $this->buildQueryString($params)
        );
    }

    /**
     * Returns a user's poll objects.
     * @param array $params An associative array of optional general parameters.
     * @return array An associative array representing the poll data.
     */
    public function getUserPolls(array $params=[])
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'users/me/polls?'.$this->buildQueryString($params)
        );
    }

    /**
     * Delete a Poll. The current user must be the same user who created the Poll.
     * @param integer $poll_id The ID of the poll to delete
     * @param array $params An associative array of optional general parameters.
     * @return array An associative array representing the poll that was deleted
     */
    public function deletePoll(int $poll_id, array $params=[])
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'polls/'.urlencode($poll_id).'?'.$this->buildQueryString($params)
        );
    }

    /**
     * List the polls that match a specific search term
     * @param array $params a list of filter, search query, and general Poll parameters
     * see: https://docs.pnut.io/resources/channels/search
     * @param string $query The search query. Supports
     * normal search terms.
     * @return array An array of associative arrays, each representing one poll.
     * or false on error
     */
    public function searchPolls(array $params=[], string $order='id')
    {
        $params['order'] = $order;

        return $this->httpReq(
            'get',
            $this->_baseUrl . 'polls/search?' . $this->buildQueryString($params)
        );
    }


    /**
     * Get Application Information
     */
    public function getAppTokenInfo()
    {
        // requires appAccessToken
        if (!$this->_appAccessToken) {
            $this->getAppAccessToken();
        }
        // ensure request is made with our appAccessToken
        $params['access_token'] = $this->_appAccessToken;
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}token",
            $params
        );
    }
    
    /**
     * Get User Information
     */
    public function getUserTokenInfo()
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}token"
        );
    }
    
    /**
     * Get Application Authorized User IDs
     */
    public function getAppUserIDs()
    {
        // requires appAccessToken
        if (!$this->_appAccessToken) {
            $this->getAppAccessToken();
        }
        // ensure request is made with our appAccessToken
        $params['access_token'] = $this->_appAccessToken;
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}apps/me/users/ids",
            $params
        );
    }
    
    /**
     * Get Application Authorized User Tokens
     */
    public function getAppUserTokens()
    {
        // requires appAccessToken
        if (!$this->_appAccessToken) {
            $this->getAppAccessToken();
        }
        // ensure request is made with our appAccessToken
        $params['access_token'] = $this->_appAccessToken;
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}apps/me/users/tokens",
            $params
        );
    }



    /**
     * Registers your function (or an array of object and method) to be called
     * whenever an event is received via an open pnut.io stream. Your function
     * will receive a single parameter, which is the object wrapper containing
     * the meta and data.
     * @param mixed A PHP callback (either a string containing the function name,
     * or an array where the first element is the class/object and the second
     * is the method).
     */
    public function registerStreamFunction($function): void
    {
        $this->_streamCallback = $function;
    }
    
    /**
     * Opens a stream that's been created for this user/app and starts sending
     * events/objects to your defined callback functions. You must define at
     * least one callback function before opening a stream.
     * @param mixed $stream Either a stream ID or the endpoint of a stream
     * you've already created. This stream must exist and must be valid for
     * your current access token. If you pass a stream ID, the library will
     * make an API call to get the endpoint.
     *
     * This function will return immediately, but your callback functions
     * will continue to receive events until you call closeStream() or until
     * pnut.io terminates the stream from their end with an error.
     *
     * If you're disconnected due to a network error, the library will
     * automatically attempt to reconnect you to the same stream, no action
     * on your part is necessary for this. However if the pnut.io API returns
     * an error, a reconnection attempt will not be made.
     *
     * Note there is no closeStream, because once you open a stream you
     * can't stop it (unless you exit() or die() or throw an uncaught
     * exception, or something else that terminates the script).
     * @return boolean True
     * @see createStream()
     */
    public function openStream($stream): bool
    {
        // if there's already a stream running, don't allow another
        if ($this->_currentStream) {
            throw new phpnutException('There is already a stream being consumed, only one stream can be consumed per phpnutStream instance');
        }
        // must register a callback (or the exercise is pointless)
        if (!$this->_streamCallback) {
            throw new phpnutException('You must define your callback function using registerStreamFunction() before calling openStream');
        }
        // if the stream is a numeric value, get the stream info from the api
        if (is_numeric($stream)) {
            $stream = $this->getStream($stream);
            $this->_streamUrl = $stream['endpoint'];
        }
        else {
            $this->_streamUrl = $stream;
        }
        // continue doing this until we get an error back or something...?
        $this->httpStream(
            'get',
            $this->_streamUrl
        );
        return true;
    }
    
    /**
     * Close the currently open stream.
     * @return true;
     */
    public function closeStream(): void
    {
        if (!$this->_lastStreamActivity) {
            // never opened
            return;
        }
        if (!$this->_multiStream) {
            throw new phpnutException('You must open a stream before calling closeStream()');
        }
        curl_close($this->_currentStream);
        curl_multi_remove_handle($this->_multiStream, $this->_currentStream);
        curl_multi_close($this->_multiStream);
        $this->_currentStream = null;
        $this->_multiStream = null;
    }
    
    /**
     * Retrieve all streams for the current access token.
     * @return array An array of stream definitions.
     */
    public function getAllStreams()
    {
        return $this->httpReq(
            'get',
            "{$this->_baseUrl}streams"
        );
    }
    
    /**
     * Returns a single stream specified by a stream ID. The stream must have been
     * created with the current access token.
     * @return array A stream definition
     */
    public function getStream(string $streamId)
    {
        return $this->httpReq(
            'get',
            $this->_baseUrl.'streams/'.urlencode($streamId)
        );
    }
    
    /**
     * Creates a stream for the current app access token.
     *
     * @param array $objectTypes The objects you want to retrieve data for from the
     * stream. At time of writing these can be 'post', 'bookmark', 'user_follow', 'mute', 'block', 'stream_marker', 'message', 'channel', 'channel_subscription', 'token', and/or 'user'.
     * If you don't specify, a few standard events will be retrieved.
     */
    public function createStream(?array $objectTypes=null)
    {
        // default object types to everything
        if (is_null($objectTypes)) {
            $objectTypes = [
                'post',
                'bookmark',
                'user_follow',
            ];
        }
        $data = [
            'object_types'=>$objectTypes,
            'type'=>'long_poll',
        ];
        $data = json_encode($data);
        $response = $this->httpReq(
            'post',
            "{$this->_baseUrl}streams",
            $data,
            'application/json'
        );
        return $response;
    }
    
    /**
     * Update stream for the current app access token
     *
     * @param string $streamId The stream ID to update. This stream must have been
     * created by the current access token.
     * @param array $data allows object_types, type, filter_id and key to be updated. filter_id/key can be omitted
     */
    public function updateStream(string $streamId, array $data)
    {
        // objectTypes is likely required
        if (is_null($data['object_types'])) {
            $data['object_types'] = [
                'post',
                'bookmark',
                'user_follow',
            ];
        }
        // type can still only be long_poll
        if (is_null($data['type'])) {
            $data['type'] = 'long_poll';
        }
        $data = json_encode($data);
        $response = $this->httpReq(
            'put',
            $this->_baseUrl.'streams/'.urlencode($streamId),
            $data,
            'application/json'
        );
        return $response;
    }
     
    /**
     * Deletes a stream if you no longer need it.
     *
     * @param string $streamId The stream ID to delete. This stream must have been
     * created by the current access token.
     */
    public function deleteStream(string $streamId)
    {
        return $this->httpReq(
            'delete',
            $this->_baseUrl.'streams/'.urlencode($streamId)
        );
    }
    
    /**
     * Deletes all streams created by the current access token.
     */
    public function deleteAllStreams()
    {
        return $this->httpReq(
            'delete',
            "{$this->_baseUrl}streams"
        );
    }
    
    /**
     * Internal function used to process incoming chunks from the stream. This is only
     * public because it needs to be accessed by CURL. Do not call or use this function
     * in your own code.
     * @ignore
     */
    public function httpStreamReceive($ch, $data)
    {
        $this->_lastStreamActivity = time();
        $this->_streamBuffer .= $data;
        if (!$this->_streamHeaders) {
            $pos = strpos($this->_streamBuffer, "\r\n\r\n");
            if ($pos !== false) {
                $this->_streamHeaders = substr($this->_streamBuffer, 0, $pos);
                $this->_streamBuffer = substr($this->_streamBuffer, $pos+4);
            }
        } else {
            $pos = strpos($this->_streamBuffer, "\r\n");
            while ($pos !== false) {
                $command = substr($this->_streamBuffer, 0, $pos);
                $this->_streamBuffer = substr($this->_streamBuffer, $pos+2);
                $command = json_decode($command, true);
                if ($command) {
                    call_user_func($this->_streamCallback, $command);
                }
                $pos = strpos($this->_streamBuffer, "\r\n");
            }
        }
        return strlen($data);
    }
    
    /**
     * Opens a long lived HTTP connection to the pnut.io servers, and sends data
     * received to the httpStreamReceive function. As a general rule you should not
     * directly call this method, it's used by openStream().
     */
    protected function httpStream(string $act, $req, array $params=[], string $contentType='application/x-www-form-urlencoded'): void
    {
        if ($this->_currentStream) {
            throw new phpnutException('There is already an open stream, you must close the existing one before opening a new one');
        }
        $headers = [];
        $this->_streamBuffer = '';
        if ($this->_accessToken) {
            $headers[] = "Authorization: Bearer {$this->_accessToken}";
        }
        $this->_currentStream = curl_init($req);
        curl_setopt($this->_currentStream, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->_currentStream, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_currentStream, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->_currentStream, CURLOPT_HEADER, true);
        if ($this->_sslCA) {
            curl_setopt($this->_currentStream, CURLOPT_CAINFO, $this->_sslCA);
        }
        // every time we receive a chunk of data, forward it to httpStreamReceive
        curl_setopt($this->_currentStream, CURLOPT_WRITEFUNCTION, array($this, 'httpStreamReceive'));
        // curl_exec($ch);
        // return;
        $this->_multiStream = curl_multi_init();
        $this->_lastStreamActivity = time();
        curl_multi_add_handle($this->_multiStream, $this->_currentStream);
    }
    
    public function reconnectStream(): void
    {
        $this->closeStream();
        $this->_connectFailCounter++;
        // if we've failed a few times, back off
        if ($this->_connectFailCounter > 1) {
            $sleepTime = pow(2, $this->_connectFailCounter);
            // don't sleep more than 60 seconds
            if ($sleepTime > 60) {
                $sleepTime = 60;
            }
            sleep($sleepTime);
        }
        $this->httpStream('get', $this->_streamUrl);
    }
    
    /**
     * Process an open stream for x microseconds, then return. This is useful if you want
     * to be doing other things while processing the stream. If you just want to
     * consume the stream without other actions, you can call processForever() instead.
     * @param float @microseconds The number of microseconds to process for before
     * returning. There are 1,000,000 microseconds in a second.
     *
     * @return void
     */
    public function processStream($microseconds=null): void
    {
        if (!$this->_multiStream) {
            throw new phpnutException('You must open a stream before calling processStream()');
        }
        $start = microtime(true);
        $active = null;
        $inQueue = null;
        $sleepFor = 0;
        do {
            // if we haven't received anything within 5.5 minutes, reconnect
            // keepalives are sent every 5 minutes (measured on 2013-3-12 by @ryantharp)
            if (time()-$this->_lastStreamActivity >= 330) {
                $this->reconnectStream();
            }
            curl_multi_exec($this->_multiStream, $active);
            if (!$active) {
                $httpCode = curl_getinfo($this->_currentStream, CURLINFO_HTTP_CODE);
                // don't reconnect on 400 errors
                if ($httpCode >= 400 && $httpCode <= 499) {
                    throw new phpnutException("Received HTTP error {$httpCode} check your URL and credentials before reconnecting");
                }
                $this->reconnectStream();
            }
            // sleep for a max of 2/10 of a second
            $timeSoFar = (microtime(true)-$start)*1000000;
            $sleepFor = $this->streamingSleepFor;
            if ($timeSoFar+$sleepFor > $microseconds) {
                $sleepFor = $microseconds - $timeSoFar;
            }
            if ($sleepFor > 0) {
                usleep($sleepFor);
            }
        } while ($timeSoFar+$sleepFor < $microseconds);
    }
    
    /**
     * Process an open stream forever. This function will never return, if you
     * want to perform other actions while consuming the stream, you should use
     * processFor() instead.
     * @return void This function will never return
     * @see processFor();
     */
    public function processStreamForever(): void
    {
        while (true) {
            $this->processStream(600);
        }
    }
}
