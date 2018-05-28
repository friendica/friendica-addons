<?php

if(! function_exists("string_plural_select_fi")) {
function string_plural_select_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "";
$a->strings["Advanced Content Filter"] = "";
$a->strings["Back to Addon Settings"] = "";
$a->strings["Add a Rule"] = "Lisää sääntö";
$a->strings["Help"] = "";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "";
$a->strings["Your rules"] = "Sääntösi";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "";
$a->strings["Disabled"] = "Ei käytössä";
$a->strings["Enabled"] = "Käytössä";
$a->strings["Disable this rule"] = "";
$a->strings["Enable this rule"] = "Ota tämä sääntö käyttöön";
$a->strings["Edit this rule"] = "Muokkaa tätä sääntöä";
$a->strings["Edit the rule"] = "Muokkaa sääntöä";
$a->strings["Save this rule"] = "Tallenna tämä sääntö";
$a->strings["Delete this rule"] = "Poista tämä sääntö";
$a->strings["Rule"] = "Sääntö";
$a->strings["Close"] = "Sulje";
$a->strings["Add new rule"] = "Lisää uusi sääntö";
$a->strings["Rule Name"] = "";
$a->strings["Rule Expression"] = "";
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "";
$a->strings["Cancel"] = "";
$a->strings["You must be logged in to use this method"] = "";
$a->strings["Invalid form security token, please refresh the page."] = "";
$a->strings["The rule name and expression are required."] = "";
$a->strings["Rule successfully added"] = "Sääntö lisätty";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "";
$a->strings["Rule successfully updated"] = "Sääntö päivitetty";
$a->strings["Rule successfully deleted"] = "Sääntö poistettu";
$a->strings["Missing argument: guid."] = "";
$a->strings["Unknown post with guid: %s"] = "";
$a->strings["Method not found"] = "";
