<?php
/*
 * Name: SAML SSO and SLO
 * Description: replace login and registration with a SAML identity provider.
 * Version: 0.0
 * Author: Ryan <https://friendica.verya.pe/profile/ryan>
 */
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Session;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Util\Strings;

require_once(__DIR__ . '/vendor/autoload.php');

define("PW_LEN", 32); // number of characters to use for random passwords

function saml_module($a)
{
}

function saml_init($a)
{
	if ($a->argc < 2) {
		return;
	}

	switch ($a->argv[1]) {
		case "metadata.xml":
			saml_metadata();
			break;
		case "sso":
			saml_sso_reply($a);
			break;
		case "slo":
			saml_slo_reply();
			break;
	}
	exit();
}

function saml_metadata()
{
	try {
		$settings = new \OneLogin\Saml2\Settings(saml_settings());
		$metadata = $settings->getSPMetadata();
		$errors = $settings->validateMetadata($metadata);

		if (empty($errors)) {
			header('Content-Type: text/xml');
			echo $metadata;
		} else {
			throw new \OneLogin\Saml2\Error(
				'Invalid SP metadata: '.implode(', ', $errors),
				\OneLogin\Saml2\Error::METADATA_SP_INVALID
			);
		}
	} catch (Exception $e) {
		Logger::error($e->getMessage());
	}
}

function saml_install()
{
	Hook::register('login_hook', __FILE__, 'saml_sso_initiate');
	Hook::register('logging_out', __FILE__, 'saml_slo_initiate');
	Hook::register('head', __FILE__, 'saml_head');
	Hook::register('footer', __FILE__, 'saml_footer');
}

function saml_head(&$a, &$b)
{
	DI::page()->registerStylesheet(__DIR__ . '/saml.css');
}

function saml_footer(&$a, &$b)
{
	$fragment = addslashes(BBCode::convert(DI::config()->get('saml', 'settings_statement')));
	$b .= <<<EOL
<script>
var target=$("#settings-nickname-desc");
if (target.length) { target.append("<p>$fragment</p>"); }
</script>
EOL;
}

function saml_is_configured()
{
	return
		DI::config()->get('saml', 'idp_id') &&
		DI::config()->get('saml', 'client_id') &&
		DI::config()->get('saml', 'sso_url') &&
		DI::config()->get('saml', 'slo_request_url') &&
		DI::config()->get('saml', 'slo_response_url') &&
		DI::config()->get('saml', 'sp_key') &&
		DI::config()->get('saml', 'sp_cert') &&
		DI::config()->get('saml', 'idp_cert');
}

function saml_sso_initiate(&$a, &$b)
{
	if (!saml_is_configured()) {
		return;
	}

	$auth = new \OneLogin\Saml2\Auth(saml_settings());
	$ssoBuiltUrl = $auth->login(null, array(), false, false, true);
	$_SESSION['AuthNRequestID'] = $auth->getLastRequestID();
	header('Pragma: no-cache');
	header('Cache-Control: no-cache, must-revalidate');
	header('Location: ' . $ssoBuiltUrl);
	exit();
}

function saml_sso_reply($a)
{
	$auth = new \OneLogin\Saml2\Auth(saml_settings());
	$requestID = null;

	if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
		$requestID = $_SESSION['AuthNRequestID'];
	}

	$auth->processResponse($requestID);
	unset($_SESSION['AuthNRequestID']);

	$errors = $auth->getErrors();

	if (!empty($errors)) {
		echo "Errors encountered.";
		Logger::error(implode(', ', $errors));
		exit();
	}

	if (!$auth->isAuthenticated()) {
		echo "Not authenticated";
		exit();
	}

	$username = $auth->getNameId();
	$email = $auth->getAttributeWithFriendlyName('email')[0];
	$name = $auth->getAttributeWithFriendlyName('givenName')[0];
	$last_name = $auth->getAttributeWithFriendlyName('surname')[0];

	if (strlen($last_name)) {
		$name .= " $last_name";
	}

	if (!DBA::exists('user', ['nickname' => $username])) {
		$user = saml_create_user($username, $email, $name);
	} else {
		$user = User::getByNickname($username);
	}

	if (!empty($user['uid'])) {
		DI::auth()->setForUser($a, $user);
	}

	if (isset($_POST['RelayState'])
		&& \OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
		$auth->redirectTo($_POST['RelayState']);
	}
}

