<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Three Dimensional Tic-Tac-Toe"] = "3D Tic-Tac-Toe";
$a->strings["3D Tic-Tac-Toe"] = "3D Tic-Tac-Toe ";
