<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Verbirgt Inhalte von Benutzern durch Zusammenklappen der Beiträge. Des Weiteren wird das Profilbild durch einen generischen Avatar ersetzt.';
$a->strings['Comma separated profile URLS:'] = 'Komma-separierte Liste von Profil-URLs';
$a->strings['Blockem'] = 'Blockem';
$a->strings['Filtered user: %s'] = 'Gefilterte Person: %s';
$a->strings['Unblock Author'] = 'Autor freischalten';
$a->strings['Block Author'] = 'Autor blockieren';
