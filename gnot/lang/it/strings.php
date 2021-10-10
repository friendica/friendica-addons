<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Gnot Settings'] = 'Impostazioni Gnot';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Enable this addon?'] = 'Abilita questo componente aggiuntivo?';
$a->strings['Allows threading of email comment notifications on Gmail and anonymising the subject line.'] = 'Permetti di raggruppare le notifiche dei commenti in thread su Gmail e anonimizza l\'oggetto';
$a->strings['[Friendica:Notify] Comment to conversation #%d'] = '[Friendica:Notifica] Commento alla conversazione n° %d';
