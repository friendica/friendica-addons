<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return intval($n % 10 != 1 || $n % 100 == 11);
}}
;
$a->strings["Post to blogger"] = "Senda færslu á bloggara";
$a->strings["Blogger Export"] = "Flytja út blogg";
$a->strings["Enable Blogger Post Addon"] = "Virkja sendiviðbót fyrir blogg";
$a->strings["Blogger username"] = "Notandanafn bloggara";
$a->strings["Blogger password"] = "Aðgangsorð bloggara";
$a->strings["Blogger API URL"] = "API slóð bloggs";
$a->strings["Post to Blogger by default"] = "Sjálfgefið láta færslur flæða inn á blogg";
$a->strings["Save Settings"] = "Vista stillingar";
$a->strings["Post from Friendica"] = "Færslur frá Friendica";
