<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Post to Twitter"] = "Poslat příspěvek na Twitter";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "";
$a->strings["Twitter settings updated."] = "Nastavení Twitteru aktualizováno.";
$a->strings["Twitter Import/Export/Mirror"] = "Import/Export/Zrcadlení Twitteru";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Nenalezen žádný spotřebitelský páru klíčů pro Twitter. Obraťte se na administrátora webu.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "";
$a->strings["Log in with Twitter"] = "Přihlásit se s Twitter";
$a->strings["Copy the PIN from Twitter here"] = "Zkopírujte sem PIN z Twitteru";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Currently connected to: "] = "V současné době připojen k:";
$a->strings["Disconnect"] = "Odpojit";
$a->strings["Allow posting to Twitter"] = "Povolit odesílání na Twitter";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Je-li povoleno, všechny Vaše <strong>veřejné</strong> příspěvky mohou být zaslány na související Twitter účet. Můžete si vybrat, zda-li toto bude výchozí nastavení (zde), nebo budete mít možnost si vybrat požadované chování při psaní každého příspěvku.";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "";
$a->strings["Send public postings to Twitter by default"] = "Defaultně zasílat veřejné komentáře na Twitter";
$a->strings["Mirror all posts from twitter that are no replies"] = "";
$a->strings["Import the remote timeline"] = "";
$a->strings["Automatically create contacts"] = "Automaticky vytvořit kontakty";
$a->strings["This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again."] = "";
$a->strings["Twitter post failed. Queued for retry."] = "";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
$a->strings["Consumer key"] = "Consumer key";
$a->strings["Consumer secret"] = "Consumer secret";
