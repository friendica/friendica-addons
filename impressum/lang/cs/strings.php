<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Majitel webu';
$a->strings['Email Address'] = 'E-mailová adresa';
$a->strings['Postal Address'] = 'Poštovní adresa';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'Doplněk Impressum musí být nakonfigurován!<br />Prosím, přidejte alespoň proměnnou <tt>owner</tt> do konfiguračního souboru. Pro nastavení ostatních proměnných se seznamte s nápovědou v souboru README tohoto doplňku.';
$a->strings['Settings updated.'] = 'Nastavení aktualizována.';
$a->strings['Submit'] = 'Odeslat';
$a->strings['The page operators name.'] = 'Jméno operátora stránky.';
$a->strings['Site Owners Profile'] = 'Profil majitele webu';
$a->strings['Profile address of the operator.'] = 'Profilová adresa operátora.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Jak kontaktovat operátora prostřednictvím klasické pošty. Zde můžete použít BBCode.';
$a->strings['Notes'] = 'Poznámky';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Další poznámky, které jsou zobrazeny pod kontaktními informacemi. Zde můžete použít BBCode.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Jak konktaktovat operátora přes mail. (bude zobrazeno "obfuskovaně")';
$a->strings['Footer note'] = 'Poznámka v zápatí';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Text pro zápatí. Zde můžete použít BBCode.';
