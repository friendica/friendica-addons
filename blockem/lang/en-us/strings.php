<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Blockem"'] = 'Blockem';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.';
$a->strings['Comma separated profile URLS:'] = 'Comma-separated profile URLs:';
$a->strings['Save Settings'] = 'Save settings';
$a->strings['BLOCKEM Settings saved.'] = 'Blockem settings saved.';
$a->strings['Filtered user: %s'] = 'Filtered user: %s';
$a->strings['Unblock Author'] = 'Unblock author';
$a->strings['Block Author'] = 'Block author';
$a->strings['blockem settings updated'] = 'Blockem settings updated';
