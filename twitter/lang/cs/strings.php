<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Post to Twitter'] = 'Poslat příspěvek na Twitter';
$a->strings['Allow posting to Twitter'] = 'Povolit odesílání na Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Je-li povoleno, všechny Vaše <strong>veřejné</strong> příspěvky mohou být zasílány na související účet na Twitteru. Můžete si vybrat, zda-li toto bude výchozí nastavení (zde), nebo budete mít možnost si vybrat požadované chování při psaní každého příspěvku.';
$a->strings['Send public postings to Twitter by default'] = 'Defaultně zasílat veřejné komentáře na Twitter';
