<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Diaspora"] = "Lähetä Diasporaan";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Kirjautuminen Diasporaan epäonnistui. Tarkista että käyttäjätunnus ja salasana ovat oikein ja varmista että kirjoitit täydellisen osoitteen (mukaan lukien http...).";
$a->strings["Diaspora Export"] = "Diaspora Export";
$a->strings["Enable Diaspora Post Addon"] = "Ota Diaspora-viestilisäosa käyttöön";
$a->strings["Diaspora username"] = "Diaspora -käyttäjätunnus";
$a->strings["Diaspora password"] = "Diaspora -salasana";
$a->strings["Diaspora site URL"] = "Diaspora -sivuston URL-osoite";
$a->strings["Post to Diaspora by default"] = "Lähetä Diasporaan oletuksena";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Diaspora post failed. Queued for retry."] = "Diaspora -julkaisu epäonnistui. Jonossa uudelleenyritykseen.";
