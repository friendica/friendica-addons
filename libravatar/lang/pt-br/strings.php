<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3"] = "Não foi possível instalar o Libravatar.<br>Ele requer PHP >= 5.3";
$a->strings["monster face"] = "careta";
$a->strings["retro arcade style face"] = "rosto de personagem de fliperama";
$a->strings["Submit"] = "Enviar";
