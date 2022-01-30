<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Show callstack'] = 'Callstack anzeigen';
$a->strings['Show detailed performance measures in the callstack. When deactivated, only the summary will be displayed.'] = 'Detailierte Performance Messungen im Callstack anzeigen. Wenn dies aktiviert ist werden anstelle der Zusammenfassung mehr Details angezeigt.';
$a->strings['Minimal time'] = 'Minimale Zeit';
$a->strings['Minimal time that an activity needs to be listed in the callstack.'] = 'Minimale Zeit die eine Aktivität dauern soll, ehe sie im Callstack aufgelistet wird.';
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'Datenbank: %s/%s, Netzwerk: %s, Darstellung: %s, Sitzung: %s, I/O: %s, Sonstiges: %s, Gesamt: %s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'Class-Init: %s, Boot: %s, Init: %s, Inhalt: %s, Sonstiges: %s, Gesamt: %s';
