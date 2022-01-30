<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Spara inställningar';
$a->strings['Redirect URL'] = 'Omdirigera URL';
$a->strings['Begin of the Blackout'] = 'Start på nedsläckningen';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Formatet är <tt>ÅÅÅÅ-MM-DD tt:mm</tt>; <em>ÅÅÅÅ</em> år, <em>MM</em> månad, <em>DD</em> dag, <em>tt</em> timme och <em>mm</em> minut.';
$a->strings['End of the Blackout'] = 'Slut på nedsläckningen';
