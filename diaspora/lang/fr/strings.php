<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to Diaspora"] = "Publier sur Diaspora";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Attention : vous pouvez toujours être joint par Diaspora avec votre identifiant Friendica <strong>%s</strong>. ";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Ce connecteur ne doit être utilisé que si vous souhaitez encore utiliser votre ancien compte Diaspora.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Quoi qu'il en soit, il est préférable de communiquer son nouvel identifiant à ses contacts Diaspora <strong>%s</strong>.";
$a->strings["All aspects"] = "Tous les aspects";
$a->strings["Public"] = "Public";
$a->strings["Post to aspect:"] = "Publier avec l'aspect:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Connecté avec votre compte Diaspora <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "Connexion impossible à votre compte Diaspora. Merci de vérifier votre identifiant (au format user@domain.tld) et votre mot de passe.";
$a->strings["Diaspora Export"] = "Export Diaspora";
$a->strings["Information"] = "Information";
$a->strings["Error"] = "Erreur";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Enable Diaspora Post Addon"] = "Activer l’extension « Publier sur Diaspora »";
$a->strings["Diaspora handle"] = "Identifiant Diaspora";
$a->strings["Diaspora password"] = "Mot de passe Diaspora";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Vie privée : Votre mot de passe Diaspora sera stocké sans encryption pour vous identifier sur votre pod. Cela signifie que l’administrateur de votre pod Diaspora peut y avoir accès.";
$a->strings["Post to Diaspora by default"] = "Publier sur Diaspora par défaut";
