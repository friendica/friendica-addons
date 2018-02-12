<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Event Export"] = "Exportare Eveniment";
$a->strings["You can download public events from: "] = "Puteți descărca evenimente publice de la:";
$a->strings["The user does not export the calendar."] = "Utilizatorul nu își exportă calendarul.";
$a->strings["This calendar format is not supported"] = "Acest format de calendar nu este acceptat";
$a->strings["Export Events"] = "Exportați Evenimente";
$a->strings["If this is enabled, your public events will be available at"] = "Dacă este activat, evenimente dvs publice vor fi disponibile pe";
$a->strings["Currently supported formats are ical and csv."] = "Formate acceptate în prezent sunt ical şi csv.";
$a->strings["Enable calendar export"] = "Activați exportarea calendarului";
$a->strings["Save Settings"] = "Salvare Configurări";
