<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['New Member'] = 'Nuovi Utenti';
$a->strings['Tips for New Members'] = 'Consigli per i Nuovi Utenti';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Message'] = 'Messaggio';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Il tuo messaggio per i nuovi utenti. Puoi usare BBCode';
$a->strings['Name of the local support group'] = 'Nome del gruppo locale di supporto';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Se hai selezionato il box sopra, specifica qui il <em>nome utente</em> del gruppo locale di supporto (e.s. \'supporto\')';
