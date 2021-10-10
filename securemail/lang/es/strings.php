<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['"Secure Mail" Settings'] = 'Configuración de "Secure Mail"';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Save and send test'] = 'Guardar y enviar prueba';
$a->strings['Enable Secure Mail'] = 'Habilitar correo seguro';
$a->strings['Public key'] = 'Public key';
$a->strings['Your public PGP key, ascii armored format'] = 'Tu clave PGP pública, formato blindado ascii';
$a->strings['Test email sent'] = 'Correo de prueba enviado';
$a->strings['There was an error sending the test email'] = 'Hubo un error al enviar el correo electrónico de prueba.';
