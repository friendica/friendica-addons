<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Settings statement'] = 'Oświadczenie o ustawieniach';
$a->strings['A statement on the settings page explaining where the user should go to change their e-mail and password. BBCode allowed.'] = 'Oświadczenie na stronie ustawień wyjaśniające, gdzie użytkownik powinien się udać, aby zmienić swój adres e-mail i hasło. Dozwolony kod BBCode.';
$a->strings['IdP ID'] = 'ID IdP';
$a->strings['Identity provider (IdP) entity URI (e.g., https://example.com/auth/realms/user).'] = 'Identyfikator URI jednostki dostawcy tożsamości (IdP) (np. https://example.com/auth/realms/user).';
$a->strings['Client ID'] = 'ID klienta';
$a->strings['Identifier assigned to client by the identity provider (IdP).'] = 'Identyfikator przypisywany klientowi przez dostawcę tożsamości (IdP).';
$a->strings['IdP SSO URL'] = 'Adres URL logowania jednokrotnego dostawcy tożsamości';
$a->strings['The URL for your identity provider\'s SSO endpoint.'] = 'Adres URL punktu końcowego logowania jednokrotnego dostawcy tożsamości.';
$a->strings['IdP SLO request URL'] = 'Adres URL żądania SLO dostawcy tożsamości';
$a->strings['The URL for your identity provider\'s SLO request endpoint.'] = 'Adres URL punktu końcowego żądania SLO dostawcy tożsamości.';
$a->strings['IdP SLO response URL'] = 'Adres URL odpowiedzi dostawcy usług SLO';
$a->strings['The URL for your identity provider\'s SLO response endpoint.'] = 'Adres URL punktu końcowego odpowiedzi SLO Twojego dostawcy tożsamości.';
$a->strings['SP private key'] = 'Klucz prywatny SP';
$a->strings['The private key the addon should use to authenticate.'] = 'Klucz prywatny, którego dodatek powinien używać do uwierzytelniania.';
$a->strings['SP certificate'] = 'Certyfikat SP';
$a->strings['The certficate for the addon\'s private key.'] = 'Certyfikat klucza prywatnego dodatku.';
$a->strings['IdP certificate'] = 'Certyfikat IdP';
$a->strings['The x509 certficate for your identity provider.'] = 'Certyfikat x509 dla dostawcy tożsamości.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
