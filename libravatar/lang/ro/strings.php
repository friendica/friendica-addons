<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3"] = "NU s-a putut instala Libravatar cu succes.<br>Acesta necesită PHP >= 5.3";
$a->strings["generic profile image"] = "imagine generică de profil";
$a->strings["random geometric pattern"] = "șablon geometric aleator";
$a->strings["monster face"] = "chip monstruos";
$a->strings["computer generated face"] = "chip generat de calculator";
$a->strings["retro arcade style face"] = "chip în stil jocuri arcade retro";
$a->strings["Warning"] = "Atenție";
$a->strings["Your PHP version %s is lower than the required PHP >= 5.3."] = "Versiunea dumneavoastră PHP %s este inferioară celei necesare PHP >= 5.3.";
$a->strings["This addon is not functional on your server."] = "Acest supliment nu este funcțional pe serverul dumneavoastră.";
$a->strings["Information"] = "Informaţii";
$a->strings["Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "Suplimentul Gravatar este instalat. Vă rugăm să dezactivați suplimentul Gravatar.<br> Suplimentul Libravatar va reveni înapoi la Gravatar, dacă nu s-a găsit nimic în Libravatar.";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Default avatar image"] = "Imagine avatar implicită";
$a->strings["Select default avatar image if none was found. See README"] = "Selectați imaginea avatar implicită, dacă nici una nu fost găsită. Consultați FIŞIERUL README";
$a->strings["Libravatar settings updated."] = "Configurările Libravatar au fost actualizate.";
