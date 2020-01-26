<?php

namespace Friendica\Addon\securemail;

use Friendica\App;
use Friendica\App\BaseURL;
use Friendica\Core\Config\IConfig;
use Friendica\Core\PConfig\IPConfig;
use Friendica\Object\EMail;

/**
 * Class for creating a Test email for the securemail addon
 */
class SecureTestEMail extends EMail
{
	public function __construct(App $a, IConfig $config, IPConfig $pConfig, BaseURL $baseUrl)
	{
		$sitename = $config->get('config', 'sitename');

		$hostname = $baseUrl->getHostname();
		if (strpos($hostname, ':')) {
			$hostname = substr($hostname, 0, strpos($hostname, ':'));
		}

		$sender_email = $config->get('config', 'sender_email');
		if (empty($sender_email)) {
			$sender_email = 'noreply@' . $hostname;
		}

		$subject = 'Friendica - Secure Mail - Test';
		$message = 'This is a test message from your Friendica Secure Mail addon.';

		// enable addon for test
		$pConfig->set(local_user(), 'securemail', 'enable', 1);

		parent::__construct($sitename, $sender_email, $sender_email, $a->user['email'],
			$subject, "<p>{$message}</p>", $message,
			'', local_user());
	}
}
