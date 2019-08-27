<?php
	class BufferApp {
		private $client_id;
		private $client_secret;
		private $code;
		public $access_token;

		private $callback_url;
		private $authorize_url = 'https://bufferapp.com/oauth2/authorize';
		private $access_token_url = 'https://api.bufferapp.com/1/oauth2/token.json';
		private $buffer_url = 'https://api.bufferapp.com/1';

		public $ok = false;

		private $endpoints = [
			'/user' => 'get',

			'/profiles' => 'get',
			'/profiles/:id' => 'get',
			'/profiles/:id/schedules' => 'get',
			'/profiles/:id/schedules/update' => 'post',	// Array schedules [0][days][]=mon, [0][times][]=12:00

			'/updates/:id' => 'get',
			'/profiles/:id/updates/pending' => 'get',
			'/profiles/:id/updates/sent' => 'get',
			'/updates/:id/interactions' => 'get',

			'/profiles/:id/updates/reorder' => 'post',	// Array order, int offset, bool utc
			'/profiles/:id/updates/shuffle' => 'post',
			'/updates/create' => 'post',								// String text, Array profile_ids, Aool shorten, Bool now, Array media ['link'], ['description'], ['picture']
			'/updates/:id/update' => 'post',						// String text, Bool now, Array media ['link'], ['description'], ['picture'], Bool utc
			'/updates/:id/share' => 'post',
			'/updates/:id/destroy' => 'post',
			'/updates/:id/move_to_top' => 'post',

			'/links/shares' => 'get',

			'/info/configuration' => 'get',

		];

		public $errors = [
			'invalid-endpoint' => 'The endpoint you supplied does not appear to be valid.',

			'401' => 'Unauthorized.',
			'403' => 'Permission denied.',
			'404' => 'Endpoint not found.',
			'405' => 'Method not allowed.',
			'504' => 'Gateway timeout server response timeout.',
			'1000' => 'An unknown error occurred.',
			'1001' => 'Access token required.',
			'1002' => 'Not within application scope.',
			'1003' => 'Parameter not recognized.',
			'1004' => 'Required parameter missing.',
			'1005' => 'Unsupported response format.',
			'1006' => 'Parameter value not within bounds.',
			'1010' => 'Profile could not be found.',
			'1011' => 'No authorization to access profile.',
			'1012' => 'Profile did not save successfully.',
			'1013' => 'Profile schedule limit reached.',
			'1014' => 'Profile limit for user has been reached.',
			'1015' => 'Profile could not be destroyed.',
			'1016' => 'Profile buffer could not be emptied.',
			'1020' => 'Update could not be found.',
			'1021' => 'No authorization to access update.',
			'1022' => 'Update did not save successfully.',
			'1023' => 'Update limit for profile has been reached.',
			'1024' => 'Update limit for team profile has been reached.',
			'1025' => "Update was recently posted, can't post duplicate content.",
			'1026' => 'Update must be in error status to requeue.',
			'1027' => 'Update must be in buffer and not custom scheduled in order to move to top.',
			'1028' => 'Update soft limit for profile reached.',
			'1029' => 'Event type not supported.',
			'1030' => 'Media filetype not supported.',
			'1031' => 'Media filesize out of acceptable range.',
			'1032' => 'Unable to post image to LinkedIn group(s).',
			'1033' => 'Comments can only be posted to Facebook at this time.',
			'1034' => 'Cannot schedule updates in the past.',
			'1042' => 'User did not save successfully.',
			'1050' => 'Client could not be found.',
			'1051' => 'No authorization to access client.',
		];

		function __construct($client_id = '', $client_secret = '', $callback_url = '', $access_token = '') {
			if ($client_id) $this->set_client_id($client_id);
			if ($client_secret) $this->set_client_secret($client_secret);
			if ($callback_url) $this->set_callback_url($callback_url);
			if ($access_token) $this->access_token = $access_token;

			if (isset($_GET['code']) && $_GET['code']) {
				$this->code = $_GET['code'];
				$this->create_access_token_url();
			}

			if (!$access_token)
				$this->retrieve_access_token();
		}

		function go($endpoint = '', $data = '') {
			if (in_array($endpoint, array_keys($this->endpoints))) {
				$done_endpoint = $endpoint;
			} else {
				$ok = false;

				foreach (array_keys($this->endpoints) as $done_endpoint) {
					if (preg_match('/' . preg_replace('/(\:\w+)/i', '(\w+)', str_replace('/', '\/', $done_endpoint)) . '/i', $endpoint, $match)) {
						$ok = true;
						break;
					}
				}

				if (!$ok) return $this->error('invalid-endpoint');
			}

			if (!$data || !is_array($data)) $data = [];
			$data['access_token'] = $this->access_token;

			$method = $this->endpoints[$done_endpoint]; //get() or post()
			return $this->$method($this->buffer_url . $endpoint . '.json', $data);
		}

		function store_access_token() {
			$_SESSION['oauth']['buffer']['access_token'] = $this->access_token;
		}

		function retrieve_access_token() {
			$this->access_token = $_SESSION['oauth']['buffer']['access_token'];

			if ($this->access_token) {
				$this->ok = true;
			}
		}

		function error($error) {
			return (object) ['error' => $this->errors[$error]];
		}

		function create_access_token_url() {
			$data = [
				'code' => $this->code,
				'grant_type' => 'authorization_code',
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri' => $this->callback_url,
			];

			$obj = $this->post($this->access_token_url, $data);
			$this->access_token = $obj->access_token;

			$this->store_access_token();
		}

		function req($url = '', $data = '', $post = true) {
			if (!$url) return false;
			if (!$data || !is_array($data)) $data = [];

			$options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false];

			if ($post) {
				$options += [
					CURLOPT_POST => $post,
					CURLOPT_POSTFIELDS => $data
				];
			} else {
				$url .= '?' . http_build_query($data);
			}

			$ch = curl_init($url);
			curl_setopt_array($ch, $options);
			$rs = curl_exec($ch);

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($code >= 400) {
				return $this->error($code);
			}

			return json_decode($rs);
		}

		function get($url = '', $data = '') {
			return $this->req($url, $data, false);
		}

		function post($url = '', $data = '') {
			return $this->req($url, $data, true);
		}

		function get_login_url() {
			return $this->authorize_url . '?'
    		. 'client_id=' . $this->client_id
    		. '&redirect_uri=' . urlencode($this->callback_url)
    		. '&response_type=code';
		}

		function set_client_id($client_id) {
			$this->client_id = $client_id;
		}

		function set_client_secret($client_secret) {
			$this->client_secret = $client_secret;
		}

		function set_callback_url($callback_url) {
			$this->callback_url = $callback_url;
		}
	}
?>
