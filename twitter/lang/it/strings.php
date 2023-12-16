<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Twitter'] = 'Invia a Twitter';
$a->strings['Allow posting to Twitter'] = 'Permetti l\'invio a Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Se abilitato tutti i tuoi messaggi <strong>pubblici</strong> possono essere inviati all\'account Twitter associato. Puoi scegliere di farlo sempre (qui) o ogni volta che invii, nelle impostazioni di privacy del messaggio.';
$a->strings['Send public postings to Twitter by default'] = 'Invia sempre i messaggi pubblici a Twitter';
