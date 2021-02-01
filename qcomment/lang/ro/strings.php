<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings[":-)"] = ":-)";
$a->strings[":-("] = ":-(";
$a->strings["lol"] = "lol";
$a->strings["Quick Comment Settings"] = "Configurări Comentariu Rapid";
$a->strings["Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies."] = "Comentariile rapide se regăsesc lângă casetele comentariilor, uneori ascunse. Apăsați pe ele pentru a posta răspunsuri simple.";
$a->strings["Enter quick comments, one per line"] = "Introduceți cometarii rapide, câte unul pe linie:";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Quick Comment settings saved."] = "Configurările pentru Comentariu Rapid au fost salvate.";
