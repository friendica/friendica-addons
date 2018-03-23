<?php
/**
 *
 * Name: Notifyall
 * Description: Send admin email message to all account holders. <b>-><a href=/notifyall TARGET = "_blank">send now!</a><-</b>
 * Version: 1.0
 * Author: Mike Macgirvin (Inital Author of the hubbwall Addon for the Hubzilla Project)
 * Author: Rabuzarus <https://friendica.kommune4.de/profile/rabuzarus> (Port to Friendica)
 */

use Friendica\Content\Text\BBCode;
use Friendica\Core\L10n;
use Friendica\Util\Emailer;

function notifyall_install() {
	logger("installed notifyall");
}

function notifyall_uninstall() {
	logger("removed notifyall");
}

function notifyall_module() {}

function notifyall_addon_admin(&$a, &$o) {

	$o = '<div></div>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . z_root() . '/notifyall">' . L10n::t('Send email to all members') . '</a></br/>';
}


function notifyall_post(&$a) {
	if(! is_site_admin())
		return;

	$text = trim($_REQUEST['text']);
	if(! $text)
		return;

	$sitename = $a->config['sitename'];

	if (!x($a->config['admin_name']))
		$sender_name = L10n::t('%s Administrator', $sitename);
	else
		$sender_name = L10n::t('%1$s, %2$s Administrator', $a->config['admin_name'], $sitename);

	if (! x($a->config['sender_email']))
		$sender_email = 'noreply@' . $a->get_hostname();
	else
		$sender_email = $a->config['sender_email'];

	$subject = $_REQUEST['subject'];


	$textversion = strip_tags(html_entity_decode(BBCode::convert(stripslashes(str_replace(["\\r", "\\n"], ["", "\n"], $text))), ENT_QUOTES, 'UTF-8'));

	$htmlversion = BBCode::convert(stripslashes(str_replace(["\\r", "\\n"], ["", "<br />\n"], $text)));

	$htmlversion = bbcode(stripslashes(str_replace(array("\\r","\\n"), array("","<br />\n"),$text)));
	
	// if this is a test, send it only to the admin(s)
	// admin_email might be a comma separated list, but we need "a@b','c@d','e@f
	if (intval($_REQUEST['test'])) {
		$email = $a->config['admin_email'];
		$email = "'" . str_replace(array(" ",","), array("","','"), $email) . "'";
	}
	$sql_extra = ((intval($_REQUEST['test'])) ? sprintf(" AND `email` in ( %s )", $email) : '');

	$recips = q("SELECT DISTINCT `email` FROM `user` WHERE `verified` AND NOT `account_removed` AND NOT `account_expired` $sql_extra");

	if (! $recips) {
		notice(L10n::t('No recipients found.') . EOL);
		return;
	}

	foreach ($recips as $recip) {
		Emailer::send([
			'fromName'             => $sender_name,
			'fromEmail'            => $sender_email,
			'replyTo'              => $sender_email,
			'toEmail'              => $recip['email'],
			'messageSubject'       => $subject,
			'htmlVersion'          => $htmlversion,
			'textVersion'          => $textversion
		));
	}

	notice(L10n::t('Emails sent'));
	goaway('admin');
}

function notifyall_content(&$a)
{
	if (! is_site_admin()) {
		return;
	}

	$title = L10n::t('Send email to all members of this Friendica instance.');

	$o = replace_macros(get_markup_template('notifyall_form.tpl', 'addon/notifyall/'), [
		'$title' => $title,
		'$text' => htmlspecialchars($_REQUEST['text']),
		'$subject' => ['subject',L10n::t('Message subject'),$_REQUEST['subject'],''],
		'$test' => ['test',L10n::t('Test mode (only send to administrator)'), 0,''],
		'$submit' => L10n::t('Submit')
	]);

	return $o;
}
