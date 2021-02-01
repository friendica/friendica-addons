<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Impressum"] = "empremta";
$a->strings["Site Owner"] = "Propietari del lloc";
$a->strings["Email Address"] = "Correu electrònic";
$a->strings["Postal Address"] = "Adreça postal";
$a->strings["The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon."] = "Cal configurar l’addon impressum<br />Afegiu com a mínim la secció <tt>owner</tt> variable del fitxer de configuració. Per a altres variables, consulteu el fitxer README de l’adjunció.";
$a->strings["Settings updated."] = "La configuració s'ha actualitzat.";
$a->strings["Submit"] = "sotmetre's";
$a->strings["The page operators name."] = "El nom dels operadors de pàgina.";
$a->strings["Site Owners Profile"] = "Perfil dels propietaris del lloc";
$a->strings["Profile address of the operator."] = "Adreça del perfil de l'operador.";
$a->strings["How to contact the operator via snail mail. You can use BBCode here."] = "Com contactar amb l'operador mitjançant correu cargol. Podeu utilitzar BBCode aquí.";
$a->strings["Notes"] = "nota";
$a->strings["Additional notes that are displayed beneath the contact information. You can use BBCode here."] = "Notes addicionals que es mostren a sota de la informació de contacte. Podeu utilitzar BBCode aquí.";
$a->strings["How to contact the operator via email. (will be displayed obfuscated)"] = "Com contactar amb l'operador per correu electrònic. (es mostrarà ofuscat)";
$a->strings["Footer note"] = "Nota de peu de pàgina";
$a->strings["Text for the footer. You can use BBCode here."] = "Text for the footer. You can use BBCode here.";
