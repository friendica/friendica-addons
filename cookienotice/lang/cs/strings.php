<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["This website uses cookies. If you continue browsing this website, you agree to the usage of cookies."] = "Tato stránka používá cookies. Pokud budete pokračovat v používání této stránky, souhlasíte s používáním cookies.";
$a->strings["OK"] = "OK";
$a->strings["\"cookienotice\" Settings"] = "Nastavení „cookienotice“";
$a->strings["<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button."] = "<b>Nastavte si vaše oznámení o používání cookies.</b> Mělo by to být pouze oznámení říkající, že stránka používá cookies. Zobrazí se, dokud uživatel neklikne na tlačítko OK.";
$a->strings["Cookie Usage Notice"] = "Oznámení o používání cookies";
$a->strings["The cookie usage notice"] = "Oznámení o používání cookies";
$a->strings["OK Button Text"] = "Text tlačítka OK";
$a->strings["The OK Button text"] = "Text tlačítka OK";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["cookienotice Settings saved."] = "Nastavení cookienotice uložena.";
$a->strings["This website uses cookies to recognize revisiting and logged in users. You accept the usage of these cookies by continue browsing this website."] = "Tato stránka používá cookies pro rozpoznávání znovu navštěvujících a přihlášených uživatelů. Pokud budete pokračovat v používání této stránky, souhlasíte s používáním cookies.";
