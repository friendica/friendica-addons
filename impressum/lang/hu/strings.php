<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Impressum"] = "Impresszum";
$a->strings["Site Owner"] = "Oldal tulajdonosa";
$a->strings["Email Address"] = "E-mail-cím";
$a->strings["Postal Address"] = "Postai cím";
$a->strings["The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon."] = "Az impresszum bővítményt be kell állítani!<br />Legalább az <tt>owner</tt> változót adja hozzá a beállítófájlhoz. Az egyéb változókért nézze meg a bővítmény README fájlját.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["The page operators name."] = "Az oldal üzemeltetőinek neve.";
$a->strings["Site Owners Profile"] = "Oldaltulajdonosok profilja";
$a->strings["Profile address of the operator."] = "Az üzemeltető profilcíme.";
$a->strings["How to contact the operator via snail mail. You can use BBCode here."] = "Hogyan léphet kapcsolatba az üzemeltetővel postai úton. Itt használhat BBCode-ot.";
$a->strings["Notes"] = "Megjegyzések";
$a->strings["Additional notes that are displayed beneath the contact information. You can use BBCode here."] = "További megjegyzések, amelyek a kapcsolatfelvételi információk alatt jelennek meg. Itt használhat BBCode-ot.";
$a->strings["How to contact the operator via email. (will be displayed obfuscated)"] = "Hogyan léphet kapcsolatba az üzemeltetővel e-mailben (rejtjelezve lesz megjelenítve).";
$a->strings["Footer note"] = "Lábjegyzet";
$a->strings["Text for the footer. You can use BBCode here."] = "A lábléc szövege. Itt használhat BBCode-ot.";
