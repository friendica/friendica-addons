<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to Dreamwidth"] = "Publicar no Dreamwidth";
$a->strings["Dreamwidth Post Settings"] = "Configurações de publicação no Dreamwidth";
$a->strings["Enable dreamwidth Post Addon"] = "Habilitar plug-in para publicar no Dreamwidth";
$a->strings["dreamwidth username"] = "Nome de usuário no Dreamwidth";
$a->strings["dreamwidth password"] = "Senha do Dreamwidth";
$a->strings["Post to dreamwidth by default"] = "Publicar no Dreamwidth por padrão";
$a->strings["Submit"] = "Enviar";
