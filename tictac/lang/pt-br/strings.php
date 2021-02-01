<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "Jogo da Velha Tridimensional";
$a->strings["3D Tic-Tac-Toe"] = "Jogo da Velha 3D";
$a->strings["New game"] = "Novo jogo";
$a->strings["New game with handicap"] = "Novo jogo com limitador";
$a->strings["Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. "] = "O jogo da velha tridimensional é como o jogo tradicional, exceto por ser jogado em vários níveis simultaneamente.";
$a->strings["In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels."] = "Neste caso, há três níveis. Vence quem conseguir alinhar três quadrados em qualquer um dos níveis, assim como para cima, para baixo e na diagonal, em níveis diferentes.";
$a->strings["The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage."] = "No jogo com limitador, a posição central do nível do meio é desativada, porque o jogador que marcasse esse quadrado teria uma vantagem injusta.";
$a->strings["You go first..."] = "Você vai primeiro...";
$a->strings["I'm going first this time..."] = "Eu vou primeiro desta vez...";
$a->strings["You won!"] = "Você venceu!";
$a->strings["\"Cat\" game!"] = "Empatamos!";
$a->strings["I won!"] = "Venci!";
