<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Insanejournal'] = 'Publicar en Insanejournal';
$a->strings['InsaneJournal Export'] = 'InsaneJournal Exportar';
$a->strings['Enable InsaneJournal Post Addon'] = 'Habilitar el Plugin de Entrada InsaneJournal';
$a->strings['InsaneJournal username'] = 'Nombre de usuario InsaneJournal';
$a->strings['InsaneJournal password'] = 'Contraseña de InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Publicar en InsaneJournal por defecto';
$a->strings['Save Settings'] = 'Guardar Ajustes';
