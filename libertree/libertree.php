<?php
/**
 * Name: Libertree Post Connector
 * Description: Post to libertree accounts
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/u/tony>
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Post;

function libertree_install()
{
	Hook::register('hook_fork',            'addon/libertree/libertree.php', 'libertree_hook_fork');
	Hook::register('post_local',           'addon/libertree/libertree.php', 'libertree_post_local');
	Hook::register('notifier_normal',      'addon/libertree/libertree.php', 'libertree_send');
	Hook::register('jot_networks',         'addon/libertree/libertree.php', 'libertree_jot_nets');
	Hook::register('connector_settings',      'addon/libertree/libertree.php', 'libertree_settings');
	Hook::register('connector_settings_post', 'addon/libertree/libertree.php', 'libertree_settings_post');
}

function libertree_jot_nets(App &$a, array &$jotnets_fields)
{
    if(! local_user()) {
        return;
    }

	if (DI::pConfig()->get(local_user(), 'libertree', 'post')) {
		$jotnets_fields[] = [
			'type' => 'checkbox',
			'field' => [
				'libertree_enable',
				DI::l10n()->t('Post to libertree'),
				DI::pConfig()->get(local_user(), 'libertree', 'post_by_default')
			]
		];
	}
}


function libertree_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    DI::page()['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . DI::baseUrl()->get() . '/addon/libertree/libertree.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = DI::pConfig()->get(local_user(),'libertree','post');
    $checked = (($enabled) ? ' checked="checked" ' : '');
    $css = (($enabled) ? '' : '-disabled');

    $def_enabled = DI::pConfig()->get(local_user(),'libertree','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

    $ltree_api_token = DI::pConfig()->get(local_user(), 'libertree', 'libertree_api_token');
    $ltree_url = DI::pConfig()->get(local_user(), 'libertree', 'libertree_url');


    /* Add some HTML to the existing form */

    $s .= '<span id="settings_libertree_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_libertree_expanded\'); openClose(\'settings_libertree_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/libertree.png" /><h3 class="connector">'. DI::l10n()->t('libertree Export').'</h3>';
    $s .= '</span>';
    $s .= '<div id="settings_libertree_expanded" class="settings-block" style="display: none;">';
    $s .= '<span class="fakelink" onclick="openClose(\'settings_libertree_expanded\'); openClose(\'settings_libertree_inflated\');">';
    $s .= '<img class="connector'.$css.'" src="images/libertree.png" /><h3 class="connector">'. DI::l10n()->t('libertree Export').'</h3>';
    $s .= '</span>';

    $s .= '<div id="libertree-enable-wrapper">';
    $s .= '<label id="libertree-enable-label" for="libertree-checkbox">' . DI::l10n()->t('Enable Libertree Post Addon') . '</label>';
    $s .= '<input id="libertree-checkbox" type="checkbox" name="libertree" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-api_token-wrapper">';
    $s .= '<label id="libertree-api_token-label" for="libertree-api_token">' . DI::l10n()->t('Libertree API token') . '</label>';
    $s .= '<input id="libertree-api_token" type="text" name="libertree_api_token" value="' . $ltree_api_token . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-url-wrapper">';
    $s .= '<label id="libertree-url-label" for="libertree-url">' . DI::l10n()->t('Libertree site URL') . '</label>';
    $s .= '<input id="libertree-url" type="text" name="libertree_url" value="' . $ltree_url . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-bydefault-wrapper">';
    $s .= '<label id="libertree-bydefault-label" for="libertree-bydefault">' . DI::l10n()->t('Post to Libertree by default') . '</label>';
    $s .= '<input id="libertree-bydefault" type="checkbox" name="libertree_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="libertree-submit" name="libertree-submit" class="settings-submit" value="' . DI::l10n()->t('Save Settings') . '" /></div></div>';

}


function libertree_settings_post(&$a,&$b) {

	if(!empty($_POST['libertree-submit'])) {

		DI::pConfig()->set(local_user(),'libertree','post',intval($_POST['libertree']));
		DI::pConfig()->set(local_user(),'libertree','post_by_default',intval($_POST['libertree_bydefault']));
		DI::pConfig()->set(local_user(),'libertree','libertree_api_token',trim($_POST['libertree_api_token']));
		DI::pConfig()->set(local_user(),'libertree','libertree_url',trim($_POST['libertree_url']));

	}

}

function libertree_hook_fork(App &$a, array &$b)
{
	if ($b['name'] != 'notifier_normal') {
		return;
	}

	$post = $b['data'];

	if ($post['deleted'] || $post['private'] || ($post['created'] !== $post['edited']) ||
		!strstr($post['postopts'], 'libertree') || ($post['parent'] != $post['id'])) {
		$b['execute'] = false;
		return;
	}
}

function libertree_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if ($b['edit']) {
		return;
	}

	if ((! local_user()) || (local_user() != $b['uid'])) {
		return;
	}

	if ($b['private'] || $b['parent']) {
		return;
	}

	$ltree_post   = intval(DI::pConfig()->get(local_user(),'libertree','post'));

	$ltree_enable = (($ltree_post && !empty($_REQUEST['libertree_enable'])) ? intval($_REQUEST['libertree_enable']) : 0);

	if ($b['api_source'] && intval(DI::pConfig()->get(local_user(),'libertree','post_by_default'))) {
		$ltree_enable = 1;
	}

	if (!$ltree_enable) {
		return;
	}

	if (strlen($b['postopts'])) {
		$b['postopts'] .= ',';
	}

	$b['postopts'] .= 'libertree';
}




function libertree_send(&$a,&$b) {

	Logger::notice('libertree_send: invoked');

	if ($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited'])) {
		return;
	}

	if (! strstr($b['postopts'],'libertree')) {
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

	$b['body'] = Post\Media::addAttachmentsToBody($b['uri-id'], $b['body']);

	$ltree_api_token = DI::pConfig()->get($b['uid'],'libertree','libertree_api_token');
	$ltree_url = DI::pConfig()->get($b['uid'],'libertree','libertree_url');
	$ltree_blog = "$ltree_url/api/v1/posts/create/?token=$ltree_api_token";
	$ltree_source = DI::baseUrl()->getHostname();

	if ($b['app'] != "")
		$ltree_source .= " (".$b['app'].")";

	if($ltree_url && $ltree_api_token && $ltree_blog && $ltree_source) {
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
		$body = BBCode::toMarkdown($body, false);

		// Adding the title
		if(strlen($title))
			$body = "## ".html_entity_decode($title)."\n\n".$body;


		$params = [
			'text' => $body,
			'source' => $ltree_source
		//	'token' => $ltree_api_token
		];

		$result = DI::httpClient()->post($ltree_blog, $params)->getBody();
		Logger::notice('libertree: ' . $result);
	}
}
