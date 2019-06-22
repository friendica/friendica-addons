<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "Filtered by rule: %s";
$a->strings["Advanced Content Filter"] = "Advanced Content Filter";
$a->strings["Back to Addon Settings"] = "Back to addon settings";
$a->strings["Add a Rule"] = "Add a rule";
$a->strings["Help"] = "Help";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>.";
$a->strings["Your rules"] = "Your rules";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "You have no rules yet! Start adding one by clicking on the button above next to the title.";
$a->strings["Disabled"] = "Disabled";
$a->strings["Enabled"] = "Enabled";
$a->strings["Disable this rule"] = "Disable this rule";
$a->strings["Enable this rule"] = "Enable this rule";
$a->strings["Edit this rule"] = "Edit this rule";
$a->strings["Edit the rule"] = "Edit the rule";
$a->strings["Save this rule"] = "Save this rule";
$a->strings["Delete this rule"] = "Delete this rule";
$a->strings["Rule"] = "Rule";
$a->strings["Close"] = "Close";
$a->strings["Add new rule"] = "Add new rule";
$a->strings["Rule Name"] = "Rule name";
$a->strings["Rule Expression"] = "Rule expression";
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>";
$a->strings["Cancel"] = "Cancel";
$a->strings["You must be logged in to use this method"] = "You must be logged in to use this method";
$a->strings["Invalid form security token, please refresh the page."] = "Invalid form security token, please refresh the page.";
$a->strings["The rule name and expression are required."] = "The rule name and expression are required.";
$a->strings["Rule successfully added"] = "Rule successfully added";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "Rule doesn't exist or doesn't belong to you.";
$a->strings["Rule successfully updated"] = "Rule successfully updated";
$a->strings["Rule successfully deleted"] = "Rule successfully deleted";
$a->strings["Missing argument: guid."] = "Missing argument: Global Unique Identifier (GUID).";
$a->strings["Unknown post with guid: %s"] = "Unknown post with Global Unique Identifier (GUID): %s";
$a->strings["Method not found"] = "Method not found";
