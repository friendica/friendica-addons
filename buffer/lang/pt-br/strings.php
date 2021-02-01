<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Permission denied."] = "Permissão negada.";
$a->strings["Save Settings"] = "Salvar configurações";
$a->strings["Error when registering buffer connection:"] = "Erro ao registrar conexão de buffer:";
$a->strings["You are now authenticated to buffer. "] = "Você está autenticado no buffer.";
$a->strings["return to the connector page"] = "Volte a página de conectores.";
$a->strings["Post to Buffer"] = "Publicar no Buffer";
$a->strings["Buffer Export"] = "Exportar Buffer";
$a->strings["Authenticate your Buffer connection"] = "Autenticar sua conexão de Buffer";
$a->strings["Enable Buffer Post Addon"] = "Habilita addon para publicar no Buffer";
$a->strings["Post to Buffer by default"] = "Publica no Buffer por padrão";
$a->strings["Check to delete this preset"] = "Marque para excluir este perfil";
