<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Beküldés a Twitterre';
$a->strings['No status.'] = 'Nincs állapot.';
$a->strings['Allow posting to Twitter'] = 'Beküldés engedélyezése a Twitterre';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Ha engedélyezve van, akkor az összes <strong>nyilvános</strong> beküldés beküldhető a hozzárendelt Twitter-fiókba. Kiválaszthatja, hogy ezt alapértelmezetten szeretné-e (itt), vagy minden egyes beküldésnél különállóan a beküldési beállításokban, amikor megírja a bejegyzést.';
$a->strings['Send public postings to Twitter by default'] = 'Nyilvános beküldések küldése a Twitterre alapértelmezetten';
$a->strings['API Key'] = 'API-kulcs';
$a->strings['API Secret'] = 'API-titok';
$a->strings['Access Token'] = 'Hozzáférési token';
$a->strings['Access Secret'] = 'Hozzáférési titok';
$a->strings['Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'] = 'Minden felhasználónak regisztrálnia kell a saját alkalmazását, hogy bejegyzést küldhessen a Twitterre. Látogassa meg a https://developer.twitter.com/en/portal/projects-and-apps oldalt egy projekt regisztrálásához. A projekten belül ezután regisztrálnia kell egy alkalmazást. A csatlakozóhoz szükséges adatokat a „Kulcsok és token” oldalon találja az alkalmazás beállításaiban.';
$a->strings['Last Status Summary'] = 'Utolsó állapot összegzése';
$a->strings['Last Status Content'] = 'Utolsó állapot tartalma';
$a->strings['Twitter Export'] = 'Twitter exportálás';
