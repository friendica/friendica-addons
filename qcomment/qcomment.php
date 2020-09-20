<?php
/**
 * Name: Quick Comment
 * Description: Two click comments
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 * Provides a set of text "snippets" which can be inserted into a comment window by clicking on them.
 * First enable the addon in the system admin panel.
 * Then each person can tailor their choice of words in Settings->Addon Settings in the Qcomment
 * pane. Initially no qcomments are provided, but on viewing the settings page, a default set of
 * of words is suggested. These can be accepted (click Submit) or edited first. Each text line represents
 * a different qcomment.
 * Many themes will hide the qcomments above or immediately adjacent to the comment input box until
 * you wish to use them. On some themes they may be visible.
 * Wave the mouse around near the comment input box and the qcomments will show up. Click on any of
 * them to open the comment window fully and insert the qcomment. Then "Submit" will submit it.
 *
 */
use Friendica\Core\Hook;
use Friendica\DI;
use Friendica\Util\XML;

function qcomment_install()
{
	Hook::register('addon_settings'     , __FILE__, 'qcomment_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'qcomment_addon_settings_post');
	Hook::register('footer'             , __FILE__, 'qcomment_footer');
}

function qcomment_footer(\Friendica\App $a, &$b)
{
	DI::page()->registerFooterScript(__DIR__ . '/qcomment.js');
}

function qcomment_addon_settings(&$a, &$s)
{
	if (! local_user()) {
		return;
	}

	$words = DI::pConfig()->get(local_user(), 'qcomment', 'words', DI::l10n()->t(':-)') . "\n" . DI::l10n()->t(':-(') . "\n" .  DI::l10n()->t('lol'));

	$t = \Friendica\Core\Renderer::getMarkupTemplate('settings.tpl', 'addon/qcomment/');
	$s .= \Friendica\Core\Renderer::replaceMacros($t, [
		'$postpost'    => isset($_POST['qcomment-words']),
		'$header'      => DI::l10n()->t('Quick Comment Settings'),
		'$description' => DI::l10n()->t("Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies."),
		'$save'        => DI::l10n()->t('Save Settings'),
		'$words'       => ['qcomment-words', DI::l10n()->t('Enter quick comments, one per line'), $words, null, ' rows="10"'],
	]);
}

function qcomment_addon_settings_post(&$a, &$b)
{
	if (! local_user()) {
		return;
	}

	if (isset($_POST['qcomment-words'])) {
		DI::pConfig()->set(local_user(), 'qcomment', 'words', XML::escape($_POST['qcomment-words']));
	}
}
