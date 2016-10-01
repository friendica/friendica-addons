<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Login'] = 'Entrar';
$a->strings['OpenID'] = 'OpenID';
$a->strings['Latest users'] = 'Usuários mais recentes';
$a->strings['Most active users'] = 'Usuários mais ativos';
$a->strings['Latest photos'] = 'Fotos mais recentes';
$a->strings['Contact Photos'] = 'Fotos dos Contatos';
$a->strings['Profile Photos'] = 'Fotos do Perfil';
$a->strings['Latest likes'] = 'Curtidas recentes';
$a->strings['event'] = 'evento';
$a->strings['status'] = 'status';
$a->strings['photo'] = 'foto';
$a->strings["%1\$s likes %2\$s's %3\$s"] = '%1$s curtiu %2$s que publicou %3$s';
$a->strings['Welcome to %s'] = 'Bem-vindo a %s';
