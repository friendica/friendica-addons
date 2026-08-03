<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['generic profile image'] = 'Generic profile image';
$a->strings['random geometric pattern'] = 'Random geometric pattern';
$a->strings['monster face'] = 'Monster face';
$a->strings['computer generated face'] = 'Computer-generated face';
$a->strings['retro arcade style face'] = 'Retro arcade-style face';
$a->strings['Information'] = 'Information';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'The Libravatar addon is also installed, please disable either the Libravatar addon or the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.';
$a->strings['Default avatar image'] = 'Default avatar image';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Select default avatar image if none was found at Gravatar. See README for more details.';
$a->strings['Rating of images'] = 'Rating of images';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Select the appropriate avatar rating for your site. See README for more details.';
