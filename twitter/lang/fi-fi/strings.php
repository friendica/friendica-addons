<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Twitter"] = "Lähetä Twitteriin";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "";
$a->strings["Twitter settings updated."] = "Twitter -asetukset päivitetty.";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter tuonti/vienti/peili";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Twitter -kuluttajan avainparia ei löytynyt. Ota yhteyttä sivuston ylläpitäjään.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "";
$a->strings["Log in with Twitter"] = "Kirjaudu sisään Twitterillä";
$a->strings["Copy the PIN from Twitter here"] = "";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Currently connected to: "] = "";
$a->strings["Disconnect"] = "Katkaise yhteys";
$a->strings["Allow posting to Twitter"] = "Salli julkaisu Twitteriin";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "";
$a->strings["Send public postings to Twitter by default"] = "Lähetä oletuksena kaikki julkiset julkaisut Twitteriin";
$a->strings["Mirror all posts from twitter that are no replies"] = "Peilaa kaikki julkaisut Twitteristä jotka eivät ole vastauksia";
$a->strings["Import the remote timeline"] = "Tuo etäaikajana";
$a->strings["Automatically create contacts"] = "Luo kontaktit automaattisesti";
$a->strings["Twitter post failed. Queued for retry."] = "Twitter -julkaisu epäonnistui. Jonossa uudelleenyritykseen.";
$a->strings["Settings updated."] = "Asetukset päivitetty.";
$a->strings["Consumer key"] = "Kuluttajan avain";
$a->strings["Consumer secret"] = "Kuluttajasalaisuus";
