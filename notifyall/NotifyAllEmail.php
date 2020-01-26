<?php

namespace Friendica\Addon\notifyall;

use Friendica\App\BaseURL;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Config\IConfig;
use Friendica\Core\L10n;
use Friendica\Object\Email;

/**
 * Class for creating a Notify-All EMail
 */
class NotifyAllEmail extends Email
{
	public function __construct(L10n $l10n, IConfig $config, BaseURL $baseUrl, string $text)
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
