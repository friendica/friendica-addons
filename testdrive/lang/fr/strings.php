<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Administrator'] = 'Administrateur';
$a->strings['Your account on %s will expire in a few days.'] = 'Votre compte sur %s va expirer dans quelques jours.';
$a->strings['Your Friendica test account is about to expire.'] = 'Votre compte Friendica de test est sur le point d\'expirer.';
$a->strings['Hi %1$s,

Your test account on %2$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.'] = 'Bonjour %1$s,

Votre compte de test sur %2$s va expirer dans moins de cinq jours. Nous espérons que vous avez apprécié cet essai et que vous utiliserez cette opportunité pour trouver un site Friendica permanent pour vos communications sociales intégrées. Une liste des sites publics est disponible sur %s/siteinfo et pour plus d\'informations sur la mise en route de votre propre serveur Friendica, vous pouvez vous référer au site du projet Friendica sur https://friendi.ca.';
