<?php

if(! function_exists("string_plural_select_da_DK")) {
function string_plural_select_da_DK($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Læg op på X';
$a->strings['No status.'] = 'Ingen status.';
$a->strings['Allow posting to Twitter'] = 'Tillad at lave opslag på X';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Hvis aktiveret, kan alle dine <strong>offentlige</strong> opslag blive lagt op på den associerede X-konto. Du kan vælge at gøre dette automatisk (her), eller separat for hvert opslag in valgmulighederne når du skriver opslaget.';
$a->strings['Send public postings to Twitter by default'] = 'Send offentlige opslag til X som standard';
$a->strings['API Key'] = 'API-nøgle';
$a->strings['API Secret'] = 'API hemmelig nøgle';
$a->strings['Access Token'] = 'Adgangs-token';
$a->strings['Access Secret'] = 'Adgangs-hemmelighed';
$a->strings['Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'] = 'Hver bruger skal registrere deres egen app for at kunne sende indlæg til X. Se venligst https://developer.twitter.com/en/portal/projects-and-apps for at registrere en app. Inde i projektet skal du registrere en app. Du kan finde de nødvendige data for connector på siden "Nøgler og tokens" i app indstillinger.';
$a->strings['Last Status Summary'] = 'Seneste status (sammendrag)';
$a->strings['Last Status Content'] = 'Seneste status (indhold)';
$a->strings['Twitter Export'] = 'Eksporter til X';
