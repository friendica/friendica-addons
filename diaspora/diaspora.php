<?php

/**
 * Name: Diaspora Post Connector
 * Description: Post to Diaspora
 * Version: 0.2
 * Author: Michael Vogel <heluecht@pirati.ca>
 */

require_once 'addon/diaspora/Diaspora_Connection.php';

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\PConfig;
use Friendica\Core\Protocol;
use Friendica\Database\DBA;
use Friendica\Model\Queue;
use Friendica\Core\Worker;

function diaspora_install()
{
	Hook::register('hook_fork',               'addon/diaspora/diaspora.php', 'diaspora_hook_fork');
	Hook::register('post_local',              'addon/diaspora/diaspora.php', 'diaspora_post_local');
	Hook::register('notifier_normal',         'addon/diaspora/diaspora.php', 'diaspora_send');
	Hook::register('jot_networks',            'addon/diaspora/diaspora.php', 'diaspora_jot_nets');
	Hook::register('connector_settings',      'addon/diaspora/diaspora.php', 'diaspora_settings');
	Hook::register('connector_settings_post', 'addon/diaspora/diaspora.php', 'diaspora_settings_post');
	Hook::register('queue_predeliver',        'addon/diaspora/diaspora.php', 'diaspora_queue_hook');
}

function diaspora_uninstall()
{
	Hook::unregister('hook_fork',               'addon/diaspora/diaspora.php', 'diaspora_hook_fork');
	Hook::unregister('post_local',              'addon/diaspora/diaspora.php', 'diaspora_post_local');
	Hook::unregister('notifier_normal',         'addon/diaspora/diaspora.php', 'diaspora_send');
	Hook::unregister('jot_networks',            'addon/diaspora/diaspora.php', 'diaspora_jot_nets');
	Hook::unregister('connector_settings',      'addon/diaspora/diaspora.php', 'diaspora_settings');
	Hook::unregister('connector_settings_post', 'addon/diaspora/diaspora.php', 'diaspora_settings_post');
	Hook::unregister('queue_predeliver',        'addon/diaspora/diaspora.php', 'diaspora_queue_hook');
}

function diaspora_jot_nets(App $a, array &$jotnets_fields)
{
	if (!local_user()) {
		return;
	}

	if (PConfig::get(local_user(), 'diaspora', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'diaspora_enable',
				L10n::t('Post to Diaspora'),
				PConfig::get(local_user(), 'diaspora', 'post_by_default')
			]
		];
	}
}

function diaspora_queue_hook(App $a, &$b) {
	$hostname = $a->getHostName();

	$qi = q("SELECT * FROM `queue` WHERE `network` = '%s'",
		DBA::escape(Protocol::DIASPORA2)
	);

	if (!DBA::isResult($qi)) {
		return;
	}

	foreach ($qi as $x) {
		if ($x['network'] !== Protocol::DIASPORA2) {
			continue;
		}

		Logger::log('diaspora_queue: run');

		$r = q("SELECT `user`.* FROM `user` LEFT JOIN `contact` on `contact`.`uid` = `user`.`uid`
			WHERE `contact`.`self` = 1 AND `contact`.`id` = %d LIMIT 1",
			intval($x['cid'])
		);

		if (!DBA::isResult($r)) {
			continue;
		}

		$userdata = $r[0];

		$handle   = PConfig::get($userdata['uid'], 'diaspora', 'handle');
		$password = PConfig::get($userdata['uid'], 'diaspora', 'password');
		$aspect   = PConfig::get($userdata['uid'], 'diaspora', 'aspect');

		$success = false;

		if ($handle && $password) {
			Logger::log('diaspora_queue: able to post for user '.$handle);

			$z = unserialize($x['content']);

			$post = $z['post'];

			Logger::log('diaspora_queue: post: '.$post, Logger::DATA);

			try {
				Logger::log('diaspora_queue: prepare', Logger::DEBUG);
				$conn = new Diaspora_Connection($handle, $password);
				Logger::log('diaspora_queue: try to log in '.$handle, Logger::DEBUG);
				$conn->logIn();
				Logger::log('diaspora_queue: try to send '.$body, Logger::DEBUG);
				$conn->provider = $hostname;
				$conn->postStatusMessage($post, $aspect);

				Logger::log('diaspora_queue: send '.$userdata['uid'].' success', Logger::DEBUG);

				$success = true;

				Queue::removeItem($x['id']);
			} catch (Exception $e) {
				Logger::log("diaspora_queue: Send ".$userdata['uid']." failed: ".$e->getMessage(), Logger::DEBUG);
			}
		} else {
			Logger::log('diaspora_queue: send '.$userdata['uid'].' missing username or password', Logger::DEBUG);
		}

		if (!$success) {
			Logger::log('diaspora_queue: delayed');
			Queue::updateTime($x['id']);
		}
	}
}

