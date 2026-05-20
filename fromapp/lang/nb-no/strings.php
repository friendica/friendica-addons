<?php

if(! function_exists("string_plural_select_nb_NO")) {
function string_plural_select_nb_NO($n){
	$n = intval($n);
	return intval($n != 1);
}}
