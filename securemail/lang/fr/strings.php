<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Secure Mail'] = 'Activer l\'extension des emails sécurisés';
$a->strings['Public key'] = 'Clé publique';
$a->strings['Your public PGP key, ascii armored format'] = 'Votre clé publique PGP formatée compatible ASCII';
$a->strings['"Secure Mail" Settings'] = 'Paramètres des emails sécurisés';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['Save and send test'] = 'Enregistrer et envoyer un message de test';
$a->strings['Test email sent'] = 'Message de test envoyé avec succès';
$a->strings['There was an error sending the test email'] = 'Une erreur est survenue pendant l\'envoi du message de test';
