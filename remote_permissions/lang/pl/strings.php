<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Remote Permissions Settings"] = "Ustawienia uprawnień zdalnych";
$a->strings["Allow recipients of your private posts to see the other recipients of the posts"] = "Zezwalaj odbiorcom prywatnych wpisów na wyświetlanie innych adresatów postów";
$a->strings["Submit"] = "Wyślij";
$a->strings["Remote Permissions settings updated."] = "Zaktualizowano ustawienia uprawnień zdalnych.";
$a->strings["Visible to:"] = "Widoczny dla:";
$a->strings["Visible to"] = "Widoczny dla";
$a->strings["may only be a partial list"] = "mogą być tylko częściowe listy";
$a->strings["Global"] = "Ogólnoświatowy";
$a->strings["The posts of every user on this server show the post recipients"] = "Wpisy każdego użytkownika na tym serwerze pokazują odbiorców wiadomości";
$a->strings["Individual"] = "Indywidualny";
$a->strings["Each user chooses whether his/her posts show the post recipients"] = "Każdy użytkownik wybiera, czy jego/jej posty pokazują odbiorców postów";
$a->strings["Settings updated."] = "Zaktualizowano ustawienia.";
