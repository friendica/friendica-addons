<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	return ($n != 1);;
}}
;
$a->strings["Post to Twitter"] = "An Twitter senden";
$a->strings["Twitter settings updated."] = "Twitter Einstellungen aktualisiert.";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter Import/Export/Spiegeln";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Kein Consumer Schlüsselpaar für Twitter gefunden. Bitte wende dich an den Administrator der Seite.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "Auf diesem Friendica-Server wurde das Twitter-Addon aktiviert, aber du hast deinen Account noch nicht mit deinem Twitter-Account verbunden. Klicke dazu auf die Schaltfläche unten. Du erhältst dann eine PIN von Twitter, die du dann in das Eingabefeld unten einfügst. Denk daran, den Senden-Knopf zu drücken! Nur <strong>öffentliche</strong> Beiträge werden bei Twitter veröffentlicht.";
$a->strings["Log in with Twitter"] = "bei Twitter anmelden";
$a->strings["Copy the PIN from Twitter here"] = "Kopiere die Twitter-PIN hier her";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Currently connected to: "] = "Momentan verbunden mit: ";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Wenn aktiviert können all deine <strong>öffentlichen</strong> Einträge auf dem verbundenen Twitter Konto veröffentlicht werden. Du kannst dies (hier) als Standardverhalten einstellen oder beim Schreiben eines Beitrags in den Beitragsoptionen festlegen.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Hinweis</strong>: Aufgrund deiner Privatsphären-Einstellungen (<em>Profil-Details vor unbekannten Betrachtern verbergen?</em>) wird der Link, der eventuell an an deinen Twitter Account angehängt wird, um auf den original Artikel zu verweisen, den Betrachter auf eine leere Seite führen, die ihn darüber informiert, dass der Zugriff eingeschränkt wurde.";
$a->strings["Allow posting to Twitter"] = "Veröffentlichung bei Twitter erlauben";
$a->strings["Send public postings to Twitter by default"] = "Veröffentliche öffentliche Beiträge standardmäßig bei Twitter";
$a->strings["Mirror all posts from twitter that are no replies"] = "Spiegle alle Beiträge von Twitter die keine Antworten oder wiederholten Nachrichten sind";
$a->strings["Import the remote timeline"] = "Importiere die entfernte Zeitleiste";
$a->strings["Automatically create contacts"] = "Automatisch Kontakte anlegen";
$a->strings["Clear OAuth configuration"] = "OAuth Konfiguration löschen";
$a->strings["Twitter post failed. Queued for retry."] = "Twitter post failed. Queued for retry.";
$a->strings["Settings updated."] = "Einstellungen aktualisiert.";
$a->strings["Consumer key"] = "Consumer Key";
$a->strings["Consumer secret"] = "Consumer Secret";
$a->strings["Name of the Twitter Application"] = "Name der Twitter Anwendung";
$a->strings["set this to avoid mirroring postings from ~friendica back to ~friendica"] = "Setze dies um eine Spiegelung von ~friendica zu ~friendica zu vermeiden";
