<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Three Dimensional Tic-Tac-Toe'] = 'Kolmiulotteinen ristinolla';
$a->strings['3D Tic-Tac-Toe'] = '3D ristinolla';
$a->strings['New game'] = 'Uusi peli';
$a->strings['New game with handicap'] = 'Uusi peli haitalla';
$a->strings['You go first...'] = 'Aloita sinä...';
$a->strings['I\'m going first this time...'] = 'Minä aloitan...';
$a->strings['You won!'] = 'Sinä voitit!';
$a->strings['I won!'] = 'Minä voitin!';
