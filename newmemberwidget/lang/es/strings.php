<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['New Member'] = 'Nuevo Miembro';
$a->strings['Tips for New Members'] = 'Consejos para Nuevos Miembros';
$a->strings['Save Settings'] = 'Guardar Ajustes';
$a->strings['Message'] = 'Mensaje';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Su mensaje para los nuevos miembros. Puede usar bbcode aquí';
$a->strings['Name of the local support group'] = 'Nombre del grupo de soporte local';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Si chequeó arriba, especifique el <em>apodo</em> del grupo de soporte local aquí (asistentes)';
