<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['From Address'] = 'Adresa odesílatele';
$a->strings['Email address that stream items will appear to be from.'] = 'Adresa, která vysílá položky, se objeví jako odesílatel.';
$a->strings['Save Settings'] = 'Uložit nastavení';
$a->strings['Re:'] = 'Re:';
$a->strings['Friendica post'] = 'Příspěvek z Friendica';
$a->strings['Diaspora post'] = 'Příspěvek z Diaspora';
$a->strings['Feed item'] = 'Položka kanálu';
$a->strings['Email'] = 'E-mail';
$a->strings['Friendica Item'] = 'Položka z Friendica';
$a->strings['Upstream'] = 'Upstream';
$a->strings['Local'] = 'Místní';
$a->strings['Enabled'] = 'Povoleno';
$a->strings['Email Address'] = 'E-mailová adresa';
$a->strings['Leave blank to use your account email address'] = 'Ponechte prázdné pro použití vaší e-mailové adresy';
$a->strings['Exclude Likes'] = 'Vynechávat "lajky"';
$a->strings['Check this to omit mailing "Like" notifications'] = 'Zaškrtnutím vypnete posílání oznámení o "To se mi líbí"';
$a->strings['Attach Images'] = 'Připojit obrázky';
$a->strings['Download images in posts and attach them to the email.  Useful for reading email while offline.'] = 'Stahovat obrázky v příspěvcích a připojovat je k e-mailu. Užitečné pro čtení e-mailu, když jste offline.';
$a->strings['Mail Stream Settings'] = 'Nastavení Mail Stream';
