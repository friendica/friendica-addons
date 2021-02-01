<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "Dreidimensionales Tic-Tac-Toe";
$a->strings["3D Tic-Tac-Toe"] = "3D Tic-Tac-Toe";
$a->strings["New game"] = "Neues Spiel";
$a->strings["New game with handicap"] = "Neues Handicap Spiel";
$a->strings["Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. "] = "3D-Tic-Tac-Toe ist genauso wie das herkömmliche Spiel, nur das man es auf mehreren Ebenen gleichzeitig spielt.";
$a->strings["In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels."] = "In diesem Fall sind es drei Ebenen. Man gewinnt indem man drei in einer Reihe auf einer beliebigen Reihe schafft, oder drei übereinander oder diagonal auf verschiedenen Ebenen.";
$a->strings["The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage."] = "Beim Handicap-Spiel wird die zentrale Position der mittleren Ebene gesperrt da der Spieler der diese Ebene besitzt oft einen unfairen Vorteil genießt.";
$a->strings["You go first..."] = "Du fängst an...";
$a->strings["I'm going first this time..."] = "Diesmal fange ich an...";
$a->strings["You won!"] = "Du gewinnst!";
$a->strings["\"Cat\" game!"] = "Unentschieden!";
$a->strings["I won!"] = "Ich gewinne!";
