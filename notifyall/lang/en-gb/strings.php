<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['%s Administrator'] = '%s Administrator';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, %2$s Administrator';
$a->strings['Send email to all members'] = 'Send email to all members';
$a->strings['No recipients found.'] = 'No recipients found.';
$a->strings['Emails sent'] = 'Emails sent';
$a->strings['Send email to all members of this Friendica instance.'] = 'Send email to all members of this Friendica instance.';
$a->strings['Message subject'] = 'Message subject';
$a->strings['Test mode (only send to administrator)'] = 'Test mode (only send to administrator)';
$a->strings['Submit'] = 'Submit';
