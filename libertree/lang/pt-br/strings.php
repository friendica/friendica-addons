<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to libertree"] = "Publicar no Libertree";
$a->strings["libertree Post Settings"] = "Configurações de publicação do Libertree";
$a->strings["Enable Libertree Post Addon"] = "Habilitar plug-in para publicar no Libertree";
$a->strings["Post to Libertree by default"] = "Publicar no Libertree por padrão";
$a->strings["Submit"] = "Enviar";
