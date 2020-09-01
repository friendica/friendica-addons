<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Invia a Diaspora";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Ricorda: Puoi sempre essere raggiunto da Diaspora con il tuo indirizzo Friendica <strong>%s</strong>.";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Questo connettore è utile solo se vuoi utilizzare il tuo vecchio account Diaspora per un po'.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Comunque, è preferibile che tu comunichi ai tuoi contatti Diaspora il nuovo indirizzo <strong>%s</strong>.";
$a->strings["All aspects"] = "Tutti gli aspetti";
$a->strings["Public"] = "Pubblico";
$a->strings["Post to aspect:"] = "Invia all'aspetto:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Connesso con il tuo account Diaspora <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "Non è stato possibile accedere al tuo account Diaspora. Per favore controlla l'indirizzo (nel formato utente@dominio.tld) e password.";
$a->strings["Diaspora Export"] = "Esporta Diaspora";
$a->strings["Information"] = "Informazione";
$a->strings["Error"] = "Errore";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Enable Diaspora Post Addon"] = "Abilita il componente aggiuntivo di invio a Diaspora";
$a->strings["Diaspora handle"] = "Indirizzo Diaspora";
$a->strings["Diaspora password"] = "Password Diaspora";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Nota sulla privacy: La tua password Diaspora sarà memorizzata in modo non criptato per autenticarti al tuo pod Diaspora. Questo significa che l'amministratore del tuo nodo Friendica può aver accesso a questa.";
$a->strings["Post to Diaspora by default"] = "Invia sempre a Diaspora";
$a->strings["Diaspora settings updated."] = "Impostazioni Diaspora aggiornate.";
$a->strings["Diaspora connector disabled."] = "Connettore Diaspora disabilitato.";
