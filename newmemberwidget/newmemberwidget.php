<?php
/***
 * Name: New Member Widget
 * Description: Adds a widget for new members into the sidebar of the network page. The widget will be displayed for the first 14 days of an account's existence and contains a link to the new member page and free-form text the admin can define.
 * Version: 1
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 ***/

use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

function newmemberwidget_install()
{
	Hook::register( 'network_mod_init', 'addon/newmemberwidget/newmemberwidget.php', 'newmemberwidget_network_mod_init');
	Logger::log('newmemberwidget installed');
}

function newmemberwidget_network_mod_init ($a, $b)
{
	if (empty($_SESSION['new_member'])) {
		return;
	}

	$t = '<div id="newmember_widget" class="widget">'.EOL;
	$t .= '<h3>'.DI::l10n()->t('New Member').'</h3>'.EOL;
	$t .= '<a href="newmember" id="newmemberwidget-tips">' . DI::l10n()->t('Tips for New Members') . '</a><br />'.EOL;

	if (DI::config()->get('newmemberwidget','linkglobalsupport', false)) {
		$t .= '<a href="https://forum.friendi.ca/profile/helpers" target="_new">'.DI::l10n()->t('Global Support Forum').'</a><br />'.EOL;
	}

	if (DI::config()->get('newmemberwidget','linklocalsupport', false)) {
		$t .= '<a href="'.DI::baseUrl()->get().'/profile/'.DI::config()->get('newmemberwidget','localsupport').'" target="_new">'.DI::l10n()->t('Local Support Forum').'</a><br />'.EOL;
	}

	$ft = DI::config()->get('newmemberwidget','freetext', '');
	if (!empty($ft)) {
		$t .= '<p>'.BBCode::convert(trim($ft)).'</p>';
	}

	$t .= '</div><div class="clear"></div>';
	DI::page()['aside'] = $t . DI::page()['aside'];
}

function newmemberwidget_addon_admin_post(&$a)
{
	$ft = (!empty($_POST['freetext']) ? trim($_POST['freetext']) : "");
	$lsn = (!empty($_POST['localsupportname']) ? Strings::escapeTags(trim($_POST['localsupportname'])) : "");
	$gs = intval($_POST['linkglobalsupport']);
	$ls = intval($_POST['linklocalsupport']);
	DI::config()->set('newmemberwidget', 'freetext',           trim($ft));
	DI::config()->set('newmemberwidget', 'linkglobalsupport',  $gs);
	DI::config()->set('newmemberwidget', 'linklocalsupport',   $ls);
	DI::config()->set('newmemberwidget', 'localsupport',       trim($lsn));
}

function newmemberwidget_addon_admin(&$a, &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/newmemberwidget');
	$o = Renderer::replaceMacros($t, [
	'$submit' => DI::l10n()->t('Save Settings'),
	'$freetext' => [ "freetext", DI::l10n()->t("Message"), DI::config()->get("newmemberwidget", "freetext"), DI::l10n()->t("Your message for new members. You can use bbcode here.")],
	'$linkglobalsupport' => [ "linkglobalsupport", DI::l10n()->t('Add a link to global support forum'), DI::config()->get('newmemberwidget', 'linkglobalsupport'), DI::l10n()->t('Should a link to the global support forum be displayed?')." (<a href='https://forum.friendi.ca/profile/helpers'>@helpers</a>)"],
	'$linklocalsupport' => [ "linklocalsupport", DI::l10n()->t('Add a link to the local support forum'), DI::config()->get('newmemberwidget', 'linklocalsupport'), DI::l10n()->t('If you have a local support forum and want to have a link displayed in the widget, check this box.')],
	'$localsupportname' => [ "localsupportname", DI::l10n()->t('Name of the local support group'), DI::config()->get('newmemberwidget', 'localsupport'), DI::l10n()->t('If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)')],
	]);
}
