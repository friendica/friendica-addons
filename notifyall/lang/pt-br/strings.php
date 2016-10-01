<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Send email to all members'] = 'Enviar e-mail para todos os membros';
$a->strings['%s Administrator'] = 'Administrador de %s';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, administrador de %2$s';
$a->strings['No recipients found.'] = 'Não foi encontrado nenhum destinatário.';
$a->strings['Emails sent'] = 'E-mails enviados';
$a->strings['Send email to all members of this Friendica instance.'] = 'Enviar e-mail para todos os membros desta instância do Friendica.';
$a->strings['Message subject'] = 'Assunto da mensagem';
$a->strings['Test mode (only send to administrator)'] = 'Modo de teste (enviar só para o administrador)';
$a->strings['Submit'] = 'Enviar';
