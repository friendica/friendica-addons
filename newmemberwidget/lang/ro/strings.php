<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["New Member"] = "Membru Nou";
$a->strings["Tips for New Members"] = "Sfaturi pentru Membrii Noi";
$a->strings["Global Support Forum"] = "Forum Global Asistentă";
$a->strings["Local Support Forum"] = "Forum Local Asistență";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Message"] = "Mesaj";
$a->strings["Your message for new members. You can use bbcode here."] = "Mesajul dumneavoastră pentru noii membri. Puteți folosi aici codul BB.";
$a->strings["Add a link to global support forum"] = "Adăugați o legătură către forumul global de asistentă";
$a->strings["Should a link to the global support forum be displayed?"] = "Ar trebui afișată o legătură către forumul global de asistență?";
$a->strings["Add a link to the local support forum"] = "Adăugați o legătură către forumul local de asistență";
$a->strings["If you have a local support forum and wand to have a link displayed in the widget, check this box."] = "Dacă dețineți un forum local de asistentă şi doriți să aveți o legătură afișată în mini-aplicația widget, bifați această casetă.";
$a->strings["Name of the local support group"] = "Numele grupului local de asistență";
$a->strings["If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)"] = "Dacă ați bifat mai sus, specificați aici <em>pseudonimul</em> grupului local de asistență (adică operatorii de asistență)";
