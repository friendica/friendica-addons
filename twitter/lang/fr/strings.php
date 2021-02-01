<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Post to Twitter"] = "Publier sur Twitter";
$a->strings["Twitter settings updated."] = "Paramètres Twitter mis à jour.";
$a->strings["Twitter Posting Settings"] = "Paramètres Twitter de publication";
$a->strings["Log in with Twitter"] = "Se connecter avec Twitter";
$a->strings["Copy the PIN from Twitter here"] = "Copier le PIN de Twitter ici";
$a->strings["Submit"] = "Soumettre";
$a->strings["Currently connected to: "] = "Actuellement connecté à :";
$a->strings["Allow posting to Twitter"] = "Autoriser la publication sur Twitter";
$a->strings["Send public postings to Twitter by default"] = "Envoyer par défaut les messages publics sur Twitter";
$a->strings["Settings updated."] = "Paramètres mis à jour.";
$a->strings["Name of the Twitter Application"] = "Nom de l'application Twitter";
