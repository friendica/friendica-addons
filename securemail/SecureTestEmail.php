<?php
/**
 * @copyright Copyright (C) 2020, Friendica
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace Friendica\Addon\securemail;

use Friendica\App;
use Friendica\App\BaseURL;
use Friendica\Core\Config\IConfig;
use Friendica\Core\PConfig\IPConfig;
use Friendica\Object\Email;

/**
 * Class for creating a Test email for the securemail addon
 */
class SecureTestEmail extends Email
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
			[], local_user());
	}
}
