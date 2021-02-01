<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["New Member"] = "Nowy Użytkownik";
$a->strings["Tips for New Members"] = "Wskazówki dla nowych członków";
$a->strings["Global Support Forum"] = "Globalne forum pomocy technicznej";
$a->strings["Local Support Forum"] = "Lokalne Forum Wsparcia";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Message"] = "Wiadomość";
$a->strings["Your message for new members. You can use bbcode here."] = "Twoja wiadomość dla nowych członków. Możesz użyć bbcode tutaj.";
$a->strings["Add a link to global support forum"] = "Dodaj link do globalnego forum pomocy technicznej";
$a->strings["Should a link to the global support forum be displayed?"] = "Czy powinien być wyświetlany link do globalnego forum pomocy technicznej?";
$a->strings["Add a link to the local support forum"] = "Dodaj link do lokalnego forum pomocy technicznej";
$a->strings["If you have a local support forum and wand to have a link displayed in the widget, check this box."] = "Jeżeli masz lokalne wsparcie forum i różdżki, aby mieć łącze wyświetlane w widżecie, zaznacz to pole wyboru.";
$a->strings["Name of the local support group"] = "Nazwa grupy lokalnej pomocy technicznej";
$a->strings["If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)"] = "Jeśli zaznaczyłeś powyższe, określ tutaj pseudonim lokalnej grupy wsparcia (np. Pomocnicy)";
