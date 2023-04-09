<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permesso negato.';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Consumer Key'] = 'Consumer Key';
$a->strings['Consumer Secret'] = 'Consumer Secret';
$a->strings['You are now authenticated to tumblr.'] = 'Sei autenticato su Tumblr.';
$a->strings['return to the connector page'] = 'ritorna alla pagina del connettore';
$a->strings['Post to Tumblr'] = 'Invia a Tumblr';
$a->strings['Post to page:'] = 'Invia alla pagina:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Ri)Autenticati con la tua pagina Tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Non sei autenticato su Tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Abilita componente aggiuntivo di invio a Tumblr';
$a->strings['Post to Tumblr by default'] = 'Invia sempre a Tumblr';
$a->strings['Tumblr Export'] = 'Esporta Tumblr';
