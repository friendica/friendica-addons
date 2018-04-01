<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Permission denied."] = "Odmowa dostępu.";
$a->strings["You are now authenticated to tumblr."] = "Jesteś teraz uwierzytelniony na tumblr.";
$a->strings["return to the connector page"] = "powrót do strony łącza";
$a->strings["Post to Tumblr"] = "Opublikuj w Tumblr";
$a->strings["Tumblr Post Settings"] = "Ustawienia Postów Tumblr";
$a->strings["(Re-)Authenticate your tumblr page"] = "(Re-) Uwierzytelnij swoją stronę tumblr";
$a->strings["Enable Tumblr Post Addon"] = "Włącz dodatek Tumblr";
$a->strings["Post to Tumblr by default"] = "Wyślij domyślnie do Tumblr";
$a->strings["Post to page:"] = "Opublikuj na stronie:";
$a->strings["You are not authenticated to tumblr"] = "Nie jesteś uwierzytelniony w tumblr";
$a->strings["Submit"] = "Wyślij";
