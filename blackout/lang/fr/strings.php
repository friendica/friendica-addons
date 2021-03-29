<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "La date de fin est antérieure à la date de début du blackout, vous devriez changer ça.";
$a->strings["Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>."] = "S'il vous plaît, vérifiez à nouveau les réglages actuels du blackout. Il commencera à  <strong>%s</strong> finira à <strong>%s</strong>.";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Redirect URL"] = "Adresse URL de redirection";
$a->strings["All your visitors from the web will be redirected to this URL."] = "Tous les visiteurs venant du web seront redirigés vers cette URL.";
$a->strings["Begin of the Blackout"] = "Début de l'extinction";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Le format est <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> année, <em>MM</em>mois, <em>DD</em> jour, <em>hh</em>heure et <em>mm</em>minute.";
$a->strings["End of the Blackout"] = "Fin de l'extinction";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out while the blackout is still in place."] = "<strong>Note</strong>: La redirection sera active à partir du moment où vous pressez le bouton d'envoi. Les utilisateurs actuellement connectés ne seront pas éjectés mais <strong> ne </strong> pourront se connecter à nouveau après s'être déconnectés, pendant que le blackout est encore en cours.";
