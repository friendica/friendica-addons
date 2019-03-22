<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "";
$a->strings["Advanced Content Filter"] = "Avancerat innehållsfiter";
$a->strings["Back to Addon Settings"] = "TIllbaka till Tilläggsinställningar";
$a->strings["Add a Rule"] = "Lägg till en regel";
$a->strings["Help"] = "Hjälp";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "Lägg till och hantera dina personliga regler för innehållsfilter i det här fönstret. Regler har ett namn och ett valfritt uttryck och kommer jämföras mot inläggets innehåll. Förteckning av alla operander och variabler finns att hitta på <a href=\"advancedcontentfilter/help\">hjälpsidan</a>.";
$a->strings["Your rules"] = "Dina regler";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "Du har inga regler än! Lägg till regler genom att klicka på knappen ovanför, bredvid överskriften.";
$a->strings["Disabled"] = "Inaktiverad";
$a->strings["Enabled"] = "Aktiverad";
$a->strings["Disable this rule"] = "Inaktivera den här regeln";
$a->strings["Enable this rule"] = "Aktivera den här regeln";
$a->strings["Edit this rule"] = "Redigera den här regeln";
$a->strings["Edit the rule"] = "Redigera den här regeln";
$a->strings["Save this rule"] = "Spara den här regeln";
$a->strings["Delete this rule"] = "Ta bort den här regeln";
$a->strings["Rule"] = "Regel";
$a->strings["Close"] = "Stäng";
$a->strings["Add new rule"] = "Lägg till ny regel";
$a->strings["Rule Name"] = "Regelnamn";
$a->strings["Rule Expression"] = "Regeluttryck";
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "<p>Exempel:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>taggar</li></ul>";
$a->strings["Cancel"] = "Avbryt";
$a->strings["You must be logged in to use this method"] = "Du måste vara inloggad för att använda den här funktionen";
$a->strings["Invalid form security token, please refresh the page."] = "";
$a->strings["The rule name and expression are required."] = "";
$a->strings["Rule successfully added"] = "";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "";
$a->strings["Rule successfully updated"] = "";
$a->strings["Rule successfully deleted"] = "";
$a->strings["Missing argument: guid."] = "";
$a->strings["Unknown post with guid: %s"] = "";
$a->strings["Method not found"] = "";
