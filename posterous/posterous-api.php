<?php /*
Name: Posterous API Library
Description: Object-oriented PHP class for accessing the Posterous API
Author: Calvin Freitas
Version: 0.1.0
Author URI: http://calvinf.com/
License:  MIT License (see LICENSE) http://creativecommons.org/licenses/MIT/
Warranties: None
Last Modified: December 07, 2009
Requirements: PHP 5 or higher.
*/

/*
Copyright (c) 2009 Calvin Freitas

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/* Define Static Variables */
define('POSTEROUS_API_LIBRARY_VERSION','1.0');
define('POSTEROUS_API_LIBRARY_RELEASE_DATE','December 07, 2009');
define('POSTEROUS_API_LIBRARY_URL','http://calvinf.com/projects/posterous-api-library-php/');
define('POSTEROUS_API_LIBRARY_AUTHOR_NAME','Calvin Freitas');
define('POSTEROUS_API_LIBRARY_AUTHOR_URL','http://calvinf.com/');
define('POSTEROUS_API_LIBRARY_AUTHOR_EMAIL','cal@calvinfreitas.com');

define('POSTEROUS_API_URL', 'http://posterous.com/api/');

// ensure Curl extension installed
if(!extension_loaded("curl")) {
	throw(new Exception("The Curl extension for PHP is required for PosterousAPI to work."));
}

/* Useful to catch this exception separately from standard PHP Exceptions */
class PosterousException extends Exception {}

/* This class contains functions for calling the Posterous API */
class PosterousAPI {
	private $user;
	private $pass;

	function __construct($user = NULL, $pass = NULL) {
		$this->user = $user;
		$this->pass = $pass;
	}

	/* Reading Methods - http://posterous.com/api/reading */
	function getsites() {
		$api_method = 'getsites';
		$xml = $this->_call( $api_method );
		return $xml;
	}

	function readposts($args) {
		$api_method = 'readposts';

		$valid_args = array('hostname','site_id','num_posts','page','tag');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	function gettags($args) {
		$api_method = 'gettags';

		$valid_args = array('hostname','site_id');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	/* Posting Methods - http://posterous.com/api/posting */
	function newpost($args) {
		$api_method = 'newpost';

		if (!$this->_auth()) {
			throw new PosterousException('Posterous API call "' . $api_method . '" requires authentication.');
		}

		$valid_args = array('site_id','media','title','body','autopost','private','date','tags','source','sourceLink');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	function updatepost($args) {
		$api_method = 'updatepost';

		if (!$this->_auth()) {
			throw new PosterousException('Posterous API call "' . $api_method . '" requires authentication.');
		}

		$valid_args = array('post_id','media','title','body');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	function newcomment($args) {
		$api_method = 'newcomment';

		$valid_args = array('post_id','comment','name','email','date');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	/* Post.ly Methods - http://posterous.com/api/postly */

	function getpost($args) {
		$api_method = 'getpost';

		$valid_args = array('id');
		$method_args = $this->_validate($args, $valid_args);

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	/* Twitter Methods - http://posterous.com/api/twitter */
	function upload() {
		$api_method = 'upload';

		$valid_args = array('username','password','media','message','body','source','sourceLink');
		$method_args = $this->_validate( $args, $method_args );

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}

	function uploadAndPost() {
		$api_method = 'uploadAndPost';

		$valid_args = array('username','password','media','message','body','source','sourceLink');
		$method_args = $this->_validate( $args, $method_args );

		$xml = $this->_call( $api_method, $method_args );
		return $xml;
	}


	/* Helper Functions */
	private function _call($api_method, $method_args = NULL) {
		$method_url = POSTEROUS_API_URL . $api_method;

		$user = $this->user();
		$pass = $this->pass();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $method_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		if (isset($user) && isset($pass) && $user != '' && $pass != '') {
			curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $pass);
		}

		curl_setopt($ch, CURLOPT_POST, 1);

		if ( is_array($method_args) && !empty($method_args) ) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $method_args);
		}

		$data = curl_exec($ch);
		//$response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$xml = '';
		try {
			$xml = new SimpleXMLElement($data);

			$response_status = $xml['stat'];
			if ($response_status == 'ok') {
				return $xml;
			}
			elseif ($response_status == 'fail') {
				throw new PosterousException('Error Code ' . $xml->err['code'] . ': ' . $xml->err['msg']);
			}
			else {
				throw new PosterousException('Error: Invalid Posterous response status.');
			}
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	private function _validate($args, $valid_args) {
		$method_args = array();
		foreach($args as $key => $value) {
			if( in_array($key, $valid_args) ) {
				$method_args[$key] = $value;
			}
		}

		return $method_args;
	}

	private function _auth() {
		//checks if object has user & password, does not verify w/ Posterous
		if (isset($this->user) && isset($this->pass) && $this->user != '' && $this->pass != '') {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/* Getters & Setters */
	function user($user = NULL) {
		if ($user) {
			$this->user = $user;
		}
		return $this->user;
	}

	function pass($pass = NULL) {
		if ($pass) {
			$this->pass = $pass;
		}
		return $this->pass;
	}
}

?>
