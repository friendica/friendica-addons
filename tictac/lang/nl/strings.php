<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "3D Tic-Tac-Toe";
$a->strings["3D Tic-Tac-Toe"] = "3D Tic-Tac-Toe ";
$a->strings["New game"] = "";
$a->strings["New game with handicap"] = "";
$a->strings["Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. "] = "";
$a->strings["In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels."] = "";
$a->strings["The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage."] = "";
$a->strings["You go first..."] = "";
$a->strings["I'm going first this time..."] = "";
$a->strings["You won!"] = "";
$a->strings["\"Cat\" game!"] = "";
$a->strings["I won!"] = "";
