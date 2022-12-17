<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['Show callstack'] = 'Afficher le callstack';
$a->strings['Show detailed performance measures in the callstack. When deactivated, only the summary will be displayed.'] = 'Affiche les performances détaillées dans le callstack. Si désactivé, seul le résumé sera affiché.';
$a->strings['Minimal time'] = 'Temps minimal';
$a->strings['Minimal time that an activity needs to be listed in the callstack.'] = 'Temps minimal qu\'une activité nécessite afin d\'être listée dans le callstack.';
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Base de données : %s/%s, Réseau : %s, Rendu : %s, Session : %s, E/S : %s, Autre : %s, Total : %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Initialisation classes : %s, Démarrage : %s, Initialisation :%s, Contenu : %s, Autre : %s, Total : %s';
