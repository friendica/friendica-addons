<?php
/***
 * Name: New Member Widget
 * Description: Adds a widget for new members into the sidebar of the network page. The widget will be displayed for the 1st 14days of a account existance and contains a link to the new member page and a free-form text the admin can define.
 * Version: 1
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 ***/

use Friendica\Content\Text;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;

function newmemberwidget_install()
{
	Addon::registerHook( 'network_mod_init', 'addon/newmemberwidget/newmemberwidget.php', 'newmemberwidget_network_mod_init');
	Text::logger('newmemberwidget installed');
}

function newmemberwidget_uninstall()
{
	Addon::unregisterHook( 'network_mod_init', 'addon/newmemberwidget/newmemberwidget.php', 'newmemberwidget_network_mod_init');
}

function newmemberwidget_network_mod_init ($a, $b)
{
	if (empty($_SESSION['new_member'])) {
		return;
	}

	$t = '<div id="newmember_widget" class="widget">'.EOL;
	$t .= '<h3>'.L10n::t('New Member').'</h3>'.EOL;
	$t .= '<a href="newmember" id="newmemberwidget-tips">' . L10n::t('Tips for New Members') . '</a><br />'.EOL;

	if (Config::get('newmemberwidget','linkglobalsupport', false)) {
		$t .= '<a href="https://forum.friendi.ca/profile/helpers" target="_new">'.L10n::t('Global Support Forum').'</a><br />'.EOL;
	}

	if (Config::get('newmemberwidget','linklocalsupport', false)) {
		$t .= '<a href="'.$a->getBaseURL().'/profile/'.Config::get('newmemberwidget','localsupport').'" target="_new">'.L10n::t('Local Support Forum').'</a><br />'.EOL;
	}

	$ft = Config::get('newmemberwidget','freetext', '');
	if (!empty($ft)) {
		$t .= '<p>'.BBCode::convert(trim($ft)).'</p>';
	}

	$t .= '</div><div class="clear"></div>';
	$a->page['aside'] = $t . $a->page['aside'];
}

function newmemberwidget_addon_admin_post(&$a)
{
	$ft = ((x($_POST, 'freetext')) ? trim($_POST['freetext']) : "");
	$lsn = ((x($_POST, 'localsupportname')) ? Text::noTags(trim($_POST['localsupportname'])) : "");
	$gs = intval($_POST['linkglobalsupport']);
	$ls = intval($_POST['linklocalsupport']);
	Config::set('newmemberwidget', 'freetext',           trim($ft));
	Config::set('newmemberwidget', 'linkglobalsupport',  $gs);
	Config::set('newmemberwidget', 'linklocalsupport',   $ls);
	Config::set('newmemberwidget', 'localsupport',       trim($lsn));
}

function newmemberwidget_addon_admin(&$a, &$o)
{
	$t = Text::getMarkupTemplate('admin.tpl', 'addon/newmemberwidget');
	$o = Text::replaceMacros($t, [
	'$submit' => L10n::t('Save Settings'),
	'$freetext' => [ "freetext", L10n::t("Message"), Config::get("newmemberwidget", "freetext"), L10n::t("Your message for new members. You can use bbcode here.")],
	'$linkglobalsupport' => [ "linkglobalsupport", L10n::t('Add a link to global support forum'), Config::get('newmemberwidget', 'linkglobalsupport'), L10n::t('Should a link to the global support forum be displayed?')." (<a href='https://forum.friendi.ca/profile/helpers'>@helpers</a>)"],
	'$linklocalsupport' => [ "linklocalsupport", L10n::t('Add a link to the local support forum'), Config::get('newmemberwidget', 'linklocalsupport'), L10n::t('If you have a local support forum and want to have a link displayed in the widget, check this box.')],
	'$localsupportname' => [ "localsupportname", L10n::t('Name of the local support group'), Config::get('newmemberwidget', 'localsupport'), L10n::t('If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)')],
	]);
}
