<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Allow to use your friendica id (%s) to connecto to external unhosted-enabled storage (like ownCloud). See <a href=\"http://www.w3.org/community/unhosted/wiki/RemoteStorage#WebFinger\">RemoteStorage WebFinger</a>"] = "Permiteți utilizarea id-ului dvs. friendica (%s) să se conecteze cu medii de stocare externe de tip unhosted (precum ownCloud). Consultați <a href=\"http://www.w3.org/community/unhosted/wiki/RemoteStorage#WebFinger\">RemoteStorage WebFinger</a>";
$a->strings["Template URL (with {category})"] = "URL șablon (cu {categorie})";
$a->strings["OAuth end-point"] = "Punct-final OAuth ";
$a->strings["Api"] = "Api";
$a->strings["Save Settings"] = "Salvare Configurări";
