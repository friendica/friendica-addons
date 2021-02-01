<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Twitter"] = "Lähetä Twitteriin";
$a->strings["Twitter settings updated."] = "Twitter -asetukset päivitetty.";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter tuonti/vienti/peili";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Twitter -kuluttajan avainparia ei löytynyt. Ota yhteyttä sivuston ylläpitäjään.";
$a->strings["Log in with Twitter"] = "Kirjaudu sisään Twitterillä";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Disconnect"] = "Katkaise yhteys";
$a->strings["Allow posting to Twitter"] = "Salli julkaisu Twitteriin";
$a->strings["Send public postings to Twitter by default"] = "Lähetä oletuksena kaikki julkiset julkaisut Twitteriin";
$a->strings["Mirror all posts from twitter that are no replies"] = "Peilaa kaikki julkaisut Twitteristä jotka eivät ole vastauksia";
$a->strings["Import the remote timeline"] = "Tuo etäaikajana";
$a->strings["Automatically create contacts"] = "Luo kontaktit automaattisesti";
$a->strings["Twitter post failed. Queued for retry."] = "Twitter -julkaisu epäonnistui. Jonossa uudelleenyritykseen.";
$a->strings["Settings updated."] = "Asetukset päivitetty.";
$a->strings["Consumer key"] = "Kuluttajan avain";
$a->strings["Consumer secret"] = "Kuluttajasalaisuus";
