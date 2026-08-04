<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Administrator';
$a->strings['Your account on %s will expire in a few days.'] = 'Your %s account will expire within a few days.';
$a->strings['Your Friendica account is about to expire.'] = 'Your Friendica account is about to expire.';
$a->strings['Hi %1$s,

Your account on %2$s will expire in less than five days. You may keep your account by logging in at least once every 30 days'] = 'Hi %1$s,

Your account on %2$s is due to expire in less than five days. You can prevent expiry by logging in once every 30 days';
