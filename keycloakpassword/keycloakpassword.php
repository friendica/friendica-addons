<?php
/**
 * Name: Keycloak Password Auth
 * Description: Allow password-based authentication via the user's Keycloak credentials.
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

function keycloakpassword_install()
{
	Hook::register('authenticate', __FILE__, 'keycloakpassword_authenticate');
}

function keycloakpassword_request($client_id, $secret, $url, $params = [])
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
		'client_id' => $client_id,
		'grant_type' => 'password',
		'client_secret' => $secret,
		'scope' => 'openid',
	] + $params));

	$headers = array();
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$res = curl_exec($ch);

	if (curl_errno($ch)) {
		Logger::error(curl_error($ch));
	}
	curl_close($ch);

	return $res;
}

function keycloakpassword_authenticate($a, &$b)
{
	if (empty($b['password'])) {
		return;
	}

	$client_id = DI::config()->get('keycloakpassword', 'client_id', null);
	$endpoint = DI::config()->get('keycloakpassword', 'endpoint', null);
	$secret = DI::config()->get('keycloakpassword', 'secret', null);

	if (!$client_id || !$endpoint || !$secret) {
		return;
	}

	$condition = [
		'nickname' => $b['username'],
		'blocked' => false,
		'account_expired' => false,
		'account_removed' => false
	];

	try {
		$user = DBA::selectFirst('user', ['uid'], $condition);
	} catch (Exception $e) {
		return;
	}

	$json = keycloakpassword_request(
		$client_id,
		$secret,
		$endpoint . '/token',
		[
			'username' => $b['username'],
			'password' => $b['password']
		]
	);

	$res = json_decode($json, true);
	if (array_key_exists('access_token', $res) && !array_key_exists('error', $res)) {
		$b['user_record'] = User::getById($user['uid']);
		$b['authenticated'] = 1;

		// Invalidate the Keycloak session we just created, as we have no use for it.
		keycloakpassword_request(
			$client_id,
			$secret,
			$endpoint . '/logout',
			[ 'refresh_token' => res['refresh_token'] ]
		);
	}
}

function keycloakpassword_admin_input($key, $label, $description)
{
	return [
		'$' . $key => [
			$key,
			$label,
			DI::config()->get('keycloakpassword', $key),
			$description,
			true, // all the fields are required
		]
	];
}

function keycloakpassword_addon_admin(&$a, &$o)
{
	$form =
		keycloakpassword_admin_input(
			'client_id',
			DI::l10n()->t('Client ID'),
			DI::l10n()->t('The name of the OpenID Connect client you created for this addon in Keycloak.'),
		) +
		keycloakpassword_admin_input(
			'secret',
			DI::l10n()->t('Client secret'),
			DI::l10n()->t('The secret assigned to the OpenID Connect client you created for this addon in Keycloak.'),
		) +
		keycloakpassword_admin_input(
			'endpoint',
			DI::l10n()->t('OpenID Connect endpoint'),
			DI::l10n()->t(
				'URL to the Keycloak endpoint for your client. '
				. '(E.g., https://example.com/auth/realms/some-realm/protocol/openid-connect)'
			),
		) +
		[
			'$msg' => DI::session()->get('keycloakpassword-msg', false),
			'$submit'  => DI::l10n()->t('Save Settings'),
		];

	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/keycloakpassword/');
	$o = Renderer::replaceMacros($t, $form);
}

function keycloakpassword_addon_admin_post(&$a)
{
	if (!local_user()) {
		return;
	}

	$set = function ($key) {
		$val = (!empty($_POST[$key]) ? trim($_POST[$key]) : '');
		DI::config()->set('keycloakpassword', $key, $val);
	};
	$set('client_id');
	$set('secret');
	$set('endpoint');
}
