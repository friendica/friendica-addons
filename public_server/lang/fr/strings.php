<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Administrator'] = 'Administrateur';
$a->strings['Your account on %s will expire in a few days.'] = 'Votre compte sur %s va expirer dans quelques jours.';
$a->strings['Your Friendica account is about to expire.'] = 'Votre compte Friendica est sur le point d\'expirer.';
$a->strings['Hi %1$s,

Your account on %2$s will expire in less than five days. You may keep your account by logging in at least once every 30 days'] = '%1$s,

Votre compte sur %2$s va expirer dans moins de 5 jours. Vous pouvez conserver votre compte en vous identifiant au moins une fois tous les 30 jours';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['Set any of these options to 0 to deactivate it.'] = 'Entrez 0 comme valeur pour n\'importe quelle de ces options pour la désactiver.';
