<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "Háromdimenziós tic-tac-toe";
$a->strings["3D Tic-Tac-Toe"] = "3D tic-tac-toe";
$a->strings["New game"] = "Új játék";
$a->strings["New game with handicap"] = "Új játék hátránnyal";
$a->strings["Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. "] = "A háromdimenziós tic-tac-toe olyan mint a hagyományos játék, kivéve hogy ezt egyidejűleg több szinten játsszák.";
$a->strings["In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels."] = "Ebben az esetben három szint van. Akkor nyeri meg a játékot, ha letesz hármat egy sorba bármely szinten, valamint fel, le és átlósan a különböző szintek között.";
$a->strings["The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage."] = "A hátránnyal indított játék letiltja a középső pozíciót a középső szinten, mert azt a négyzetet megszerző játékos gyakran tisztességtelen előnyt szerez.";
$a->strings["You go first..."] = "Ön lép először…";
$a->strings["I'm going first this time..."] = "Most én lépek először…";
$a->strings["You won!"] = "Ön nyert!";
$a->strings["\"Cat\" game!"] = "„Macska” játék!";
$a->strings["I won!"] = "Én nyertem!";
