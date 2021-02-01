<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Filtered by rule: %s"] = "Filtré par règle:%s";
$a->strings["Advanced Content Filter"] = "Filtre avancé de contenu";
$a->strings["Back to Addon Settings"] = "Retour aux paramètres de l'extension";
$a->strings["Add a Rule"] = "Ajouter une règle";
$a->strings["Help"] = "Aide";
$a->strings["Your rules"] = "Vos règles";
$a->strings["Disabled"] = "Désactivé";
$a->strings["Enabled"] = "Activé";
$a->strings["Disable this rule"] = "Désactiver cette règle";
$a->strings["Enable this rule"] = "Activer cette règle";
$a->strings["Edit this rule"] = "Modifier cette règle";
$a->strings["Edit the rule"] = "Modifier la règle";
$a->strings["Save this rule"] = "Enregistrer cette règle";
$a->strings["Delete this rule"] = "Supprimer cette règle";
$a->strings["Rule"] = "Règle";
$a->strings["Close"] = "Fermer";
$a->strings["Add new rule"] = "Ajouter nouvelle règle";
$a->strings["Rule Name"] = "Nommer règle";
$a->strings["Rule Expression"] = "Expression de règle";
$a->strings["Cancel"] = "Annuler";
$a->strings["You must be logged in to use this method"] = "Vous devez être connecté pour utiliser cette méthode";
$a->strings["Invalid form security token, please refresh the page."] = "Formulaire token de sécurité invalide, rafraîchissez la page";
$a->strings["The rule name and expression are required."] = "Le nom et l'expression de cette règle sont requis";
$a->strings["Rule successfully added"] = "Règle bien ajoutée";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "Cette règle n'existe pas ou ne vous appartient pas";
$a->strings["Rule successfully updated"] = "Règle bien mise à jour";
$a->strings["Rule successfully deleted"] = "Règle bien supprimée";
$a->strings["Missing argument: guid."] = "Argument manquant: ";
$a->strings["Unknown post with guid: %s"] = "Post inconnu avec guid : %s";
$a->strings["Method not found"] = "Méthode non trouvée";
