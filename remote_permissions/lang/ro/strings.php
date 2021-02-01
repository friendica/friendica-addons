<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Remote Permissions Settings"] = "Configurări Permisiuni la Distanță";
$a->strings["Allow recipients of your private posts to see the other recipients of the posts"] = "Permite destinatarilor, posturile dvs. private, să-i vadă și pe ceilalți destinatari ai postărilor";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Remote Permissions settings updated."] = "Configurările Permisiunilor la Distanță, au fost actualizate.";
$a->strings["Visible to:"] = "Vizibil pentru :";
$a->strings["Visible to"] = "Vizibil pentru";
$a->strings["may only be a partial list"] = "poate fi doar o listă parțială";
$a->strings["Global"] = "Global";
$a->strings["The posts of every user on this server show the post recipients"] = "Postările fiecărui utilizator de pe acest server, afișează destinatarii mesajului";
$a->strings["Individual"] = "Individual";
$a->strings["Each user chooses whether his/her posts show the post recipients"] = "Fiecare utilizator alege dacă postările lui/ei, afișează și destinatarii mesajului";
$a->strings["Settings updated."] = "Configurări actualizate.";
