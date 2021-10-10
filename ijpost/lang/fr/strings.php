<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to Insanejournal'] = 'Publier sur Insanejournal';
$a->strings['InsaneJournal Export'] = 'Export vers journal Insane ';
$a->strings['Enable InsaneJournal Post Addon'] = 'Activer l\'application complémentaire InsaneJournalPost';
$a->strings['InsaneJournal username'] = 'Identifiant du InsaneJournal';
$a->strings['InsaneJournal password'] = 'Mot de passe du InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Publier sur le InsaneJournal par défaut';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
