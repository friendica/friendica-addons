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

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function notifyall_module() {}

function notifyall_addon_admin(string &$o)
{
	$o = '<div></div>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . DI::baseUrl() . '/notifyall">' . DI::l10n()->t('Send email to all members') . '</a></br/>';
}


function notifyall_post()
{
	if (!DI::userSession()->isSiteAdmin()) {
		return;
	}

	$text = trim($_REQUEST['text']);

	if(! $text) {
		return;
	}

	$condition = ['account_removed' => false, 'account_expired' => false];

	// if this is a test, send it only to the admin(s)
	// admin_email might be a comma separated list, but we need "a@b','c@d','e@f
	if (intval($_REQUEST['test'])) {
		$adminEmails = \Friendica\Model\User::getAdminListForEmailing(['email']);

		$condition['email'] = array_column($adminEmails, 'email');
	}

	$recipients = DBA::p("SELECT DISTINCT `email` FROM `user`" . DBA::buildCondition($condition), $condition);

	if (! $recipients) {
		DI::sysmsg()->addNotice(DI::l10n()->t('No recipients found.'));
		return;
	}

	$notifyEmail = new NotifyAllEmail(DI::l10n(), DI::config(), DI::baseUrl(), $text);

	foreach ($recipients as $recipient) {
		DI::emailer()->send($notifyEmail->withRecipient($recipient['email']));
	}

	DI::sysmsg()->addInfo(DI::l10n()->t('Emails sent'));
	DI::baseUrl()->redirect('admin');
}

function notifyall_content()
{
	if (!DI::userSession()->isSiteAdmin()) {
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
