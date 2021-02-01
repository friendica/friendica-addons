<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Jabber username"] = "Nome de usuário no Jabber";
$a->strings["Jabber password"] = "Senha do Jabber";
$a->strings["Encrypt Jabber password with Friendica password (recommended)"] = "Criptografar senha de Jabber com senha do Friendica (recomendado)";
$a->strings["Friendica password"] = "Senha do Friendica";
$a->strings["Submit"] = "Enviar";
