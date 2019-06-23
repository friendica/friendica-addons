<?php
/**
 * Name: Blogger Post Connector
 * Description: Post to Blogger (or anything else which uses blogger XMLRPC API)
 * Version: 1.0
 * Status: Unsupported
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Util\Network;
use Friendica\Util\XML;

function blogger_install()
{
	Hook::register('hook_fork',               'addon/blogger/blogger.php', 'blogger_hook_fork');
	Hook::register('post_local',              'addon/blogger/blogger.php', 'blogger_post_local');
	Hook::register('notifier_normal',         'addon/blogger/blogger.php', 'blogger_send');
	Hook::register('jot_networks',            'addon/blogger/blogger.php', 'blogger_jot_nets');
	Hook::register('connector_settings',      'addon/blogger/blogger.php', 'blogger_settings');
	Hook::register('connector_settings_post', 'addon/blogger/blogger.php', 'blogger_settings_post');
}

function blogger_uninstall()
{
	Hook::unregister('hook_fork',               'addon/blogger/blogger.php', 'blogger_hook_fork');
	Hook::unregister('post_local',              'addon/blogger/blogger.php', 'blogger_post_local');
	Hook::unregister('notifier_normal',         'addon/blogger/blogger.php', 'blogger_send');
	Hook::unregister('jot_networks',            'addon/blogger/blogger.php', 'blogger_jot_nets');
	Hook::unregister('connector_settings',      'addon/blogger/blogger.php', 'blogger_settings');
	Hook::unregister('connector_settings_post', 'addon/blogger/blogger.php', 'blogger_settings_post');

	// obsolete - remove
	Hook::unregister('post_local_end',      'addon/blogger/blogger.php', 'blogger_send');
	Hook::unregister('addon_settings',      'addon/blogger/blogger.php', 'blogger_settings');
	Hook::unregister('addon_settings_post', 'addon/blogger/blogger.php', 'blogger_settings_post');
}


function blogger_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (PConfig::get(local_user(), 'blogger', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'blogger_enable',
				L10n::t('Post to blogger'),
				PConfig::get(local_user(), 'blogger', 'post_by_default')
			]
		];
	}
}


function blogger_settings(App $a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/blogger/blogger.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = PConfig::get(local_user(), 'blogger', 'post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = PConfig::get(local_user(), 'blogger', 'post_by_default');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$bl_username = PConfig::get(local_user(), 'blogger', 'bl_username');
	$bl_password = PConfig::get(local_user(), 'blogger', 'bl_password');
	$bl_blog = PConfig::get(local_user(), 'blogger', 'bl_blog');

	/* Add some HTML to the existing form */
	$s .= '<span id="settings_blogger_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_blogger_expanded\'); openClose(\'settings_blogger_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/blogger.png" /><h3 class="connector">'. L10n::t('Blogger Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_blogger_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_blogger_expanded\'); openClose(\'settings_blogger_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/blogger.png" /><h3 class="connector">'. L10n::t('Blogger Export').'</h3>';
	$s .= '</span>';

	$s .= '<div id="blogger-enable-wrapper">';
	$s .= '<label id="blogger-enable-label" for="blogger-checkbox">' . L10n::t('Enable Blogger Post Addon') . '</label>';
	$s .= '<input id="blogger-checkbox" type="checkbox" name="blogger" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="blogger-username-wrapper">';
	$s .= '<label id="blogger-username-label" for="blogger-username">' . L10n::t('Blogger username') . '</label>';
	$s .= '<input id="blogger-username" type="text" name="bl_username" value="' . $bl_username . '" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="blogger-password-wrapper">';
	$s .= '<label id="blogger-password-label" for="blogger-password">' . L10n::t('Blogger password') . '</label>';
	$s .= '<input id="blogger-password" type="password" name="bl_password" value="' . $bl_password . '" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="blogger-blog-wrapper">';
	$s .= '<label id="blogger-blog-label" for="blogger-blog">' . L10n::t('Blogger API URL') . '</label>';
	$s .= '<input id="blogger-blog" type="text" name="bl_blog" value="' . $bl_blog . '" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="blogger-bydefault-wrapper">';
	$s .= '<label id="blogger-bydefault-label" for="blogger-bydefault">' . L10n::t('Post to Blogger by default') . '</label>';
	$s .= '<input id="blogger-bydefault" type="checkbox" name="bl_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */
	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="blogger-submit" name="blogger-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';
}


function blogger_settings_post(App $a, array &$b)
{
	if (!empty($_POST['blogger-submit'])) {
		PConfig::set(local_user(), 'blogger', 'post',            defaults($_POST, 'blogger', false));
		PConfig::set(local_user(), 'blogger', 'post_by_default', defaults($_POST, 'bl_bydefault', false));
		PConfig::set(local_user(), 'blogger', 'bl_username',     trim($_POST['bl_username']));
		PConfig::set(local_user(), 'blogger', 'bl_password',     trim($_POST['bl_password']));
		PConfig::set(local_user(), 'blogger', 'bl_blog',         trim($_POST['bl_blog']));
	}
}

function blogger_hook_fork(App &$a, array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'blogger') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function blogger_post_local(App $a, array &$b)
{
	// This can probably be changed to allow editing by pointing to a different API endpoint

	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$bl_post   = intval(PConfig::get(local_user(), 'blogger', 'post'));

	$bl_enable = (($bl_post && !empty($_REQUEST['blogger_enable'])) ? intval($_REQUEST['blogger_enable']) : 0);

	if ($b['api_source'] && intval(PConfig::get(local_user(), 'blogger', 'post_by_default'))) {
		$bl_enable = 1;
	}

	if (!$bl_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'blogger';
}

function blogger_send(App $a, array &$b)
{
	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (! strstr($b['postopts'], 'blogger')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	$bl_username = XML::escape(PConfig::get($b['uid'], 'blogger', 'bl_username'));
	$bl_password = XML::escape(PConfig::get($b['uid'], 'blogger', 'bl_password'));
	$bl_blog = PConfig::get($b['uid'], 'blogger', 'bl_blog');

	if ($bl_username && $bl_password && $bl_blog) {
		$title = '<title>' . (($b['title']) ? $b['title'] : L10n::t('Post from Friendica')) . '</title>';
		$post = $title . BBCode::convert($b['body']);
		$post = XML::escape($post);

		$xml = <<< EOT
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<methodCall>
  <methodName>blogger.newPost</methodName>
  <params>
    <param><value><string/></value></param>
    <param><value><string/></value></param>
    <param><value><string>$bl_username</string></value></param>
    <param><value><string>$bl_password</string></value></param>
    <param><value><string>$post</string></value></param>
    <param><value><int>1</int></value></param>
  </params>
</methodCall>

EOT;

		Logger::log('blogger: data: ' . $xml, Logger::DATA);

		if ($bl_blog !== 'test') {
			$x = Network::post($bl_blog, $xml)->getBody();
		}

		Logger::log('posted to blogger: ' . (($x) ? $x : ''), Logger::DEBUG);
	}
}
