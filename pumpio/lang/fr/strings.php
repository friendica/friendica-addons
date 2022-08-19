<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permission refusée.';
$a->strings['Unable to register the client at the pump.io server \'%s\'.'] = 'Impossible d\'enregistrer le client sur le serveur pump.io "%s".';
$a->strings['You are now authenticated to pumpio.'] = 'Vous êtes maintenant authentifié sur pump.io.';
$a->strings['return to the connector page'] = 'Retourner à la page du connecteur';
$a->strings['Post to pumpio'] = 'Publier sur pump.io';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Delete this preset'] = 'Supprimer ce préréglage';
$a->strings['Authenticate your pump.io connection'] = 'Identifiez votre connexion à pump.io';
$a->strings['Pump.io servername (without "http://" or "https://" )'] = 'Domaine du serveur Pump.io (sans "http://" ou "https://")';
$a->strings['Pump.io username (without the servername)'] = 'Nom d\'utilisateur Pump.io (sans le domaine de serveur)';
$a->strings['Import the remote timeline'] = 'Importer la timeline distante';
$a->strings['Enable Pump.io Post Addon'] = 'Activer l\'extension Pump.io';
$a->strings['Post to Pump.io by default'] = 'Publier sur Pump.io par défaut';
$a->strings['Should posts be public?'] = 'Les messages devraient être publiques ?';
$a->strings['Mirror all public posts'] = 'Refléter toutes les publications publiques';
$a->strings['Pump.io Import/Export/Mirror'] = 'Import/Export/Miroir Pump.io';
$a->strings['status'] = 'statut';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s aime lea %3$s de %2$s';
