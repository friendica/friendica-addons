<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Auf Diaspora veröffentlichen";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Denke daran: Du kannst Jederzeit über deinen Friendica Account <strong>%s</strong> von Diaspora aus erreicht werden.";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Dieser Connector ist ausschließlich dafür gedacht, deinen alten Diaspora Account noch ein wenig weiter zu betreiben.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Du solltest allerdings deinen Diaspora Kontakten deinen Friendica Account <strong>%s</strong> mitteilen, damit sie diesem folgen.";
$a->strings["All aspects"] = "Alle Aspekte";
$a->strings["Public"] = "Öffentlich";
$a->strings["Post to aspect:"] = "Bei aspect veröffentlichen:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Verbunden mit deinem Diaspora-Konto <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "Anmeldung bei deinem Diaspora-Konto fehlgeschlagen. Bitte überprüfe Handle (im Format user@domain.tld) und Passwort.";
$a->strings["Diaspora Export"] = "Diaspora-Export";
$a->strings["Information"] = "Information";
$a->strings["Error"] = "Fehler";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Enable Diaspora Post Addon"] = "Diaspora-Post-Addon aktivieren";
$a->strings["Diaspora handle"] = "Diaspora-Handle";
$a->strings["Diaspora password"] = "Diaspora-Passwort";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Datenschutzhinweis: Dein Diaspora-Passwort wird unverschlüsselt gespeichert, um dich an deinem Diaspora-Pod zu authentifizieren. Dadurch kann der Administrator deines Friendica-Knotens Zugriff darauf erlangen.";
$a->strings["Post to Diaspora by default"] = "Veröffentliche öffentliche Beiträge standardmäßig bei Diaspora";
$a->strings["Diaspora settings updated."] = "Diaspora-Einstellungen aktualisiert.";
$a->strings["Diaspora connector disabled."] = "Diaspora-Connector deaktiviert.";
