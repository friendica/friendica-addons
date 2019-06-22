<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "La date de fin est antérieure au début de l'extinction, vous devriez corriger cela.";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Merci de vérifier que le paramétrage actuel pour l'extinction. Le début sera <strong>%s</strong> et se terminera <strong>%s</strong>";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Redirect URL"] = "Adresse URL de redirection";
$a->strings["all your visitors from the web will be redirected to this URL"] = "Tous vos visiteurs venant du web seront redirigés vers cette URL.";
$a->strings["Begin of the Blackout"] = "Début de l'extinction";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Le format est <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> année, <em>MM</em>mois, <em>DD</em> jour, <em>hh</em>heure et <em>mm</em>minute.";
$a->strings["End of the Blackout"] = "Fin de l'extinction";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Note</strong>: La redirection sera active à partir du moment ou vous appuierez sur le bouton envoyer. Les utilisateurs identifiés ne seront <strong>pas</strong> déconnectés mais ne pourront pas se reconnecter après s'être déconnectés tant que l'extinction est en place.";
