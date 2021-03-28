<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Gnot Settings"] = "Gnot beállítások";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Enable this addon?"] = "Engedélyezi ezt a bővítményt?";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Lehetővé teszi az e-mailes hozzászólás értesítéseinek szálkezelését a Gmailnél, és anonimizálja a tárgy sorát.";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica: értesítés] Hozzászólás a(z) %d. beszélgetéshez";
