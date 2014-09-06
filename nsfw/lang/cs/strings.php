<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Not Safe For Work (General Purpose Content Filter)"] = "Not Safe For Work (General Purpose Content Filter)";
$a->strings["This plugin looks in posts for the words/text you specify below, and collapses any content containing those keywords so it is not displayed at inappropriate times, such as sexual innuendo that may be improper in a work setting. It is polite and recommended to tag any content containing nudity with #NSFW.  This filter can also match any other word/text you specify, and can thereby be used as a general purpose content filter."] = "Tento plugin hledá v příspěvcích slova zadáná níže a skryje jakýkoliv obsah, který tyto slova obsahuje v prostředích, kde to není vhodné. Je slušné a doporučené jakékoliv příspěvky s mahotou označit s #NSFW.  Tento filtr může také vyhledávat jakékoliv Vámi specifikované slovní spojení, takže může být využit jako obecný kontextový filtr.";
$a->strings["Enable Content filter"] = "Povolit Kontextový filtr";
$a->strings["Comma separated list of keywords to hide"] = "Čárkou oddělený seznam klíčových slov ke skrytí";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Use /expression/ to provide regular expressions"] = "Použít /výraz/ pro použití regulárních výrazů";
$a->strings["NSFW Settings saved."] = "NSFW nastavení uloženo";
$a->strings["%s - Click to open/close"] = "%s - Klikněte pro otevření/zavření";