function saml_slo_initiate(&$a, &$b)
{
	$auth = new \OneLogin\Saml2\Auth(saml_settings());

	$sloBuiltUrl = $auth->logout();
	$_SESSION['LogoutRequestID'] = $auth->getLastRequestID();
	header('Pragma: no-cache');
	header('Cache-Control: no-cache, must-revalidate');
	header('Location: ' . $sloBuiltUrl);
	exit();
}

function saml_slo_reply()
{
	$auth = new \OneLogin\Saml2\Auth(saml_settings());

	if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
		$requestID = $_SESSION['LogoutRequestID'];
	} else {
		$requestID = null;
	}

	$auth->processSLO(false, $requestID);

	$errors = $auth->getErrors();

	if (empty($errors)) {
		$auth->redirectTo(DI::baseUrl());
	} else {
		Logger::error(implode(', ', $errors));
	}
}

function saml_input($key, $label, $description)
{
	return [
		'$' . $key => [
			$key,
			$label,
			DI::config()->get('saml', $key),
			$description,
		true, // all the fields are required
		]
	];
}

function saml_addon_admin(&$a, &$o)
{
	$form =
		saml_input(
			'settings_statement',
			DI::l10n()->t('Settings statement'),
			DI::l10n()->t('A statement on the settings page explaining where the user should go to change their e-mail and password. BBCode allowed.')
		) +
		saml_input(
			'idp_id',
			DI::l10n()->t('IdP ID'),
			DI::l10n()->t('Identity provider (IdP) entity URI (e.g., https://example.com/auth/realms/user).')
		) +
		saml_input(
			'client_id',
			DI::l10n()->t('Client ID'),
			DI::l10n()->t('Identifier assigned to client by the identity provider (IdP).')
		) +
		saml_input(
			'sso_url',
			DI::l10n()->t('IdP SSO URL'),
			DI::l10n()->t('The URL for your identity provider\'s SSO endpoint.')
		) +
		saml_input(
			'slo_request_url',
			DI::l10n()->t('IdP SLO request URL'),
			DI::l10n()->t('The URL for your identity provider\'s SLO request endpoint.')
		) +
		saml_input(
			'slo_response_url',
			DI::l10n()->t('IdP SLO response URL'),
			DI::l10n()->t('The URL for your identity provider\'s SLO response endpoint.')
		) +
		saml_input(
			'sp_key',
			DI::l10n()->t('SP private key'),
			DI::l10n()->t('The private key the addon should use to authenticate.')
		) +
		saml_input(
			'sp_cert',
			DI::l10n()->t('SP certificate'),
			DI::l10n()->t('The certficate for the addon\'s private key.')
		) +
		saml_input(
			'idp_cert',
			DI::l10n()->t('IdP certificate'),
			DI::l10n()->t('The x509 certficate for your identity provider.')
		) +
		[
			'$submit'  => DI::l10n()->t('Save Settings'),
		];
	$t = Renderer::getMarkupTemplate("admin.tpl", "addon/saml/");
	$o = Renderer::replaceMacros($t, $form);
}

function saml_addon_admin_post(&$a)
{
	$safeset = function ($key) {
		$val = (!empty($_POST[$key]) ? Strings::escapeTags(trim($_POST[$key])) : '');
		DI::config()->set('saml', $key, $val);
	};
	$safeset('idp_id');
	$safeset('client_id');
	$safeset('sso_url');
	$safeset('slo_request_url');
	$safeset('slo_response_url');
	$safeset('sp_key');
	$safeset('sp_cert');
	$safeset('idp_cert');

	// Not using safeset here since settings_statement is *meant* to include HTML tags.
	DI::config()->set('saml', 'settings_statement', $_POST['settings_statement']);
}

function saml_create_user($username, $email, $name)
{
	if (!strlen($email) || !strlen($name)) {
		Logger::error('Could not create user: no email or username given.');
		return false;
	}

	try {
		$strong = false;
		$bytes = openssl_random_pseudo_bytes(intval(ceil(PW_LEN * 0.75)), $strong);

		if (!$strong) {
			throw new Exception('Strong algorithm not available for PRNG.');
		}

		$user = User::create([
			'username' => $name,
			'nickname' => $username,
			'email'	=> $email,
			'password' => base64_encode($bytes), // should be at least PW_LEN long
		'verified' => true
		]);

		return $user;
	} catch (Exception $e) {
		Logger::error(
			'Exception while creating user',
			[
				'username'  => $username,
				'email'	 => $email,
				'name'	  => $name,
				'exception' => $e->getMessage(),
				'trace'	 => $e->getTraceAsString()
			]
		);

		return false;
	}
}

