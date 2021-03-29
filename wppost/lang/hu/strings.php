<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Wordpress"] = "Beküldés a WordPressre";
$a->strings["Wordpress Export"] = "WordPress exportálás";
$a->strings["Enable WordPress Post Addon"] = "A WordPress-beküldő bővítmény engedélyezése";
$a->strings["WordPress username"] = "WordPress felhasználónév";
$a->strings["WordPress password"] = "WordPress jelszó";
$a->strings["WordPress API URL"] = "WordPress API URL";
$a->strings["Post to WordPress by default"] = "Beküldés a WordPressre alapértelmezetten";
$a->strings["Provide a backlink to the Friendica post"] = "Visszafelé mutató hivatkozás biztosítása a Friendica bejegyzésre";
$a->strings["Text for the backlink, e.g. Read the original post and comment stream on Friendica."] = "A visszafelé mutató hivatkozás szövege, például „Olvassa el az eredeti bejegyzést és a hozzászólásokat a Friendicán”.";
$a->strings["Don't post messages that are too short"] = "Ne küldjön be olyan üzeneteket, amelyek túl rövidek";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Read the orig­i­nal post and com­ment stream on Friendica"] = "Olvassa el az eredeti bejegyzést és a hozzászólásokat a Friendicán";
$a->strings["Post from Friendica"] = "Bejegyzés a Friendicáról";
