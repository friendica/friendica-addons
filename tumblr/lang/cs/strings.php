<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Permission denied.'] = 'Přístup odmítnut.';
$a->strings['You are now authenticated to tumblr.'] = 'Nyní jste přihlášen/a k Tumblr.';
$a->strings['return to the connector page'] = 'návrat ke stránce konektor';
$a->strings['Post to Tumblr'] = 'Posílat na Tumblr';
$a->strings['Post to page:'] = 'Posílat na stránku:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Znovu) přihlásit k Vaší stránce Tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Nyní nejste přihlášen/a k Tumblr.';
$a->strings['Enable Tumblr Post Addon'] = 'Povolit doplněk Tumblr Post';
$a->strings['Post to Tumblr by default'] = 'Ve výchozím stavu posílat příspěvky na Tumblr';
