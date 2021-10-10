<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Send email to all members'] = 'Envoyer un courriel à tous les membres';
$a->strings['No recipients found.'] = 'Aucun destinataire trouvé.';
$a->strings['Emails sent'] = 'Courriels envoyés';
$a->strings['Send email to all members of this Friendica instance.'] = 'Envoyer un courriel à tous les membres de cet instance Friendica.';
$a->strings['Message subject'] = 'Objet du message';
$a->strings['Test mode (only send to administrator)'] = 'Mode test (envoyer uniquement à l\'administrateur)';
$a->strings['Submit'] = 'Envoyer';
