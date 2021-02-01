<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Blockem"] = "Blockem";
$a->strings["Hides user's content by collapsing posts. Also replaces their avatar with generic image."] = "Skrývá uživatelský obsah zabalením příspěvků. Navíc nahrazuje avatar generickým obrázkem.";
$a->strings["Comma separated profile URLS:"] = "URL adresy profilů, oddělené čárkami:";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["BLOCKEM Settings saved."] = "Nastavení BLOCKEM uložena.";
$a->strings["Filtered user: %s"] = "Filtrovaný uživatel: %s";
$a->strings["Unblock Author"] = "Odblokovat autora";
$a->strings["Block Author"] = "Zablokovat autora";
$a->strings["blockem settings updated"] = "nastavení blockem aktualizována";
