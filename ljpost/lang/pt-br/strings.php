<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to LiveJournal'] = 'Publicar no LiveJournal';
$a->strings['LiveJournal username'] = 'Nome de usuário no LiveJournal';
$a->strings['LiveJournal password'] = 'Senha do LiveJournal';
$a->strings['Post to LiveJournal by default'] = 'Publicar no LiveJournal por padrão';
