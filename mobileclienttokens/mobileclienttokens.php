<?php
/**
 * Name: Mobile Client Tokens
 * Description: Allow the creation and revocation of tokens for authentication in lieu of the account password.
 * Version: 1.0
 * Author: Ryan <https://verya.pe/profile/ryan>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;

define('DEFAULT_LENGTH', 16);
define('DEFAULT_GROUPING', 4);

// Base-58 for legibility
define('DEFAULT_CHARPOOL', '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');

function mobileclienttokens_install()
{
	Hook::register('authenticate', __FILE__, 'mobileclienttokens_authenticate');
	Hook::register('addon_settings', __FILE__, 'mobileclienttokens_addon_settings');
	Hook::register('addon_settings_post', __FILE__, 'mobileclienttokens_addon_settings_post');
}

function mobileclienttokens_authenticate($a, &$b)
{
	// Make sure there's a *chance* of authentication.
	// Authentication via client API token is done by concatenating the user's
	// nickname with the client token identifier, with a slash between the two.
	// If there's no slash, there's no way this addon will authenticate.
	// If there's no password, the same applies.
	if (!strpos($b['username'], '/') || !$b['password']) {
		return;
	}
	$usertoken = explode('/', $b['username'], 2);
	$username = $usertoken[0];
       	$tokenid = $usertoken[1];

	$condition = [
		'nickname' => $username,
		'blocked' => false,
		'account_expired' => false,
		'account_removed' => false
	];

	try {
		$user = DBA::selectFirst('user', ['uid'], $condition);
	} catch (Exception $e) {
		return;
	}

	$token = DI::pConfig()->get($user['uid'], 'mobileclienttokens', 'token/' . $tokenid, false);

	if ($token && password_verify($b['password'], $token)) {
		$b['user_record'] = User::getById($user['uid']);
		$b['authenticated'] = 1;
		DI::session()->set('mobileclienttokens_authed', true);
	}
}

function mobileclienttokens_admin_input($key, $label, $description, $default=null)
{
	return [
		'$' . $key => [
			$key,
			$label,
			DI::config()->get('mobileclienttokens', $key, $default),
			$description,
			true, // all the fields are required
		]
	];
}

function mobileclienttokens_addon_admin(&$a, &$o)
{
	if (DI::session()->get('mobileclienttokens_authed')) {
		DI::session()->set(
			'mobileclienttokens-msg',
			DI::l10n()->t('The Mobile Client Tokens addon cannot be configured in sessions authenticated via the same.'));
	}

	$form =
		mobileclienttokens_admin_input(
			'length',
			DI::l10n()->t('Token length'),
			DI::l10n()->t('How long should generated API tokens be?'),
			DEFAULT_LENGTH
		) +
		mobileclienttokens_admin_input(
			'grouping',
			DI::l10n()->t('Grouping length'),
			DI::l10n()->t('For readability, tokens are displayed with spaces between groups of characters. '
				. 'How long should these groups be?'),
			DEFAULT_GROUPING
		) +
		mobileclienttokens_admin_input(
			'charpool',
			DI::l10n()->t('Character pool'),
			DI::l10n()->t('What characters should be used to generate the API tokens?'),
			DEFAULT_CHARPOOL
		) +
		[
			'$msg' => DI::session()->get('mobileclienttokens-msg', false),
			'$submit'  => DI::l10n()->t('Save Settings'),
		];

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/mobileclienttokens/');
	$o = Renderer::replaceMacros($t, $form);

	DI::session()->remove('mobileclienttokens-msg');
}

function mobileclienttokens_addon_admin_post(&$a)
{
	if (DI::session()->get('mobileclienttokens_authed')) {
		return;
	}

	if (!local_user()) {
		return;
	}

	$set = function ($key) {
		$val = (!empty($_POST[$key]) ? trim($_POST[$key]) : '');
		DI::config()->set('mobileclienttokens', $key, $val);
	};
	$set('length');
	$set('grouping');
	$set('charpool');
}

function mobileclienttokens_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	if (DI::session()->get('mobileclienttokens_authed')) {
		DI::session()->set(
			'mobileclienttokens-msg',
			DI::l10n()->t('Mobile client tokens cannot be added or removed in sessions authenticated via the same.'));
	}

	$tokens = mobileclienttokens_gettokens();

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/mobileclienttokens/');
	$s .= Renderer::replaceMacros($t, [
		'$create' => DI::l10n()->t('Create'),
		'$delete' => DI::l10n()->t('Delete'),
		'$header' => DI::l10n()->t('Mobile Client Tokens'),
		'$msg' => DI::session()->get('mobileclienttokens-msg', false),
		'$newtoken' => DI::session()->get('mobileclienttokens-new', false),
		'$newtokenid' => [
			'newtokenid',
			DI::l10n()->t('Token ID'),
			'',
			DI::l10n()->t('A name for your new mobile client token.'),
		],
		'$posted' => !empty($_POST['mobileclienttokens-create']) || !empty($_POST['mobileclienttokens-delete']),
		'$deletetokenid' => [
			'deletetokenid',
			DI::l10n()->t('Tokens'),
			empty($tokens) ? null : $tokens[0],
			DI::l10n()->t('Pick an existing token to delete/revoke.'),
			array_combine($tokens, $tokens)
		],
		'$tokens' => empty($tokens) ? false : array_combine($tokens, $tokens),
	]);

	DI::session()->remove('mobileclienttokens-msg');
	DI::session()->remove('mobileclienttokens-new');
}

function mobileclienttokens_addon_settings_post(App $a, &$s)
{
	if (!local_user() || DI::session()->get('mobileclienttokens_authed')) {
		return;
	}

	if (!empty($_POST['mobileclienttokens-create'])) {
		mobileclienttokens_create($_POST['newtokenid']);
	} else if (!empty($_POST['mobileclienttokens-delete'])) {
		mobileclienttokens_delete($_POST['deletetokenid']);
	}
}

function mobileclienttokens_create($tokenid) {
	if (empty(trim($tokenid))) {
		DI::session()->set('mobileclienttokens-msg', DI::l10n()->t('Error: No token ID provided!'));
		return;
	}
	$charpool = DI::config()->get('mobileclienttokens', 'charpool', DEFAULT_CHARPOOL);
	$grouping = DI::config()->get('mobileclienttokens', 'grouping', DEFAULT_GROUPING);
	$length = DI::config()->get('mobileclienttokens', 'length', DEFAULT_LENGTH);

	$lencharpool = mb_strlen($charpool);

	$password = [];

	for ($i = 0; $i < $length; $i++) {
		if ($i % $grouping == 0) {
			$password[] = '';
		}
		$password[$i / $grouping] .= $charpool[random_int(0, $lencharpool)];
	}

	$tokens = mobileclienttokens_gettokens();

	if (in_array($tokenid, $tokens)) {
		DI::session()->set('mobileclienttokens-msg', DI::l10n()->t('Error: A token with that ID already exists.'));
		return;
	}
	$tokens[] = $tokenid;

	DI::pConfig()->set(
		local_user(),
		'mobileclienttokens',
		'tokens',
		implode('/', $tokens)
	);

	DI::pConfig()->set(
		local_user(),
		'mobileclienttokens',
		'token/' . $tokenid,
		password_hash(implode('', $password), PASSWORD_DEFAULT)
	);

	$user = DBA::selectFirst('user', ['nickname'], ['uid' => local_user()]);
	DI::session()->set(
		'mobileclienttokens-new',
		[
			'username' => $user['nickname'] . '/' . $tokenid,
			'password' => $password
		]
	);
}

function mobileclienttokens_delete($tokenid) {
	$tokens = mobileclienttokens_gettokens();

	if (empty($tokens)) {
		return;
	}

	if (!in_array($tokenid, $tokens)) {
		DI::session()->set('mobileclienttokens-msg', DI::l10n()->t('Error: Could not find token to delete it!'));
		return;
	}
	$tokens = array_diff($tokens, [$tokenid]);

	DI::pConfig()->set(local_user(), 'mobileclienttokens', 'tokens', implode('/', $tokens));
	DI::pConfig()->delete(local_user(), 'mobileclienttokens', $tokenid);
	DI::session()->set('mobileclienttokens-msg', DI::l10n()->t('Token successfully deleted!'));
}

function mobileclienttokens_gettokens() {
	$tokens = explode('/', DI::pConfig()->get(
		local_user(),
		'mobileclienttokens',
		'tokens',
		''
	));

	if (!$tokens) {
		$tokens = [];
	} else if (is_string($tokens)) {
		$tokens = [$tokens];
	}
	return array_filter($tokens);
}
