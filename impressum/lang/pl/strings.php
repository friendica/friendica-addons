<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Impressum'] = 'Stopka redakcyjna';
$a->strings['Site Owner'] = 'Właściciel witryny';
$a->strings['Email Address'] = 'Adres e-mail';
$a->strings['Postal Address'] = 'Adres pocztowy';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'Dodatek impressum musi zostać skonfigurowany!<br />Dodaj co najmniej zmienną <tt>właściciela</tt> do pliku konfiguracyjnego. Inne zmienne można znaleźć w pliku README dodatku.';
$a->strings['Settings updated.'] = 'Ustawienia zaktualizowane.';
$a->strings['Submit'] = 'Wyślij';
$a->strings['The page operators name.'] = 'Nazwa operatora strony.';
$a->strings['Site Owners Profile'] = 'Profil właściciela witryny';
$a->strings['Profile address of the operator.'] = 'Adres profilu operatora.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Jak skontaktować się z operatorem za pośrednictwem poczty elektronicznej. Możesz użyć BBCode tutaj.';
$a->strings['Notes'] = 'Notatki';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Dodatkowe uwagi, które są wyświetlane pod danymi kontaktowymi. Możesz użyć BBCode tutaj.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Jak skontaktować się z operatorem za pośrednictwem poczty elektronicznej. (zostanie wyświetlony ukryty)';
$a->strings['Footer note'] = 'Notatka w stopce';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Tekst stopki. Tutaj można użyć BBCode.';
