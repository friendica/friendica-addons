<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Use Cat as Avatar"] = "Macska használata profilképként";
$a->strings["More Random Cat!"] = "Több véletlen macskát!";
$a->strings["Reset to email Cat"] = "Visszaállítás e-mail macskára";
$a->strings["Cat Avatar Settings"] = "Macskaprofilkép-beállítások";
$a->strings["The cat hadn't found itself."] = "A macska nem találta meg önmagát.";
$a->strings["There was an error, the cat ran away."] = "Hiba történt, a macska elfutott.";
$a->strings["Profile Photos"] = "Profilfényképek";
$a->strings["Meow!"] = "Miáú!";
