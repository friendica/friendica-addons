<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Gnot settings updated."] = "S'ha actualitzat la configuració de Gnot.";
$a->strings["Gnot Settings"] = "Configuració de gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "Permet llançar les notificacions de comentaris de correu electrònic a Gmail i anonimitzar la línia de l’assumpte.";
$a->strings["Enable this addon?"] = "Activar aquest addon?";
$a->strings["Submit"] = "Envia";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "[Friendica:Notify] comenta la conversa #%d";
