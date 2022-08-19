<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Insanejournal'] = 'Publier sur Insanejournal';
$a->strings['Enable InsaneJournal Post Addon'] = 'Activer l\'application complémentaire InsaneJournalPost';
$a->strings['InsaneJournal username'] = 'Identifiant du InsaneJournal';
$a->strings['InsaneJournal password'] = 'Mot de passe du InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Publier sur le InsaneJournal par défaut';
$a->strings['InsaneJournal Export'] = 'Export vers journal Insane ';
