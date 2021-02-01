<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Diaspora"] = "Beküldés a Diasporára";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Ne feledje: Ön mindig elérhető a Diasporáról a(z) <strong>%s</strong> Friendica kezelőjével. ";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Ez az összekötő csak akkor szükséges, ha továbbra is használni szeretné a régi Diaspora-fiókját egy ideig. ";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Azonban az ajánlott eljárás az, hogy inkább mondja meg a Diaspora partnereinek az új <strong>%s</strong> kezelőt.";
$a->strings["All aspects"] = "Minden szempont";
$a->strings["Public"] = "Nyilvános";
$a->strings["Post to aspect:"] = "Beküldés a szempontba:";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Kapcsolódva a(z) <strong>%s</strong> Diaspora-fiókjával";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "Nem lehet bejelentkezni a Diaspora-fiókjába. Ellenőrizze a kezelőt (felhasználó@tartomány.tld formátumban) és a jelszót.";
$a->strings["Diaspora Export"] = "Diaspora exportálás";
$a->strings["Information"] = "Információ";
$a->strings["Error"] = "Hiba";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Enable Diaspora Post Addon"] = "A Diaspora-beküldő bővítmény engedélyezése";
$a->strings["Diaspora handle"] = "Diaspora kezelő";
$a->strings["Diaspora password"] = "Diaspora jelszó";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Adatvédelmi figyelmeztetés: a Diaspora jelszava titkosítatlanul lesz eltárolva, hogy hitelesítse Önt a Diaspora csomópontján. Ez azt jelenti, hogy a Friendica csomópontjának adminisztrátora hozzáférhet.";
$a->strings["Post to Diaspora by default"] = "Beküldés a Diasporára alapértelmezetten";
$a->strings["Diaspora settings updated."] = "A Diaspora beállításai frissítve.";
$a->strings["Diaspora connector disabled."] = "A Diaspora összekötő letiltva.";
