<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to LiveJournal'] = 'In LiveJournal veröffentlichen.';
$a->strings['LiveJournal Post Settings'] = 'LiveJournal-Veröffentlichungs-Einstellungen';
$a->strings['Enable LiveJournal Post Addon'] = 'LiveJournal-Post-Addon aktivieren';
$a->strings['LiveJournal username'] = 'LiveJournal-Benutzername';
$a->strings['LiveJournal password'] = 'LiveJournal-Passwort';
$a->strings['Post to LiveJournal by default'] = 'Standardmäßig bei LiveJournal veröffentlichen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
