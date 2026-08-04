<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['%s Administrator'] = 'Administrador de %s';
$a->strings['%1$s, %2$s Administrator'] = '%1$s, administrador de %2$s';
$a->strings['Send email to all members'] = 'Enviar e-mail para todos os membros';
$a->strings['No recipients found.'] = 'Não foi encontrado nenhum destinatário.';
$a->strings['Emails sent'] = 'E-mails enviados';
$a->strings['Send email to all members of this Friendica instance.'] = 'Enviar e-mail para todos os membros desta instância do Friendica.';
$a->strings['Message subject'] = 'Assunto da mensagem';
$a->strings['Test mode (only send to administrator)'] = 'Modo de teste (enviar somente para o administrador)';
$a->strings['Submit'] = 'Enviar';
