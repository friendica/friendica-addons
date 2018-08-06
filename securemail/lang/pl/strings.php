<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["\"Secure Mail\" Settings"] = "Ustawienia \"Bezpieczna poczta\"";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Save and send test"] = "Zapisz i wyślij test";
$a->strings["Enable Secure Mail"] = "Włącz bezpieczną pocztę";
$a->strings["Public key"] = "Klucz publiczny";
$a->strings["Your public PGP key, ascii armored format"] = "Twój publiczny klucz PGP, rekomendowany format ascii";
$a->strings["Secure Mail Settings saved."] = "Ustawienia bezpiecznej poczty zostały zapisane.";
$a->strings["Test email sent"] = "Wysłano testowy e-mail";
$a->strings["There was an error sending the test email"] = "Wystąpił błąd podczas wysyłania e-maila testowego";
