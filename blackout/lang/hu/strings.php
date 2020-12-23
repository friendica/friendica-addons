<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "A befejezési dátum az áramszünet kezdési dátuma előtt van, ezt javítania kell.";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Ellenőrizze még egyszer az áramszünet jelenlegi beállításait. A kezdete <strong>%s</strong> és a vége <strong>%s</strong> lesz.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Redirect URL"] = "Átirányítási URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "A webről érkező összes látogatója át lesz irányítva erre az URL-re.";
$a->strings["Begin of the Blackout"] = "Az áramszünet kezdete";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "A formátum <tt>ÉÉÉÉ-HH-NN óó:pp</tt>, ahol <em>ÉÉÉÉ</em> az év, <em>HH</em> a hónap, <em>NN</em> a nap, <em>óó</em> az óra és <em>pp</em> a perc.";
$a->strings["End of the Blackout"] = "Az áramszünet vége";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Megjegyzés</strong>: Az átirányítás attól a pillanattól kezdve lesz aktív, amikor megnyomja az elküldés gombot. A jelenleg bejelentkezett felhasználók <strong>nem</strong> lesznek kidobva, de nem tudnak újra bejelentkezni, miután kijelentkeztek és az áramszünet még hatályban van.";
