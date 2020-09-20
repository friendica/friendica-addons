<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Plaatsen op Diaspora";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Let op: vanuit Diaspora ben je altijd bereikbaar met je Friendica-handvat <strong>%s</strong>. ";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Deze connector is alleen bedoeld als je je oude diaspora-account nog enige tijd wilt gebruiken.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Het verdient echter de voorkeur dat u uw diaspora contacteert met de nieuwe handle <strong>%s</strong>.";
$a->strings["All aspects"] = "Alle aspecten";
$a->strings["Public"] = "Openbaar";
$a->strings["Post to aspect:"] = "Post naar aspect:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Verbonden met uw diaspora-account <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "U kunt niet inloggen op uw Diaspora-account. Controleer de handle (in het formaat gebruiker@domein.tld) ​​en het wachtwoord.";
$a->strings["Diaspora Export"] = "Diaspora Exporteren";
$a->strings["Information"] = "Informatie";
$a->strings["Error"] = "Fout";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Enable Diaspora Post Addon"] = "Diaspora Post Addon inschakelen";
$a->strings["Diaspora handle"] = "";
$a->strings["Diaspora password"] = "Diaspora wachtwoord";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Privacyverklaring: uw diaspora-wachtwoord wordt onversleuteld opgeslagen om u te authenticeren met uw diaspora-pod. Dit betekent dat uw Friendica-knooppuntbeheerder er toegang toe heeft.";
$a->strings["Post to Diaspora by default"] = "Plaatsen op Diaspora als standaard instellen ";
$a->strings["Diaspora settings updated."] = "";
$a->strings["Diaspora connector disabled."] = "";
