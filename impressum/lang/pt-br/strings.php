<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Responsável pelo site';
$a->strings['Email Address'] = 'Endereço de e-mail';
$a->strings['Postal Address'] = 'Endereço postal';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = '';
$a->strings['Settings updated.'] = 'As configurações foram atualizadas.';
$a->strings['Submit'] = 'Enviar';
$a->strings['The page operators name.'] = '';
$a->strings['Site Owners Profile'] = 'Perfil do responsável pelo site';
$a->strings['Profile address of the operator.'] = '';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = '';
$a->strings['Notes'] = '';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = '';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = '';
$a->strings['Footer note'] = 'Nota de rodapé';
$a->strings['Text for the footer. You can use BBCode here.'] = '';
