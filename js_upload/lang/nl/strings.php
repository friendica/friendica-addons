<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Upload a file"] = "Upload een bestand";
$a->strings["Drop files here to upload"] = "Sleep uw bestanden hier om ze te uploaden";
$a->strings["Cancel"] = "";
$a->strings["Failed"] = "";
$a->strings["No files were uploaded."] = "";
$a->strings["Uploaded file is empty"] = "";
$a->strings["Image exceeds size limit of "] = "";
$a->strings["File has an invalid extension, it should be one of "] = "";
$a->strings["Upload was cancelled, or server error encountered"] = "";
