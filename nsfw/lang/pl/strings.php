<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Filtr zawartości (NSFW i więcej)";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "Ten dodatek szuka określonych słów/tekstów w postach i zwija je. Może służyć do filtrowania treści oznaczonych np. NSFW, które mogą zostać uznane za nieodpowiednie w określonych momentach lub miejscach, na przykład w pracy. Jest to również przydatne do ukrywania nieistotnych lub irytujących treści z bezpośredniego widoku.";
$a->strings["Enable Content filter"] = "Włącz filtr treści";
$a->strings["Comma separated list of keywords to hide"] = "Rozdzielana przecinkami lista słów kluczowych do ukrycia";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Use /expression/ to provide regular expressions"] = "Użyj /wyrażenia/, aby zapewnić wyrażenia regularne";
$a->strings["NSFW Settings saved."] = "Ustawienia NSFW zostały zapisane.";
$a->strings["%s - Click to open/close"] = "%s - Kliknij, aby otworzyć/zamknąć";
