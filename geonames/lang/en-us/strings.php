<?php

if(! function_exists("string_plural_select_en_US")) {
function string_plural_select_en_US($n){
	$n = intval($n);
	return intval($n != 1);
}}
