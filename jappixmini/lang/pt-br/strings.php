<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Jappix Mini addon settings'] = '';
$a->strings['Activate addon'] = '';
$a->strings['Do <em>not</em> insert the Jappixmini Chat-Widget into the webinterface'] = '';
$a->strings['Jabber username'] = 'Nome de usuário no Jabber';
$a->strings['Jabber server'] = '';
$a->strings['Jabber BOSH host'] = '';
$a->strings['Jabber password'] = 'Senha do Jabber';
$a->strings['Encrypt Jabber password with Friendica password (recommended)'] = 'Criptografar senha de Jabber com senha do Friendica (recomendado)';
$a->strings['Friendica password'] = 'Senha do Friendica';
$a->strings['Approve subscription requests from Friendica contacts automatically'] = '';
$a->strings['Subscribe to Friendica contacts automatically'] = '';
$a->strings['Purge internal list of jabber addresses of contacts'] = '';
$a->strings['Submit'] = 'Enviar';
$a->strings['Add contact'] = '';
