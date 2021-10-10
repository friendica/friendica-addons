<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Which nitter server shall be used for the replacements in the post bodies? Use the URL with servername and protocol.  See %s for a list of available public Nitter servers.'] = 'Welcher Nitter server soll für die Ersetzungen verwendet werden? Gib die URL mit Servername und Protokoll an. Eine Liste von öffentlichen Nitter servern findest du unter %s.';
$a->strings['Nitter server'] = 'Nitter Server';
$a->strings['Save Settings'] = 'Einstellungen Speichern';
$a->strings['In an attempt to protect your privacy, links to Twitter in this posting were replaced by links to the Nitter instance at %s'] = 'Um deine Privatsphäre zu schützen, wurden in diesem Beitrag Links nach twitter.com durch die Nitter Instanz auf %s ersetzt.';
