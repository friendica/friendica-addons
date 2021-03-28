<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Remote Permissions Settings"] = "Távoli jogosultságok beállításai";
$a->strings["Allow recipients of your private posts to see the other recipients of the posts"] = "Engedélyezés a személyes bejegyzések címzettjei számára, hogy láthassák a bejegyzések egyéb címzettjeit";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Visible to:"] = "Látható nekik:";
$a->strings["Visible to"] = "Látható nekik";
$a->strings["may only be a partial list"] = "esetleg csak részleges lista lehet";
$a->strings["Global"] = "Globális";
$a->strings["The posts of every user on this server show the post recipients"] = "Ezen a kiszolgálón lévő összes felhasználó bejegyzései megjelenítik a bejegyzés címzettjeit";
$a->strings["Individual"] = "Egyéni";
$a->strings["Each user chooses whether his/her posts show the post recipients"] = "Minden felhasználó kiválaszthatja, hogy a bejegyzéseik megjelenítsék-e a bejegyzés címzettjeit";
