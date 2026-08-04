<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.';
$a->strings['Enable Content filter'] = 'Enable content filter';
$a->strings['Comma separated list of keywords to hide'] = 'Comma separated list of keywords';
$a->strings['Content Filter (NSFW and more)'] = 'Content Filter (NSFW and more)';
$a->strings['Filtered tag: %s'] = 'Filtered tag: %s';
$a->strings['Filtered word: %s'] = 'Filtered word: %s';
