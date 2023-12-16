<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Auf Twitter veröffentlichen';
$a->strings['No status.'] = 'Kein Status.';
$a->strings['Allow posting to Twitter'] = 'Veröffentlichung bei Twitter erlauben';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Wenn aktiviert, können all deine <strong>öffentlichen</strong> Einträge auf dem verbundenen Twitter-Konto veröffentlicht werden. Du kannst dies (hier) als Standardverhalten einstellen oder beim Schreiben eines Beitrags in den Beitragsoptionen festlegen.';
$a->strings['Send public postings to Twitter by default'] = 'Veröffentliche öffentliche Beiträge standardmäßig bei Twitter';
$a->strings['API Key'] = 'API Key';
$a->strings['API Secret'] = 'API Secret';
$a->strings['Access Token'] = 'Access Token';
$a->strings['Access Secret'] = 'Access Secret';
$a->strings['Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'] = 'Jeder Nutzer muss seine eigene App registrieren, um auf Twitter posten zu können. Bitte besuchen Sie https://developer.twitter.com/en/portal/projects-and-apps, um ein Projekt zu registrieren. Innerhalb des Projekts müssen Sie dann eine App registrieren. Die benötigten Daten für den Connector finden Sie auf der Seite "Keys and token" in den App-Einstellungen.';
$a->strings['Last Status Summary'] = 'Zusammenfassung des letzten Status';
$a->strings['Last Status Content'] = 'Inhalt des letzten Status';
$a->strings['Twitter Export'] = 'Twitter Export';
