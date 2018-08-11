<?php
/**
 * Super-skeletal class to interact with Diaspora.
 *
 * @author Meitar Moscovitz <meitarm@gmail.com>
 * Modifications by Michael Vogel <heluecht@pirati.ca>
 */

class Diaspora_Connection {
	private $user;
	private $host;
	private $password;
	private $tls = true; //< Whether to use an SSL/TLS connection or not.

	private $last_http_result; //< Result of last cURL transaction.
	private $csrf_token; //< Authenticity token retrieved from last HTTP response.
	private $http_method; //< Which HTTP verb to use for the next HTTP request.
	private $cookiejar;

	private $debug_log;

	public $provider = '*Diaspora Connection';

	public function __construct($diaspora_handle = '', $password = '') {
		if (!empty($diaspora_handle)) {
			$this->setDiasporaID($diaspora_handle);
		}
		if (!empty($password)) {
			$this->setPassword($password);
		}

		$this->cookiejar = tempnam(get_temppath(), 'cookies');
		return $this;
	}

	public function __destruct() {
		if (file_exists($this->cookiejar)) {
			unlink($this->cookiejar);
		}
	}

	public function setDebugLog($log_file) {
		$this->debug_log = $log_file;
	}

	public function setDiasporaID($id) {
		$parts = explode('@', $id);
		$this->user = $parts[0];
		$this->host = $parts[1];
	}

	public function getDiasporaID() {
		return $this->user . '@' . $this->host;
	}

	public function getPodURL() {
		return $this->getScheme() . '://' . $this->host;
	}

	public function setPassword($passwd) {
		$this->password = $passwd;
	}

	public function setSecureTransport($is_secure) {
		$this->tls = (bool) $is_secure;
	}

	private function getScheme() {
		return ($this->tls) ? 'https' : 'http';
	}

	private function doHttpRequest($url, $data = [], $headers = []) {
		if (0 === strpos($url, '/')) {
			$url = $this->getScheme() . '://' . $this->host . $url;
		}

		$ch = curl_init($url);

		if ($this->debug_log) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			$fh = fopen($this->debug_log, 'a');
			curl_setopt($ch, CURLOPT_STDERR, $fh);
		}

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);

		// Are we doing a special kind of HTTP request?
		switch ($this->http_method) {
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->http_method);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				break;
		}

		$this->last_http_result = new stdClass();
		$this->last_http_result->response = curl_exec($ch);
		$this->last_http_result->info = curl_getinfo($ch);
		curl_close($ch);
		if (isset($fh)) {
			fclose($fh);
		}

		// Maybe update CSRF token
		$token = $this->parseAuthenticityToken($this->last_http_result->response);
		if ($token) {
			$this->csrf_token = $token;
		}

		return $this->last_http_result;
	}

	private function doHttpDelete($url, $data = [], $headers = []) {
		$this->http_method = 'DELETE';
		$this->doHttpRequest($url, $data, $headers);
		$this->http_method = null; // reset for next request
	}

	private function parseAuthenticityToken($str) {
		$m = [];
		preg_match('/<meta (?:name="csrf-token" content="(.*?)"|content="(.*?)" name="csrf-token")/', $str, $m);
		if (empty($m[1]) && !empty($m[2])) {
			$token = $m[2];
		} elseif (!empty($m[1])) {
			$token = $m[1];
		}
		return (!empty($token)) ? $token : false;
	}

	private function readJsonResponse($response) {
		$lines = explode("\r\n", $response);
		$x = array_splice(
			$lines, array_search('', $lines) + 1 // empty, as "\r\n" was explode()'d
		);
		$http_body = array_pop($x);
		return json_decode($http_body);
	}

	public function logIn() {
		$this->doHttpRequest('/users/sign_in');

		$params = [
			'user[username]' => $this->user,
			'user[password]' => $this->password,
			'authenticity_token' => $this->csrf_token
		];
		$this->doHttpRequest('/users/sign_in', $params);
		$this->doHttpRequest('/stream');
		return (200 === $this->last_http_result->info['http_code']) ? true : false;
	}

	public function getAspects() {
		$this->doHttpRequest('/bookmarklet');
		$m = [];
		preg_match('/"aspects"\:(\[.+?\])/', $this->last_http_result->response, $m);
		return (!empty($m[1])) ? json_decode($m[1]) : false;
	}

	public function getServices() {
		$this->doHttpRequest('/bookmarklet');
		$m = [];
		preg_match('/"configured_services"\:(\[.+?\])/', $this->last_http_result->response, $m);
		return (!empty($m[1])) ? json_decode($m[1]) : false;
	}

	public function getNotifications($notification_type = '', $show = '') {
		$url = '/notifications?format=json';

		if (!empty($notification_type)) {
			$url .= "&type=$notification_type";
		}

		if ('unread' === $show) {
			$url .= '&show=unread';
		}

		$this->doHttpRequest($url);
		return $this->readJsonResponse($this->last_http_result->response);
	}

	public function getComments($post_id) {
		$url = "/posts/$post_id/comments?format=json";
		$this->doHttpRequest($url);
		return $this->readJsonResponse($this->last_http_result->response);
	}

	public function postStatusMessage($msg, $aspect_ids = 'all_aspects', $additional_data = []) {
		$data = [
			'aspect_ids' => $aspect_ids,
			'status_message' => [
				'text' => $msg,
				'provider_display_name' => $this->provider
			]
		];

		if (!empty($additional_data)) {
			$data += $additional_data;
		}

		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'X-CSRF-Token: ' . $this->csrf_token
		];

		$this->http_method = 'POST';
		$this->doHttpRequest('/status_messages', json_encode($data), $headers);
		$this->http_method = null; // reset for next request
		if (201 !== $this->last_http_result->info['http_code']) {
			// TODO: Handle error.
			return false;
		} elseif (200 !== $this->last_http_result->info['http_code']) {
			$resp = $this->readJsonResponse($this->last_http_result->response);
			return $resp->id;
		}
	}

	public function postPhoto($file) {
		$params = [
			'photo[pending]' => 'true',
			'qqfile' => basename($file)
		];
		$query_string = '?' . http_build_query($params);
		$headers = [
			'Accept: application/json',
			'X-Requested-With: XMLHttpRequest',
			'X-CSRF-Token: ' . $this->csrf_token,
			'X-File-Name: ' . basename($file),
			'Content-Type: application/octet-stream',
		];
		if ($size = @filesize($file)) {
			$headers[] = "Content-Length: $size";
		}
		$data = file_get_contents($file);
		$this->doHttpRequest('/photos' . $query_string, $data, $headers);
		return $this->readJsonResponse($this->last_http_result->response);
	}

	public function deletePost($id) {
		$headers = ['X-CSRF-Token: ' . $this->csrf_token];
		$this->doHttpDelete("/posts/$id", [], $headers);
		return (204 === $this->last_http_result->info['http_code']) ? true : false;
	}

	public function deleteComment($id) {
		$headers = ['X-CSRF-Token: ' . $this->csrf_token];
		$this->doHttpDelete("/comments/$id", [], $headers);
		return (204 === $this->last_http_result->info['http_code']) ? true : false;
	}

}
