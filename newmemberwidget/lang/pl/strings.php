<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['New Member'] = 'Nowy użytkownik';
$a->strings['Tips for New Members'] = 'Wskazówki dla nowych użytkowników';
$a->strings['Global Support Group'] = 'Globalna Grupa Pomocy';
$a->strings['Local Support Group'] = 'Lokalna Grupa Pomocy';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Message'] = 'Wiadomość';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Twoja wiadomość dla nowych członków. Możesz tutaj użyć bbcode.';
$a->strings['Add a link to global support group'] = 'Dodaj link do globalnej grupy pomocy';
$a->strings['Should a link to the global support group be displayed?'] = 'Czy link do globalnej grupy pomocy powinien być wyświetlany?';
$a->strings['Add a link to the local support group'] = 'Dodaj link do lokalnej grupy pomocy';
$a->strings['If you have a local support group and want to have a link displayed in the widget, check this box.'] = 'Jeśli posiadasz lokalną grupę pomocy i chcesz wyświetlać link do niej na widżecie, zaznacz tę opcję.';
$a->strings['Name of the local support group'] = 'Alias lokalnej grupy pomocy';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Jeśli zaznaczyłeś powyższe, podaj tutaj <em>alias</em> lokalnej grupy pomocy (np. pomocnicy)';
