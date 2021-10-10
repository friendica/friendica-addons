<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Settings statement'] = 'Anweisungen zu den Einstellungen';
$a->strings['A statement on the settings page explaining where the user should go to change their e-mail and password. BBCode allowed.'] = 'Erörternde Anweisungen, die auf der Einstellungsseite angezeigt werden und den Nutzern erklären, wo sie ihre E-Mail Adressen und Passwörter ändern sollen. BBCode wird unterstützt.';
$a->strings['IdP ID'] = 'IdP ID';
$a->strings['Identity provider (IdP) entity URI (e.g., https://example.com/auth/realms/user).'] = 'Entitäten-URL des Identitätsanbieters (IdP)  (z.B. https://example.com/auth/realms/user).';
$a->strings['Client ID'] = 'Client-ID';
$a->strings['Identifier assigned to client by the identity provider (IdP).'] = 'Kennung des Clients, die vom Identitätsanbieter (IdP) zugewiesen wurde.';
$a->strings['IdP SSO URL'] = 'IdP SSO URL';
$a->strings['The URL for your identity provider\'s SSO endpoint.'] = 'Die URL des SSO-Endpunktes deines Identitätenanbieters.';
$a->strings['IdP SLO request URL'] = 'IdP-SLO-Anfrage-URL';
$a->strings['The URL for your identity provider\'s SLO request endpoint.'] = 'Die URL des SLO-Anfrage-Endpunktes deines Identitätenanbieters.';
$a->strings['IdP SLO response URL'] = 'IdP-SLO-Antwort-URL';
$a->strings['The URL for your identity provider\'s SLO response endpoint.'] = 'Die URL des SLO-Antwort-Endpunktes deines Identitätenanbieters.';
$a->strings['SP private key'] = 'Privater Schlüssel (SP)';
$a->strings['The private key the addon should use to authenticate.'] = 'Der private Schlüssel, den das Addon zur Authentifizierung verwenden soll.';
$a->strings['SP certificate'] = 'SP-Zertifikat';
$a->strings['The certficate for the addon\'s private key.'] = 'Das Zertifikat für den privaten Schlüssel des Addons.';
$a->strings['IdP certificate'] = 'IdP-Zertifikat';
$a->strings['The x509 certficate for your identity provider.'] = 'Das x509-Zertifikat deines Identitätanbieters.';
$a->strings['Save Settings'] = 'Einstellungen speichern';
