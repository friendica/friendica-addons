<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Use Cat as Avatar"] = "";
$a->strings["More Random Cat!"] = "";
$a->strings["Reset to email Cat"] = "";
$a->strings["Cat Avatar Settings"] = "";
$a->strings["The cat hadn't found itself."] = "";
$a->strings["There was an error, the cat ran away."] = "";
$a->strings["Profile Photos"] = "Forsíðumyndir";
$a->strings["Meow!"] = "Mjá!";
