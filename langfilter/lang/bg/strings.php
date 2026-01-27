<?php

if(! function_exists("string_plural_select_bg")) {
function string_plural_select_bg($n){
	$n = intval($n);
	return intval($n != 1);
}}
