<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['%s Administrator'] = '%s Administrator';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, %2$s Administrator';
$a->strings['Send email to all members'] = 'Send email til alle medlemmer';
$a->strings['No recipients found.'] = 'Ingen modtagere fundet.';
$a->strings['Emails sent'] = 'Emails sendt';
$a->strings['Send email to all members of this Friendica instance.'] = 'Send emails til alle medlemmer af denne Friendica instans';
$a->strings['Message subject'] = 'Beskedemne';
$a->strings['Test mode (only send to administrator)'] = 'Testtilstand (send kun til administrator)';
$a->strings['Submit'] = 'Indsend';
