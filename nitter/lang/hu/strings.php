<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Which nitter server shall be used for the replacements in the post bodies? Use the URL with servername and protocol.  See %s for a list of available public Nitter servers.'] = 'Melyik Nitter-kiszolgálót kell használni a bejegyzések törzseiben történő cserékhez? Az URL-t kiszolgálónévvel és protokollal használja. Az elérhető nyilvános Nitter-kiszolgálók listájáért nézze meg a %s oldalt.';
$a->strings['Nitter server'] = 'Nitter-kiszolgáló';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Links to Twitter in this posting were replaced by links to the Nitter instance at %s'] = 'Ebben a bejegyzésben a Twitterre mutató hivatkozások ki lettek cserélve a %s címen elérhető Nitter-példányra mutató hivatkozásokkal.';
