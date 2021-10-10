<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Send email to all members'] = 'Wyślij e-mail do wszystkich członków';
$a->strings['%s Administrator'] = '%s Administrator';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, %2$s Administrator';
$a->strings['No recipients found.'] = 'Nie znaleziono adresatów.';
$a->strings['Emails sent'] = 'Wysłane wiadomości e-mail';
$a->strings['Send email to all members of this Friendica instance.'] = 'Wyślij wiadomość e-mail do wszystkich członków tej instancji Friendica.';
$a->strings['Message subject'] = 'Temat wiadomości';
$a->strings['Test mode (only send to administrator)'] = 'Tryb testowy (wysyłany tylko do administratora)';
$a->strings['Submit'] = 'Zatwierdź';
