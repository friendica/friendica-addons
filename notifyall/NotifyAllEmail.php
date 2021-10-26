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

namespace Friendica\Addon\notifyall;

use Friendica\App\BaseURL;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Object\Email;

/**
 * Class for creating a Notify-All EMail
 */
class NotifyAllEmail extends Email
{
	public function __construct(L10n $l10n, IManageConfigValues $config, BaseURL $baseUrl, string $text)
	{
		$sitename = $config->get('config', 'sitename');

		if (empty($config->get('config', 'admin_name'))) {
			$sender_name = '"' . $l10n->t('%s Administrator', $sitename) . '"';
		} else {
			$sender_name = '"' . $l10n->t('%1$s, %2$s Administrator', $config->get('config', 'admin_name'), $sitename) . '"';
		}

		if (!$config->get('config', 'sender_email')) {
			$sender_email = 'noreply@' . $baseUrl->getHostname();
		} else {
			$sender_email = $config->get('config', 'sender_email');
		}

		$subject = $_REQUEST['subject'];

		$textversion = strip_tags(html_entity_decode(BBCode::convert(stripslashes(str_replace(["\\r", "\\n"], ["", "\n"], $text))), ENT_QUOTES, 'UTF-8'));

		$htmlversion = BBCode::convert(stripslashes(str_replace(["\\r", "\\n"], ["", "<br />\n"], $text)));

		parent::__construct($sender_name, $sender_email, $sender_email, '', $subject, $htmlversion, $textversion);
	}
}
