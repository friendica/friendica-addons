<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Method not found"] = "Método no encontrado";
$a->strings["Filtered by rule: %s"] = "Filtrado por la regla: %s";
$a->strings["Advanced Content Filter"] = "Filtro de contenido avanzado";
$a->strings["Back to Addon Settings"] = "Volver a Ajustes de Complemento";
$a->strings["Add a Rule"] = "Añadir una regla";
$a->strings["Help"] = "Ayuda";
$a->strings["Your rules"] = "Tus reglas";
$a->strings["Disabled"] = "Desactivado";
$a->strings["Enabled"] = "Activado";
$a->strings["Disable this rule"] = "Desactivar esta regla";
$a->strings["Enable this rule"] = "Activar esta regla";
$a->strings["Edit this rule"] = "Editar esta regla";
$a->strings["Edit the rule"] = "Editar regla";
$a->strings["Save this rule"] = "Guardar esta regla";
$a->strings["Delete this rule"] = "Borrar esta regla";
$a->strings["Rule"] = "Regla";
$a->strings["Close"] = "Cerrar";
$a->strings["Add new rule"] = "Agregar nueva regla";
$a->strings["Rule Name"] = "Nombre de la regla";
$a->strings["Rule Expression"] = "Expresión de la regla";
$a->strings["Cancel"] = "Cancelar";
$a->strings["You must be logged in to use this method"] = "Debe estar registrado para usar este método";
$a->strings["The rule name and expression are required."] = "El nombre y la expresión de la regla son obligatorios.";
$a->strings["Rule successfully added"] = "Regla añadida exitosamente";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "La regla no existe o no te pertenece.";
$a->strings["Rule successfully updated"] = "Regla actualizada exitosamente";
$a->strings["Rule successfully deleted"] = "Regla eliminada exitosamente";
$a->strings["Missing argument: guid."] = "Algumento faltante: guía";
$a->strings["Unknown post with guid: %s"] = "Publicacion desconocida con la guía: %s";
