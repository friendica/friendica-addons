<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['This website is tracked using the <a href=\'http://www.matomo.org\'>Matomo</a> analytics tool.'] = 'Ce site Internet utilise <a href=\'http://www.matomo.org\'>Matomo</a> pour mesurer son audience.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Matomo (Piwik) Base URL'] = 'URL de base de Matomo (Piwik)';
$a->strings['Site ID'] = 'ID du site';
$a->strings['Show opt-out cookie link?'] = 'Montrer le lien d\'opt-out pour les cookies ?';
$a->strings['Asynchronous tracking'] = 'Suivi asynchrone';
