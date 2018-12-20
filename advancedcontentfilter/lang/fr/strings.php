<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "Filtrer par règle:%s";
$a->strings["Advanced Content Filter"] = "Filtre avancé de contenu";
$a->strings["Back to Addon Settings"] = "Retour aux paramètres de l'extension";
$a->strings["Add a Rule"] = "Ajouter une règle";
$a->strings["Help"] = "Aide";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "";
$a->strings["Your rules"] = "Vos règles";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "";
$a->strings["Disabled"] = "";
$a->strings["Enabled"] = "";
$a->strings["Disable this rule"] = "";
$a->strings["Enable this rule"] = "";
$a->strings["Edit this rule"] = "";
$a->strings["Edit the rule"] = "";
$a->strings["Save this rule"] = "";
$a->strings["Delete this rule"] = "";
$a->strings["Rule"] = "";
$a->strings["Close"] = "";
$a->strings["Add new rule"] = "";
$a->strings["Rule Name"] = "";
$a->strings["Rule Expression"] = "";
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "";
$a->strings["Cancel"] = "";
$a->strings["You must be logged in to use this method"] = "";
$a->strings["Invalid form security token, please refresh the page."] = "";
$a->strings["The rule name and expression are required."] = "";
$a->strings["Rule successfully added"] = "";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "";
$a->strings["Rule successfully updated"] = "";
$a->strings["Rule successfully deleted"] = "";
$a->strings["Missing argument: guid."] = "";
$a->strings["Unknown post with guid: %s"] = "";
$a->strings["Method not found"] = "";
