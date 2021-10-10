<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Settings statement'] = 'Beállítások nyilatkozata';
$a->strings['A statement on the settings page explaining where the user should go to change their e-mail and password. BBCode allowed.'] = 'Egy nyilatkozat a beállítások oldalon, amely elmagyarázza, hogy a felhasználónak hova kell mennie az e-mail-címének és a jelszavának megváltoztatásához. A BBCode engedélyezett.';
$a->strings['IdP ID'] = 'IdP-azonosító';
$a->strings['Identity provider (IdP) entity URI (e.g., https://example.com/auth/realms/user).'] = 'Személyazonosság-szolgáltató (IdP) entitás URI-ja (például https://example.com/auth/realms/user).';
$a->strings['Client ID'] = 'Ügyfél-azonosító';
$a->strings['Identifier assigned to client by the identity provider (IdP).'] = 'A személyazonosság-szolgáltató (IdP) által az ügyfélhez rendelt azonosító.';
$a->strings['IdP SSO URL'] = 'IdP SSO URL';
$a->strings['The URL for your identity provider\'s SSO endpoint.'] = 'Az URL a személyazonosság-szolgáltatója egyszeri bejelentkezésének végpontjához.';
$a->strings['IdP SLO request URL'] = 'IdP SLO kérés URL';
$a->strings['The URL for your identity provider\'s SLO request endpoint.'] = 'Az URL a személyazonosság-szolgáltatója egyszeri kijelentkezési kérésének végpontjához.';
$a->strings['IdP SLO response URL'] = 'IdP SLO válasz URL';
$a->strings['The URL for your identity provider\'s SLO response endpoint.'] = 'Az URL a személyazonosság-szolgáltatója egyszeri kijelentkezési válaszának végpontjához.';
$a->strings['SP private key'] = 'Szolgáltató személyes kulcsa';
$a->strings['The private key the addon should use to authenticate.'] = 'Az a személyes kulcs, amelyet a bővítménynek a hitelesítéshez kell használnia.';
$a->strings['SP certificate'] = 'Szolgáltató tanúsítványa';
$a->strings['The certficate for the addon\'s private key.'] = 'A tanúsítvány a bővítmény személyes kulcsához.';
$a->strings['IdP certificate'] = 'IdP tanúsítvány';
$a->strings['The x509 certficate for your identity provider.'] = 'A személyazonosság-szolgáltatójának x509 tanúsítványa.';
$a->strings['Save Settings'] = 'Beállítások mentése';
