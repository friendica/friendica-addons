<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Toegang geweigerd.';
$a->strings['You are now authenticated to tumblr.'] = 'Je bent nu aangemeld bij tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Tumblr Post Addon inschakelen';
$a->strings['Post to Tumblr by default'] = 'Plaatsen op Tumblr als standaard instellen ';
$a->strings['Post to page:'] = 'Plaats op pagina:';
