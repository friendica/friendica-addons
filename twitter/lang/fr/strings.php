<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Twitter'] = 'Publier sur Twitter';
$a->strings['No status.'] = 'Aucun statut';
$a->strings['Allow posting to Twitter'] = 'Autoriser la publication sur Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'En cas d\'activation, toutes vos publications <strong>publiques</strong> seront transmises au compte Twitter associé. Vous pourrez choisir de le faire par défaut (ici), ou bien pour chaque publication séparément lors de sa rédaction.';
$a->strings['Send public postings to Twitter by default'] = 'Envoyer par défaut les publications publiques sur Twitter';
$a->strings['API Key'] = 'Clé API';
$a->strings['API Secret'] = 'Secret API';
$a->strings['Access Token'] = 'Token Accès';
$a->strings['Access Secret'] = 'Secret Accès';
$a->strings['Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'] = 'Chaque utilisateur doit enregistrer sa propre application pour pouvoir publier des messages sur Twitter. Allez sur https://developer.twitter.com/en/portal/projects-and-apps pour enregistrer un projet. Dans le projet, vous devez ensuite enregistrer une application. Vous trouverez les données nécessaires pour le connecteur sur la page "Keys and token" dans les paramètres de l\'application.';
$a->strings['Last Status Summary'] = 'Résumé du dernier statut';
$a->strings['Last Status Content'] = 'Contenu du dernier statut';
$a->strings['Twitter Export'] = 'Export Twitter';
