<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["New Member"] = "Nuevo Miembro";
$a->strings["Tips for New Members"] = "Consejos para Nuevos Miembros";
$a->strings["Global Support Forum"] = "Foro de Soporte Global";
$a->strings["Local Support Forum"] = "Foro de Soporte Local";
$a->strings["Save Settings"] = "Guardar Ajustes";
$a->strings["Message"] = "Mensaje";
$a->strings["Your message for new members. You can use bbcode here."] = "Su mensaje para los nuevos miembros. Puede usar bbcode aquí";
$a->strings["Add a link to global support forum"] = "Añadir un enlace al foro de soporte global";
$a->strings["Should a link to the global support forum be displayed?"] = "¿Debería mostrarse un enlace al foro de soporte global?";
$a->strings["Add a link to the local support forum"] = "Añadir un enlace al foro de soporte local";
$a->strings["If you have a local support forum and want to have a link displayed in the widget, check this box."] = "Si tiene foro de soporte local y desea que se muestre un enlace en el widget, marque esta casilla.";
$a->strings["Name of the local support group"] = "Nombre del grupo de soporte local";
$a->strings["If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)"] = "Si chequeó arriba, especifique el <em>apodo</em> del grupo de soporte local aquí (asistentes)";
