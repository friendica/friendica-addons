<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "Trójwymiarowy Kółko i krzyżyk";
$a->strings["3D Tic-Tac-Toe"] = "3D Kółko i krzyżyk";
$a->strings["New game"] = "Nowa gra";
$a->strings["New game with handicap"] = "Nowa gra z handicapem";
$a->strings["Three dimensional tic-tac-toe is just like the traditional game except that it is played on multiple levels simultaneously. "] = "Trójwymiarowe Kółko i Krzyżyk jest tak jak w tradycyjnej grze, z tym wyjątkiem, że gra się na wielu poziomach jednocześnie.";
$a->strings["In this case there are three levels. You win by getting three in a row on any level, as well as up, down, and diagonally across the different levels."] = "W tym przypadku istnieją trzy poziomy. Wygrywasz, zdobywając trzy z rzędu na dowolnym poziomie, a także w górę, w dół i po przekątnej na różnych poziomach.";
$a->strings["The handicap game disables the center position on the middle level because the player claiming this square often has an unfair advantage."] = "Gra z handicapem wyłącza środkową pozycję na środkowym poziomie, ponieważ gracz zdobywający ten kwadrat często ma nieuczciwą przewagę.";
$a->strings["You go first..."] = "Idź pierwszy...";
$a->strings["I'm going first this time..."] = "Tym razem idę pierwszy...";
$a->strings["You won!"] = "Wygrałeś!";
$a->strings["\"Cat\" game!"] = "Gra \"Kot\"!";
$a->strings["I won!"] = "Wygrałem!";
