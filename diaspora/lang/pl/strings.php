<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Post to Diaspora"] = "Napisz do Diaspory";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Ten łącznik jest przeznaczony do tego, gdy nadal chcesz korzystać ze starego konta Diaspora przez jakiś czas.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "";
$a->strings["All aspects"] = "Wszystkie aspekty";
$a->strings["Public"] = "Publiczny";
$a->strings["Post to aspect:"] = "";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "";
$a->strings["Diaspora Export"] = "Eksportuj do Diaspory";
$a->strings["Information"] = "";
$a->strings["Error"] = "Błąd";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Enable Diaspora Post Addon"] = "Włącz dodatek Diaspora";
$a->strings["Diaspora handle"] = "";
$a->strings["Diaspora password"] = "Hasło Diaspora";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "";
$a->strings["Post to Diaspora by default"] = "Wyślij domyślnie do Diaspory";
$a->strings["Diaspora settings updated."] = "";
$a->strings["Diaspora connector disabled."] = "";
