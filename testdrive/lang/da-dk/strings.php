<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Administrator';
$a->strings['Your account on %s will expire in a few days.'] = 'Din konto på %s udløber om nogle få dage.';
$a->strings['Your Friendica test account is about to expire.'] = 'Din Friendica konto er tæt på at udløbe.';
$a->strings['Hi %1$s,

Your test account on %2$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.'] = 'Hej %1$s,

Din testkonto på %2$s udløber om mindre end fem dage. Vi håber at du nød denne testkørsel og bruger denne lejlighed til at finde en permanent Friendica hjemmeside til dit online sociale liv. En liste af offentlige sider er tilgængelig på %s/siteinfo - og for mere information om at opsætte din egen Friendica server, se venligst Friendica-projektets hjemmeside: https://friendi.ca.';
