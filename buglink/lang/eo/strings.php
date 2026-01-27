<?php

if(! function_exists("string_plural_select_eo")) {
function string_plural_select_eo($n){
	$n = intval($n);
	return intval($n != 1);
}}
