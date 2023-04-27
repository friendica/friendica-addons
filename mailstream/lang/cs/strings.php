<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n==1)) { return 0; } else if (($n>=2 && $n<=4)) { return 1; } else  { return 2; }
}}
$a->strings['From Address'] = 'Adresa odesílatele';
$a->strings['Email address that stream items will appear to be from.'] = 'Adresa, která vysílá položky, se objeví jako odesílatel.';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Re:'] = 'Re:';
$a->strings['Friendica post'] = 'Friendica příspěvky';
$a->strings['Diaspora post'] = 'Diaspora příspvěvky';
$a->strings['Feed item'] = 'Zdrojová položka';
$a->strings['Email'] = 'E-mail';
$a->strings['Friendica Item'] = 'Friendica položka';
$a->strings['Upstream'] = 'Upstream';
$a->strings['Local'] = 'Lokální';
$a->strings['Email Address'] = 'E-mailová adresa';
$a->strings['Leave blank to use your account email address'] = 'Ponechte prázdné pro použití vaší e-mailové adresy';
$a->strings['Enabled'] = 'Povoleno';
$a->strings['Mail Stream Settings'] = 'Mail Stream nastavení';
