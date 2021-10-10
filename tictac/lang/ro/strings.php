<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Three Dimensional Tic-Tac-Toe'] = 'Tic-Tac-Toe Tridimensional';
$a->strings['3D Tic-Tac-Toe'] = '3D Tic-Tac-Toe';
$a->strings['New game'] = 'Joc nou';
$a->strings['New game with handicap'] = 'Joc nou cu handicap';
$a->strings['Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. '] = 'Tic-tac-toe Tridimensional este exact ca și jocul tradițional cu excepția faptului că acesta este jucat simultan pe multiple niveluri.';
$a->strings['In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels.'] = 'În acest caz există trei niveluri. Câștigați prin obținerea a trei marcaje per line, la orice nivel, precum şi în sus, în jos, sau pe diagonală pe diferite niveluri.';
$a->strings['The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage.'] = 'Jocul cu handicap, dezactivează poziția centrală din nivelul mediu, deoarece jucătorul consideră că acest pătrat are adeseori un avantaj incorect.';
$a->strings['You go first...'] = 'Bifează tu primul...';
$a->strings['I\'m going first this time...'] = 'Bifez eu primul, de data aceasta ...';
$a->strings['You won!'] = 'Ai câştigat!';
$a->strings['"Cat" game!'] = 'Joc "Pisică" !';
$a->strings['I won!'] = 'Am câştigat!';