function diaspora_settings(App $a, &$s)
{
	if (! local_user()) {
		return;
	}

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->getBaseURL() . '/addon/diaspora/diaspora.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variables */

	$enabled = PConfig::get(local_user(),'diaspora','post');
	$checked = (($enabled) ? ' checked="checked" ' : '');
	$css = (($enabled) ? '' : '-disabled');

	$def_enabled = PConfig::get(local_user(),'diaspora','post_by_default');

	$def_checked = (($def_enabled) ? ' checked="checked" ' : '');

	$handle = PConfig::get(local_user(), 'diaspora', 'handle');
	$password = PConfig::get(local_user(), 'diaspora', 'password');
	$aspect = PConfig::get(local_user(),'diaspora','aspect');

	$status = "";

	$r = q("SELECT `addr` FROM `contact` WHERE `self` AND `uid` = %d", intval(local_user()));

	if (DBA::isResult($r)) {
		$status = L10n::t("Please remember: You can always be reached from Diaspora with your Friendica handle %s. ", $r[0]['addr']);
		$status .= L10n::t('This connector is only meant if you still want to use your old Diaspora account for some time. ');
		$status .= L10n::t('However, it is preferred that you tell your Diaspora contacts the new handle %s instead.', $r[0]['addr']);
	}

	$aspects = false;

	if ($handle && $password) {
		$conn = new Diaspora_Connection($handle, $password);
		$conn->logIn();
		$aspects = $conn->getAspects();

		if (!$aspects) {
			$status = L10n::t("Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password.");
		}
	}

	/* Add some HTML to the existing form */

	$s .= '<span id="settings_diaspora_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_diaspora_expanded\'); openClose(\'settings_diaspora_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/diaspora-logo.png" /><h3 class="connector">'. L10n::t('Diaspora Export').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_diaspora_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_diaspora_expanded\'); openClose(\'settings_diaspora_inflated\');">';
	$s .= '<img class="connector'.$css.'" src="images/diaspora-logo.png" /><h3 class="connector">'. L10n::t('Diaspora Export').'</h3>';
	$s .= '</span>';

	if ($status) {
		$s .= '<div id="diaspora-status-wrapper"><strong>';
		$s .= $status;
		$s .= '</strong></div><div class="clear"></div>';
	}

	$s .= '<div id="diaspora-enable-wrapper">';
	$s .= '<label id="diaspora-enable-label" for="diaspora-checkbox">' . L10n::t('Enable Diaspora Post Addon') . '</label>';
	$s .= '<input id="diaspora-checkbox" type="checkbox" name="diaspora" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="diaspora-username-wrapper">';
	$s .= '<label id="diaspora-username-label" for="diaspora-username">' . L10n::t('Diaspora handle') . '</label>';
	$s .= '<input id="diaspora-username" type="text" name="handle" value="' . $handle . '" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div id="diaspora-password-wrapper">';
	$s .= '<label id="diaspora-password-label" for="diaspora-password">' . L10n::t('Diaspora password') . '</label>';
	$s .= '<input id="diaspora-password" type="password" name="password" value="' . $password . '" />';
	$s .= '</div><div class="clear"></div>';

	if ($aspects) {
		$single_aspect =  new stdClass();
		$single_aspect->id = 'all_aspects';
		$single_aspect->name = L10n::t('All aspects');
		$aspects[] = $single_aspect;

		$single_aspect =  new stdClass();
		$single_aspect->id = 'public';
		$single_aspect->name = L10n::t('Public');
		$aspects[] = $single_aspect;

		$s .= '<label id="diaspora-aspect-label" for="diaspora-aspect">' . L10n::t('Post to aspect:') . '</label>';
		$s .= '<select name="aspect" id="diaspora-aspect">';
		foreach($aspects as $single_aspect) {
			if ($single_aspect->id == $aspect)
				$s .= "<option value='".$single_aspect->id."' selected>".$single_aspect->name."</option>";
			else
				$s .= "<option value='".$single_aspect->id."'>".$single_aspect->name."</option>";
		}

		$s .= "</select>";
		$s .= '<div class="clear"></div>';
	}

	$s .= '<div id="diaspora-bydefault-wrapper">';
	$s .= '<label id="diaspora-bydefault-label" for="diaspora-bydefault">' . L10n::t('Post to Diaspora by default') . '</label>';
	$s .= '<input id="diaspora-bydefault" type="checkbox" name="diaspora_bydefault" value="1" ' . $def_checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="diaspora-submit" name="diaspora-submit" class="settings-submit" value="' . L10n::t('Save Settings') . '" /></div></div>';

}


