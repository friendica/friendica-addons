<?php

if(! function_exists("string_plural_select_en_US")) {
function string_plural_select_en_US($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Allows threading of email comment notifications on Gmail and anonymising the subject line.'] = 'Allows threading of email comment notifications on Gmail and anonymising the subject line.';
$a->strings['Enable this addon?'] = 'Enable this addon?';
$a->strings['Gnot Settings'] = 'Gnot';
$a->strings['[Friendica:Notify] Comment to conversation #%d'] = '[Friendica:Notify] Comment to conversation #%d';
