<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['generic profile image'] = 'imagine generică de profil';
$a->strings['random geometric pattern'] = 'șablon geometric aleator';
$a->strings['monster face'] = 'chip monstruos';
$a->strings['computer generated face'] = 'chip generat de calculator';
$a->strings['retro arcade style face'] = 'chip în stil jocuri arcade retro ';
$a->strings['Information'] = 'Informaţii';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Modulul Libravatar este instalat, de asemenea. Vă rugăm să dezactivați modulul Libravatar sau acest modul Gravatar. <br>Modulul Libravatar va reveni înapoi la Gravatar, dacă nu s-a găsit nimic în Libravatar.';
$a->strings['Submit'] = 'Trimite';
$a->strings['Default avatar image'] = 'Imagine avatar implicită';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Selectați imagine avatar implicită, dacă nici una nu fost găsită în Gravatar. Consultați FIŞIERUL README';
$a->strings['Rating of images'] = 'Evaluările imaginilor';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Selectați evaluarea adecvată a avatarului pentru site-ul dumneavoastră. Consultați FIŞIERUL README';
$a->strings['Gravatar settings updated.'] = 'Configurările Gravatar au fost actualizate.';