function diaspora_settings_post(App $a, &$b)
{
	if (!empty($_POST['diaspora-submit'])) {
		PConfig::set(local_user(),'diaspora', 'post'           , intval($_POST['diaspora']));
		PConfig::set(local_user(),'diaspora', 'post_by_default', intval($_POST['diaspora_bydefault']));
		PConfig::set(local_user(),'diaspora', 'handle'         , trim($_POST['handle']));
		PConfig::set(local_user(),'diaspora', 'password'       , trim($_POST['password']));
		PConfig::set(local_user(),'diaspora', 'aspect'         , trim($_POST['aspect']));
	}
}

function diaspora_hook_fork(&$a, &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'diaspora') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function diaspora_post_local(App $a, array &$b)
{
	if ($b['edit']) {
		return;
	}

	if (!local_user() || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$diaspora_post   = intval(PConfig::get(local_user(),'diaspora','post'));

	$diaspora_enable = (($diaspora_post && !empty($_REQUEST['diaspora_enable'])) ? intval($_REQUEST['diaspora_enable']) : 0);

	if ($b['api_source'] && intval(PConfig::get(local_user(),'diaspora','post_by_default'))) {
		$diaspora_enable = 1;
	}

	if (!$diaspora_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'diaspora';
}

function diaspora_send(App $a, array &$b)
{
	$hostname = $a->getHostName();

	Logger::log('diaspora_send: invoked');

	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (!strstr($b['postopts'],'diaspora')) {
		return;
	}

	if ($b['parent'] != $b['id']) {
		return;
	}

	// Dont't post if the post doesn't belong to us.
	// This is a check for forum postings
	$self = DBA::selectFirst('contact', ['id'], ['uid' => $b['uid'], 'self' => true]);

	if ($b['contact-id'] != $self['id']) {
		return;
	}

	Logger::log('diaspora_send: prepare posting', Logger::DEBUG);

	$handle = PConfig::get($b['uid'],'diaspora','handle');
	$password = PConfig::get($b['uid'],'diaspora','password');
	$aspect = PConfig::get($b['uid'],'diaspora','aspect');

	if ($handle && $password) {
		Logger::log('diaspora_send: all values seem to be okay', Logger::DEBUG);

		$tag_arr = [];
		$tags = '';
		$x = preg_match_all('/\#\[(.*?)\](.*?)\[/',$b['tag'],$matches,PREG_SET_ORDER);

		if ($x) {
			foreach ($matches as $mtch) {
				$tag_arr[] = $mtch[2];
			}
		}

		if (count($tag_arr)) {
			$tags = implode(',',$tag_arr);
		}

		$title = $b['title'];
		$body = $b['body'];
		// Insert a newline before and after a quote
		$body = str_ireplace("[quote", "\n\n[quote", $body);
		$body = str_ireplace("[/quote]", "[/quote]\n\n", $body);

		// Removal of tags and mentions
		// #-tags
		$body = preg_replace('/#\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '#$2', $body);
 		// @-mentions
		$body = preg_replace('/@\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '@$2', $body);

		// remove multiple newlines
		do {
			$oldbody = $body;
			$body = str_replace("\n\n\n", "\n\n", $body);
		} while ($oldbody != $body);

		// convert to markdown
		$body = BBCode::toMarkdown($body);

		// Adding the title
		if (strlen($title)) {
			$body = "## ".html_entity_decode($title)."\n\n".$body;
		}

		require_once "addon/diaspora/diasphp.php";

		try {
			Logger::log('diaspora_send: prepare', Logger::DEBUG);
			$conn = new Diaspora_Connection($handle, $password);
			Logger::log('diaspora_send: try to log in '.$handle, Logger::DEBUG);
			$conn->logIn();
			Logger::log('diaspora_send: try to send '.$body, Logger::DEBUG);

			$conn->provider = $hostname;
			$conn->postStatusMessage($body, $aspect);

			Logger::log('diaspora_send: success');
		} catch (Exception $e) {
			Logger::log("diaspora_send: Error submitting the post: " . $e->getMessage());

			Logger::log('diaspora_send: requeueing '.$b['uid'], Logger::DEBUG);

			$r = q("SELECT `id` FROM `contact` WHERE `uid` = %d AND `self`", $b['uid']);
			if (count($r))
				$a->contact = $r[0]["id"];

			$s = serialize(['url' => $url, 'item' => $b['id'], 'post' => $body]);

			Worker::defer();
			notice(L10n::t('Diaspora post failed. Deferred for retry.').EOL);
		}
	}
}
