<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to GNU Social'] = 'Publier sur GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Merci de contacter l\'administrateur du site.<br />L\'URL d\'API fournie est invalide.';
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'Impossible de se connecter à l\'API GNU Social avec le chemin indiqué.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Currently connected to: <a href="%s" target="_statusnet">%s</a>'] = 'Actuellement connecté à : <a href="%s" target="_statusnet">%s</a>';
$a->strings['Clear OAuth configuration'] = 'Effacer la configuration OAuth';
$a->strings['Cancel GNU Social Connection'] = 'Annuler la connexion à GNU Social';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'Clés OAuth de GNU Social disponibles globalement';
$a->strings['Provide your own OAuth Credentials'] = 'Fournissez vos propres identifiants OAuth';
$a->strings['Log in with GNU Social'] = 'Se connecter avec GNU Social';
$a->strings['Cancel Connection Process'] = 'Annuler le processus de connexion';
$a->strings['Current GNU Social API is: %s'] = 'L\'API GNU Social actuelle est : %s';
$a->strings['OAuth Consumer Key'] = 'Clé d\'Utilisateur OAuth';
$a->strings['OAuth Consumer Secret'] = 'Secret d\'Utilisateur OAuth';
$a->strings['Base API Path (remember the trailing /)'] = 'Chemin de base de l\'API (n\'oubliez pas le / final)';
$a->strings['Copy the security code from GNU Social here'] = 'Coller le code de sécurité de GNU Social ici';
$a->strings['Allow posting to GNU Social'] = 'Autoriser la publication sur GNU Social';
$a->strings['Post to GNU Social by default'] = 'Publier sur GNU Social par défaut';
$a->strings['Mirror all public posts'] = 'Refléter toutes les publications publiques';
$a->strings['Automatically create contacts'] = 'Créer les contacts automatiquement';
$a->strings['Import the remote timeline'] = 'Importer la Timeline distante';
$a->strings['Disabled'] = 'Désactiver';
$a->strings['Full Timeline'] = 'Timeline complète';
$a->strings['Only Mentions'] = 'Mentions uniquement';
$a->strings['GNU Social Import/Export/Mirror'] = 'Import/Export/Miroir GNU Social';
$a->strings['Site name'] = 'Nom du site';
$a->strings['Consumer Secret'] = 'Secret d\'Utilisateur';
$a->strings['Consumer Key'] = 'Clé d\'Utilisateur';
