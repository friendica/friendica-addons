<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to Wordpress"] = "Publicar no Wordpress";
$a->strings["WordPress Post Settings"] = "Configurações de publicação no WordPress";
$a->strings["Enable WordPress Post Addon"] = "Habilitar plug-in para publicar no WordPress";
$a->strings["WordPress username"] = "Nome de usuário no WordPress";
$a->strings["WordPress password"] = "Senha do WordPress";
$a->strings["Post to WordPress by default"] = "Publicar no WordPress por padrão";
$a->strings["Submit"] = "Enviar";
