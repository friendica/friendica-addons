<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This website is tracked using the <a href=\'http://www.matomo.org\'>Matomo</a> analytics tool.'] = 'Denne hjemmeside er sporet med analyticsværktøjet <a href=\'http://www.matomo.org\'>Matomo</a>.';
$a->strings['If you do not want that your visits are logged in this way you <a href=\'%s\'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out).'] = 'Hvis du ikke vil have at dine besøg bliver logget på denne måde, kan du <a href=\'%s\'>sætte en cookie for at forhindre Matomo / Piwik i at spore yderligere besøg på siden</a> (opt-out).';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Matomo (Piwik) Base URL'] = 'Matomo (Piwik) Base URL';
$a->strings['Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)'] = 'Absolut sti til din Matomo (Piwik) installation. (uden protokol (http/s), med efterfølgende skråstreg)';
$a->strings['Site ID'] = 'Side ID';
$a->strings['Show opt-out cookie link?'] = 'Vis opt-out cookie link?';
$a->strings['Asynchronous tracking'] = 'Asynkron sporing';
