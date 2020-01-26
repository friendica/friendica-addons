<?php
/**
 *
 * Name: Notifyall
 * Description: Send admin email message to all account holders. <b>-><a href=/notifyall TARGET = "_blank">send now!</a><-</b>
 * Version: 1.0
 * Author: Mike Macgirvin (Inital Author of the hubbwall Addon for the Hubzilla Project)
 * Author: Rabuzarus <https://friendica.kommune4.de/profile/rabuzarus> (Port to Friendica)
 */

use Friendica\Addon\notifyall\NotifyAllEmail;
use Friendica\App;
use Friendica\Database\DBA;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;

function notifyall_install()
{
	Logger::log("installed notifyall");
}

function notifyall_uninstall()
{
	Logger::log("removed notifyall");
}

function notifyall_module() {}

function notifyall_addon_admin(App $a, &$o)
{
	$o = '<div></div>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . DI::baseUrl()->get() . '/notifyall">' . DI::l10n()->t('Send email to all members') . '</a></br/>';
}


function notifyall_post(App $a)
{
	if(!is_site_admin()) {
		return;
	}

	$text = trim($_REQUEST['text']);

	if(! $text) {
		return;
	}

	// if this is a test, send it only to the admin(s)
	// admin_email might be a comma separated list, but we need "a@b','c@d','e@f
	if (intval($_REQUEST['test'])) {
		$email = DI::config()->get('config', 'admin_email');
		$email = "'" . str_replace([" ",","], ["","','"], $email) . "'";
	}
	$sql_extra = ((intval($_REQUEST['test'])) ? sprintf(" AND `email` in ( %s )", $email) : '');

	$recipients = DBA::p("SELECT DISTINCT `email` FROM `user` WHERE `verified` AND NOT `account_removed` AND NOT `account_expired` $sql_extra");

	if (! $recipients) {
		notice(DI::l10n()->t('No recipients found.') . EOL);
		return;
	}

	$notifyEmail = new NotifyAllEmail(DI::l10n(), DI::config(), DI::baseUrl(), $text);

	foreach ($recipients as $recipient) {
		DI::emailer()->send($notifyEmail->withRecipient($recipient['email']));
	}

	notice(DI::l10n()->t('Emails sent'));
	DI::baseUrl()->redirect('admin');
}

function notifyall_content(&$a)
{
	if (! is_site_admin()) {
		return '';
	}

	$title = DI::l10n()->t('Send email to all members of this Friendica instance.');

	$o = Renderer::replaceMacros(Renderer::getMarkupTemplate('notifyall_form.tpl', 'addon/notifyall/'), [
		'$title' => $title,
		'$text' => htmlspecialchars($_REQUEST['text'] ?? ''),
		'$subject' => ['subject', DI::l10n()->t('Message subject'), $_REQUEST['subject'] ?? '',''],
		'$test' => ['test',DI::l10n()->t('Test mode (only send to administrator)'), 0,''],
		'$submit' => DI::l10n()->t('Submit')
	]);

	return $o;
}
