<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New game'] = 'Nytt spel';
$a->strings['New game with handicap'] = 'Nytt spel med handikapp';
$a->strings['You go first...'] = 'Börja först du...';
$a->strings['I\'m going first this time...'] = 'Jag börjar först den här gången...';
$a->strings['You won!'] = 'Du vann!';
$a->strings['"Cat" game!'] = '"Katt"-spel!';
$a->strings['I won!'] = 'Jag vann!';
