<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Diaspora'] = 'Læg op på Diaspora';
$a->strings['Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. '] = 'Husk på: Du kan altid kontaktes fra Diaspora med dit Friendica handle <strong>%s</strong>. ';
$a->strings['All aspects'] = 'Alle aspekter';
$a->strings['Public'] = 'Offentlig';
$a->strings['Connected with your Diaspora account <strong>%s</strong>'] = 'Forbundet til din Diaspora-konto <strong>%s</strong>';
$a->strings['Information'] = 'Information';
$a->strings['Error'] = 'Fejl';
$a->strings['Enable Diaspora Post Addon'] = 'Aktiver Diaspora-tilføjelsen';
$a->strings['Diaspora handle'] = 'Diaspora handle';
$a->strings['Diaspora password'] = 'Diaspora adgangskode';
$a->strings['Post to Diaspora by default'] = 'Læg op på Diaspora som standard';
