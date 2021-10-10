<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to GNU Social'] = 'Publier sur GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Merci de contacter l\'administrateur du site.<br />L\'URL d\'API fournie est invalide.';
$a->strings['GNU Social settings updated.'] = 'Paramètres du GNU Social mis à jour.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Log in with GNU Social'] = 'Se connecter avec GNU Social';
$a->strings['Copy the security code from GNU Social here'] = 'Coller le code de sécurité de GNU Social ici';
$a->strings['Current GNU Social API is'] = 'L\'API actuelle de GNU Social est';
$a->strings['Currently connected to: '] = 'Actuellement connecté à :';
$a->strings['Allow posting to GNU Social'] = 'Autoriser la publication sur GNU Social';
$a->strings['Disabled'] = 'Désactiver';
$a->strings['Full Timeline'] = 'Timeline complète';
$a->strings['Site name'] = 'Nom du site';
