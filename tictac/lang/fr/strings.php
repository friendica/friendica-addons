<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Three Dimensional Tic-Tac-Toe'] = 'Morpion tri-dimensionnel';
$a->strings['3D Tic-Tac-Toe'] = 'Morpion 3D';
$a->strings['New game'] = 'Nouvelle partie';
$a->strings['New game with handicap'] = 'Nouvelle partie avec handicap';
$a->strings['Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. '] = 'Le morpion tri-dimensionnel est comme le jeu traditionnel, si ce n\'est qu\'il se joue sur plusieurs niveaux simultanément.';
$a->strings['In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels.'] = 'Dans ce cas, il y a trois niveaux. Vous pouvez gagner en alignant trois signes à la suite sur n\'importe quel niveau, aussi bien vers le haut, vers le bas, qu\'en diagonale à travers les différents niveaux.';
$a->strings['The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage.'] = 'Le jeu avec handicap désactive la position centrale du niveau du milieu car le joueur occupant cette case a souvent un avantage disproportionné.';
$a->strings['You go first...'] = 'Vous commencez...';
$a->strings['I\'m going first this time...'] = 'Je commence cette fois-ci...';
$a->strings['You won!'] = 'Vous avez gagné !';
$a->strings['"Cat" game!'] = 'Match nul !';
$a->strings['I won!'] = 'J’ai gagné !';
