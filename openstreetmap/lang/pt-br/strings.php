<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Submit"] = "Enviar";
$a->strings["Tile Server URL"] = "URL do Servidor de Bloco";
$a->strings["A list of <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">public tile servers</a>"] = "Uma lista de <a href=\"http://wiki.openstreetmap.org/wiki/TMS\" target=\"_blank\">servidores de bloco públicos</a>";
$a->strings["Default zoom"] = "Zoom padrão";
$a->strings["The default zoom level. (1:world, 18:highest)"] = "O nível padrão de zoom. (1:mundo, 18:máximo) ";
$a->strings["Settings updated."] = "As configurações foram atualizadas.";
