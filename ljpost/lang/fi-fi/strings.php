<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to LiveJournal'] = 'Lähetä LiveJournaliin';
$a->strings['LiveJournal Post Settings'] = 'LiveJournal -viestin asetukset';
$a->strings['Enable LiveJournal Post Addon'] = 'Ota LiveJournal -viestilisäosa käyttöön';
$a->strings['LiveJournal username'] = 'Live Journal -käyttäjätunnus';
$a->strings['LiveJournal password'] = 'LiveJournal -salasana';
$a->strings['Post to LiveJournal by default'] = 'Lähetä LiveJournaliin oletuksena';
$a->strings['Submit'] = 'Lähetä';
