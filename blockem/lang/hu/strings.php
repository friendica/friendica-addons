<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Blockem'] = 'Blockem';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Elrejti a felhasználók tartalmát a bejegyzések összecsukásával. Ezenkívül lecseréli a profilképeiket egy általános képre.';
$a->strings['Comma separated profile URLS:'] = 'Profil URL-ek vesszővel elválasztva:';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['BLOCKEM Settings saved.'] = 'A Blockem beállításai elmentve.';
$a->strings['Filtered user: %s'] = 'Kiszűrt felhasználó: %s';
$a->strings['Unblock Author'] = 'Szerző tiltásának feloldása';
$a->strings['Block Author'] = 'Szerző tiltása';
$a->strings['blockem settings updated'] = 'A Blockem beállításai frissítve.';
