<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['New Member'] = 'Nowy użytkownik';
$a->strings['Tips for New Members'] = 'Wskazówki dla nowych użytkowników';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Message'] = 'Wiadomość';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Twoja wiadomość dla nowych członków. Możesz tutaj użyć bbcode.';
$a->strings['Name of the local support group'] = 'Nazwa grupy lokalnej pomocy technicznej';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Jeśli zaznaczyłeś powyższe, określ tutaj pseudonim lokalnej grupy wsparcia (np. Pomocnicy)';
