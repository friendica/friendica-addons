<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Gnot settings updated."] = "Configurările Gnot au fost actualizate.";
$a->strings["Gnot Settings"] = "Configurări Gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Permite înlănțuirea notificărilor prin email a comentariilor, în Gmail și anonimizarea  subiectului.";
$a->strings["Enable this plugin/addon?"] = "Activați acest modul/supliment?";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica:Notificare] Comentariu la conversația # %d";
