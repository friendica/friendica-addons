<?php
/**
 * Ein fies zusammengehackter PHP-Diaspory-Client, der direkt von diesem abgeschaut ist:
 * https://github.com/Javafant/diaspy/blob/master/client.py
 */

class Diasphp {
	private $cookiejar;

	function __construct($pod) {
		$this->token_regex = '/content="(.*?)" name="csrf-token/';

		$this->pod = $pod;
		$this->cookiejar = tempnam(get_temppath(), 'cookies');
	}

	function __destruct() {
		if (file_exists($this->cookiejar))
			unlink($this->cookiejar);
	}

	function _fetch_token() {
		$ch = curl_init();

		curl_setopt ($ch, CURLOPT_URL, $this->pod . "/stream");
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec ($ch);
		curl_close($ch);

		// Token holen und zurückgeben
		preg_match($this->token_regex, $output, $matches);
		return $matches[1];
	}

	function login($username, $password) {
		$datatopost = array(
			'user[username]' => $username,
			'user[password]' => $password,
			'authenticity_token' => $this->_fetch_token()
		);

		$poststr = http_build_query($datatopost);

		// Adresse per cURL abrufen
		$ch = curl_init();

		curl_setopt ($ch, CURLOPT_URL, $this->pod . "/users/sign_in");
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $poststr);

		curl_exec ($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code'] != 302) {
			throw new Exception('Login error '.print_r($info, true));
		}

		// Das Objekt zurückgeben, damit man Aurufe verketten kann.
		return $this;
	}

	function post($text, $provider = "diasphp") {
		// post-daten vorbereiten
		$datatopost = json_encode(array(
				'aspect_ids' => 'public',
				'status_message' => array('text' => $text,
							'provider_display_name' => $provider)
		));

		// header vorbereiten
		$headers = array(
			'Content-Type: application/json',
			'accept: application/json',
			'x-csrf-token: '.$this->_fetch_token()
		);

		// Adresse per cURL abrufen
		$ch = curl_init();

		curl_setopt ($ch, CURLOPT_URL, $this->pod . "/status_messages");
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);

		curl_exec ($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code'] != 201) {
			throw new Exception('Post error '.print_r($info, true));
		}

		// Ende der möglichen Kette, gib mal "true" zurück.
		return true;
	}
}
?>
