<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "La data de finalització és anterior a la data d'inici de l'apagada, hauríeu d'arreglar-ho";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Verifiqueu si la configuració actual per a l'apagat. Començarà serà <strong>%s</strong> i s’acabarà <strong>%s</strong>.";
$a->strings["Save Settings"] = "Desa la configuració";
$a->strings["Redirect URL"] = "Redirigir URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "tots els visitants del web seran redirigits a aquest tema URL";
$a->strings["Begin of the Blackout"] = "Inici de l’apagada";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "El format és <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> mes. <em>DD</em> day, <em>hh</em>hora i <em>mm</em> minut.";
$a->strings["End of the Blackout"] = "Fi de l’apagada";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Nota</strong>: La redirecció estarà activa des del moment en què premeu el botó d'enviament. Els usuaris actualment connectats ho faran <strong>no</strong> es llençarà però no es pot tornar a iniciar la sessió un cop s'hagi desactivat l'apagada.";
