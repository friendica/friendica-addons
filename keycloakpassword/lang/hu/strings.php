<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Client ID'] = 'Ügyfél-azonosító';
$a->strings['The name of the OpenID Connect client you created for this addon in Keycloak.'] = 'Az OpenID Connect ügyfelének neve, amelyet ehhez a bővítményhez hozott létre a Keycloak alkalmazásban.';
$a->strings['Client secret'] = 'Ügyféltitok';
$a->strings['The secret assigned to the OpenID Connect client you created for this addon in Keycloak.'] = 'Ahhoz az OpenID Connect ügyfélhez hozzárendelt titok, amelyet ehhez a bővítményhez hozott létre a Keycloak alkalmazásban.';
$a->strings['OpenID Connect endpoint'] = 'OpenID Connect végpont';
$a->strings['URL to the Keycloak endpoint for your client. (E.g., https://example.com/auth/realms/some-realm/protocol/openid-connect)'] = 'Az ügyfél URL-e a Keycloak végponthoz (például https://example.com/auth/realms/some-realm/protocol/openid-connect).';
$a->strings['Save Settings'] = 'Beállítások mentése';
