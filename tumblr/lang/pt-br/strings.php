<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Permission denied.'] = 'Permissão negada.';
$a->strings['You are now authenticated to tumblr.'] = 'Você se autenticou no Tumblr.';
$a->strings['return to the connector page'] = 'voltar à página de conectores';
$a->strings['Post to Tumblr'] = 'Publicar no Tumblr';
$a->strings['Tumblr Post Settings'] = 'Configurações de publicação no Tumblr';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re)autenticar sua página no Tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Habilitar plug-in para publicar no Tumblr';
$a->strings['Post to Tumblr by default'] = 'Publicar no Tumblr por padrão';
$a->strings['Post to page:'] = 'Publicar na página:';
$a->strings['You are not authenticated to tumblr'] = 'Você não se autenticou no Tumblr';
$a->strings['Submit'] = 'Enviar';
