<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permissão negada.';
$a->strings['Post to page:'] = 'Publicar na página:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re)autenticar sua página no Tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Você não se autenticou no Tumblr';
$a->strings['Post to Tumblr by default'] = 'Publicar no Tumblr por padrão';
$a->strings['Post to Tumblr'] = 'Publicar no Tumblr';
