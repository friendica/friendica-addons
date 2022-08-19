<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Vous pouvez modifier ici tous les paramètres système pour connecter et joindre les chaînes grâce à la barre latérale. Notez que les changement effectués ici, n\'affectent la sélection de chaînes qui si vous êtes connecté.';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Chaîne(s) à connecter automatiquement (séparée(s) par une virgule)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Liste des chaînes devant être automatiquement connectées au lancement de l\'application.';
$a->strings['Popular Channels (comma separated)'] = 'Chaînes populaires (séparées par une virgule)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'La liste des chaînes populaires et leur lien sera affichée sur le coté pour un accès facile';
$a->strings['IRC Settings'] = 'Paramètres de l\'IRC';
$a->strings['IRC Chatroom'] = 'Salon IRC';
$a->strings['Popular Channels'] = 'Chaînes populaires';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
