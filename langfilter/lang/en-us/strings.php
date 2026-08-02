<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Language Filter'] = 'Language Filter';
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.';
$a->strings['Use the language filter'] = 'Use the language filter';
$a->strings['Able to read'] = 'Able to read';
$a->strings['List of abbreviations (iso2 codes) for languages you speak, comma separated. For example "de,it".'] = 'List of abbreviations (iso2 codes) for languages you speak, comma separated. For example "de,it".';
$a->strings['Minimum confidence in language detection'] = 'Minimum confidence in language detection';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.';
$a->strings['Minimum length of message body'] = 'Minimum length of message body';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Language Filter Settings saved.'] = 'Language Filter Settings saved.';
$a->strings['Filtered language: %s'] = 'Filtered language: %s';
