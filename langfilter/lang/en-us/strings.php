<?php

if(! function_exists("string_plural_select_en_US")) {
function string_plural_select_en_US($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Use the language filter'] = 'Use the language filter';
$a->strings['Able to read'] = 'Able to read';
$a->strings['Minimum confidence in language detection'] = 'Minimum confidence in language detection';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.';
$a->strings['Minimum length of message body'] = 'Minimum length of message body';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).';
$a->strings['Language Filter'] = 'Language Filter';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Filtered language: %s'] = 'Filtered language: %s';
