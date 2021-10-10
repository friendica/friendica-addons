<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Site Owner';
$a->strings['Email Address'] = 'Email Address';
$a->strings['Postal Address'] = 'Postal Address';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'The Impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables, please refer to the addon\'s README file.';
$a->strings['Settings updated.'] = 'Settings updated.';
$a->strings['Submit'] = 'Submit';
$a->strings['The page operators name.'] = 'The site operator\'s name';
$a->strings['Site Owners Profile'] = 'Site owner\'s profile';
$a->strings['Profile address of the operator.'] = 'Profile address of the operator.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'How to contact the site operator via snail mail. You can use BBCode here.';
$a->strings['Notes'] = 'Notes';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Additional notes that are displayed beneath the contact information. You can use BBCode here.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'How to contact the operator via email. (The e-mail address will be obfuscated to avoid spam.)';
$a->strings['Footer note'] = 'Footer notes';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Text for the footer. You can use BBCode here.';
