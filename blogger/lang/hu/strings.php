<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to blogger"] = "Beküldés a Bloggerre";
$a->strings["Blogger Export"] = "Blogger exportálás";
$a->strings["Enable Blogger Post Addon"] = "A Blogger-beküldő bővítmény engedélyezése";
$a->strings["Blogger username"] = "Blogger felhasználónév";
$a->strings["Blogger password"] = "Blogger jelszó";
$a->strings["Blogger API URL"] = "Blogger API URL";
$a->strings["Post to Blogger by default"] = "Beküldés a Bloggerre alapértelmezetten";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Post from Friendica"] = "Bejegyzés a Friendicáról";
