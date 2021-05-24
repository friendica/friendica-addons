<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Twitter"] = "Auf Twitter veröffentlichen";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "Du hast keine PIN übertragen. Bitte melde dich erneut bei Twitter an, um eine neue PIN zu erhalten.";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter-Import/Export/Spiegeln";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Kein Consumer-Schlüsselpaar für Twitter gefunden. Bitte wende dich an den Administrator der Seite.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "Auf diesem Friendica-Server wurde das Twitter-Addon aktiviert, aber du hast deinen Account noch nicht mit deinem Twitter-Account verbunden. Klicke dazu auf die Schaltfläche unten. Du erhältst dann eine PIN von Twitter, die du in das Eingabefeld unten einfügst. Denk daran, den Senden-Knopf zu drücken! Nur <strong>öffentliche</strong> Beiträge werden bei Twitter veröffentlicht.";
$a->strings["Log in with Twitter"] = "bei Twitter anmelden";
$a->strings["Copy the PIN from Twitter here"] = "Kopiere die Twitter-PIN hierher";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["An error occured: "] = "Ein Fehler ist aufgetreten:";
$a->strings["Currently connected to: "] = "Momentan verbunden mit: ";
$a->strings["Disconnect"] = "Trennen";
$a->strings["Allow posting to Twitter"] = "Veröffentlichung bei Twitter erlauben";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Wenn aktiviert, können all deine <strong>öffentlichen</strong> Einträge auf dem verbundenen Twitter-Konto veröffentlicht werden. Du kannst dies (hier) als Standardverhalten einstellen oder beim Schreiben eines Beitrags in den Beitragsoptionen festlegen.";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Hinweis</strong>: Aufgrund deiner Privatsphären-Einstellungen (<em>Profil-Details vor unbekannten Betrachtern verbergen?</em>) wird der Link, der eventuell an deinen Twitter-Beitrag angehängt wird, um auf den Originalbeitrag zu verweisen, den Betrachter auf eine leere Seite führen, die ihn darüber informiert, dass der Zugriff eingeschränkt wurde.";
$a->strings["Send public postings to Twitter by default"] = "Veröffentliche öffentliche Beiträge standardmäßig bei Twitter";
$a->strings["Mirror all posts from twitter that are no replies"] = "Spiegle alle Beiträge von Twitter, die keine Antworten sind";
$a->strings["Import the remote timeline"] = "Importiere die Remote-Zeitleiste";
$a->strings["Automatically create contacts"] = "Automatisch Kontakte anlegen";
$a->strings["This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again."] = "Mit dieser Option wird automatisch ein Kontakt bei Friendica angelegt, wenn du eine Nachricht von einem bestehenden Kontakt auf Twitter erhältst. Ist die Option nicht aktiv, musst du manuell Kontakte für diejenigen deiner Twitter-Kontakte anlegen, deren Nachrichten du auf Friendica lesen möchtest. Doch falls aktiviert, lassen sich Twitter-Kontakte nicht einfach aus deinen Kontakten bei Friendica entfernen, da sie bei neuem Posting neu erstellt werden.";
$a->strings["Consumer key"] = "Consumer Key";
$a->strings["Consumer secret"] = "Consumer Secret";
$a->strings["%s on Twitter"] = "%s auf Twitter";
