<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permission refusée.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Consumer Key'] = 'Clé utilisateur';
$a->strings['Consumer Secret'] = 'Secret utilisateur';
$a->strings['Maximum tags'] = 'Étiquettes maximum';
$a->strings['Maximum number of tags that a user can follow. Enter 0 to deactivate the feature.'] = 'Nombre maximum d\'étiquettes qu\'un utilisateur peut suivre. Entrez 0 pour désactiver cette fonctionnalité.';
$a->strings['Post to page:'] = 'Publier sur la page :';
$a->strings['(Re-)Authenticate your tumblr page'] = '(re)Authentifiez votre page Tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Vous n\'êtes pas identifié sur Tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Activez l\'extension de publication Tumblr';
$a->strings['Post to Tumblr by default'] = 'Publier sur Tumblr par défaut';
$a->strings['Import the remote timeline'] = 'Importer le flux distant';
$a->strings['Subscribed tags'] = 'Étiquettes suivies';
$a->strings['Comma separated list of up to %d tags that will be imported additionally to the timeline'] = 'Liste contenant jusqu\'à %d étiquettes, séparées par des virgules, qui seront importées dans le flux';
$a->strings['Tumblr Import/Export'] = 'Import/Export Tumblr';
$a->strings['Post to Tumblr'] = 'Publier vers Tumblr';
