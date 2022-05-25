<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['%s Administrator'] = 'L\'administrateur de %s';
$a->strings['%1$s, %2$s Administrator'] = 'L\'administrateur de %1$s, %2$s.';
$a->strings['Send email to all members'] = 'Envoyer un courriel à tous les membres';
$a->strings['No recipients found.'] = 'Aucun destinataire trouvé.';
$a->strings['Emails sent'] = 'Courriels envoyés';
$a->strings['Send email to all members of this Friendica instance.'] = 'Envoyer un courriel à tous les membres de cet instance Friendica.';
$a->strings['Message subject'] = 'Objet du message';
$a->strings['Test mode (only send to administrator)'] = 'Mode test (envoyer uniquement à l\'administrateur)';
$a->strings['Submit'] = 'Envoyer';
