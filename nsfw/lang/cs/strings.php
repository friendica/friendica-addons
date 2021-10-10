<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Content Filter (NSFW and more)'] = 'Filtr obsahu (citlivý obsah a další)';
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Tento doplněk vyhledává specifikovaná slova/text v příspěvcích a skrývá je. Může být použit pro filtrování obsahu označeného štítky, jako například #NSFW, který může být považován za nevhodný v určitých časech či místech, například když jste v práci. Může být také užitečný ke skrývání nepodstatného či nepříjemného obsahu z přímého pohledu.';
$a->strings['Enable Content filter'] = 'Povolit filtr obsahu';
$a->strings['Comma separated list of keywords to hide'] = 'Čárkou oddělený seznam klíčových slov ke skrytí';
$a->strings['Save Settings'] = 'Uložit nastavení';
$a->strings['Use /expression/ to provide regular expressions'] = 'Použijte /výraz/ pro použití regulárních výrazů';
$a->strings['NSFW Settings saved.'] = 'Nastavení NSFW uloženo';
$a->strings['Filtered tag: %s'] = 'Filtrovaná značka: %s';
$a->strings['Filtered word: %s'] = 'Filtrované slovo: %s';
