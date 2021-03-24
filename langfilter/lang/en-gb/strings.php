<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Language Filter"] = "Language Filter";
$a->strings["This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them."] = "This addon tries to identify the language that posts are written in. If posts don't match any of the languages specifed below, those posts will be hidden by collapsing them.";
$a->strings["Use the language filter"] = "Use the Language Filter";
$a->strings["Able to read"] = "Language selection";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "List of abbreviations (ISO two-letter language codes) for languages you wish to view, separated by commas. For example, German and Italian would be \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "Minimum confidence in language detection";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "The minimum confidence in language detection being correct, from 0 to 100. Posts will only be filtered if their confidence value is higher than this percentage.";
$a->strings["Minimum length of message body"] = "Minimum length of message body";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "The minimum number of characters a message has to contain for it to activate the filter. Posts shorter than this will not be filtered. Please note that language detection is unreliable for short content (for example for posts of less than 200 characters).";
$a->strings["Save Settings"] = "Save Settings";
$a->strings["Language Filter Settings saved."] = "Language Filter settings saved.";
$a->strings["Filtered language: %s"] = "Filtered language: %s";
