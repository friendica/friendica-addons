<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Impressum"] = "Impressum";
$a->strings["Site Owner"] = "Propietario de la página";
$a->strings["Email Address"] = "Dirección de Email";
$a->strings["Postal Address"] = "Dirección postal";
$a->strings["The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon."] = "¡El addon impressum necesita ser configurado!<br />Por favor añada al menos <tt>propietario</tt> disponible en su archivo de configuración. Para otros valores por favor dirígase al archivo README del addon.";
$a->strings["Save Settings"] = "Guardar Ajustes";
$a->strings["The page operators name."] = "El nombre de los operadores de página";
$a->strings["Site Owners Profile"] = "Perfil del Propietario de la Página";
$a->strings["Profile address of the operator."] = "Dirección del perfil del operador.";
$a->strings["How to contact the operator via snail mail. You can use BBCode here."] = "Cómo contactar con el operador via correo ordinario. Puede usar BBCode aquí.";
$a->strings["Notes"] = "Notas";
$a->strings["Additional notes that are displayed beneath the contact information. You can use BBCode here."] = "Notas adicionales que se muestran bajo la información de contacto. Puede usar BBCode aquí.";
$a->strings["How to contact the operator via email. (will be displayed obfuscated)"] = "Cómo contactar con el operador via email. (se mostrará ofuscado)";
$a->strings["Footer note"] = "Nota de pie de página";
$a->strings["Text for the footer. You can use BBCode here."] = "Texto para el pie de página. Puede usar BBCode aquí";