function saml_settings()
{
	return array(
		// If 'strict' is True, then the PHP Toolkit will reject unsigned
		// or unencrypted messages if it expects them to be signed or encrypted.
		// Also it will reject the messages if the SAML standard is not strictly
		// followed: Destination, NameId, Conditions ... are validated too.
		// Should never be set to anything else in production!
		'strict' => true,

		// Enable debug mode (to print errors).
		'debug' => false,

		// Set a BaseURL to be used instead of try to guess
		// the BaseURL of the view that process the SAML Message.
		// Ex http://sp.example.com/
		//	http://example.com/sp/
		'baseurl' => DI::baseUrl() . "/saml",

		// Service Provider Data that we are deploying.
		'sp' => array(
			// Identifier of the SP entity  (must be a URI)
			'entityId' => DI::config()->get('saml', 'client_id'),
			// Specifies info about where and how the <AuthnResponse> message MUST be
			// returned to the requester, in this case our SP.
			'assertionConsumerService' => array(
				// URL Location where the <Response> from the IdP will be returned
				'url' => DI::baseUrl() . "/saml/sso",
				// SAML protocol binding to be used when returning the <Response>
				// message. OneLogin Toolkit supports this endpoint for the
				// HTTP-POST binding only.
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
			),
			// If you need to specify requested attributes, set a
			// attributeConsumingService. nameFormat, attributeValue and
			// friendlyName can be omitted
			"attributeConsumingService"=> array(
				"serviceName" => "Friendica SAML SSO and SLO Addon",
				"serviceDescription" => "SLO and SSO support for Friendica",
				"requestedAttributes" => array(
					array(
					"uid" => "",
					"isRequired" => false,
					)
				)
			),
			// Specifies info about where and how the <Logout Response> message MUST be
			// returned to the requester, in this case our SP.
			'singleLogoutService' => array(
				// URL Location where the <Response> from the IdP will be returned
				'url' => DI::baseUrl() . "/saml/slo",
				// SAML protocol binding to be used when returning the <Response>
				// message. OneLogin Toolkit supports the HTTP-Redirect binding
				// only for this endpoint.
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
			),
			// Specifies the constraints on the name identifier to be used to
			// represent the requested subject.
			// Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported.
			'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
			// Usually x509cert and privateKey of the SP are provided by files placed at
			// the certs folder. But we can also provide them with the following parameters
			'x509cert' => DI::config()->get('saml', 'sp_cert'),
			'privateKey' => DI::config()->get('saml', 'sp_key'),
		),

		// Identity Provider Data that we want connected with our SP.
		'idp' => array(
			// Identifier of the IdP entity  (must be a URI)
			'entityId' => DI::config()->get('saml', 'idp_id'),
			// SSO endpoint info of the IdP. (Authentication Request protocol)
			'singleSignOnService' => array(
				// URL Target of the IdP where the Authentication Request Message
				// will be sent.
				'url' => DI::config()->get('saml', 'sso_url'),
				// SAML protocol binding to be used when returning the <Response>
				// message. OneLogin Toolkit supports the HTTP-Redirect binding
				// only for this endpoint.
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
			),
			// SLO endpoint info of the IdP.
			'singleLogoutService' => array(
				// URL Location of the IdP where SLO Request will be sent.
				'url' => DI::config()->get('saml', 'slo_request_url'),
				// URL location of the IdP where SLO Response will be sent (ResponseLocation)
				// if not set, url for the SLO Request will be used
				'responseUrl' => DI::config()->get('saml', 'slo_response_url'),
				// SAML protocol binding to be used when returning the <Response>
				// message. OneLogin Toolkit supports the HTTP-Redirect binding
				// only for this endpoint.
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
		   ),
		   // Public x509 certificate of the IdP
		   'x509cert' => DI::config()->get('saml', 'idp_cert'),
	   ),
	   'security' => array (
		   'wantXMLValidation' => false,

	   // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
	   // will be signed.  [Metadata of the SP will offer this info]
	   'authnRequestsSigned' => true,

	   // Indicates whether the <samlp:logoutRequest> messages sent by this SP
	   // will be signed.
	   'logoutRequestSigned' => true,

	   // Indicates whether the <samlp:logoutResponse> messages sent by this SP
	   // will be signed.
	   'logoutResponseSigned' => true,

	   /* Sign the Metadata */
	   'signMetadata' => true,
	   )
	);
}
