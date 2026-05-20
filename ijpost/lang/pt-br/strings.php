<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Insanejournal'] = 'Publicar no Insanejournal';
$a->strings['InsaneJournal username'] = 'Nome de usuário no InsaneJournal';
$a->strings['InsaneJournal password'] = 'Senha do InsaneJournal';
$a->strings['Post to InsaneJournal by default'] = 'Publicar no InsaneJournal por padrão';
