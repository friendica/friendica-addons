<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['XMPP-Chat (Jabber)'] = 'XMPP-Chat (Jabber)';
$a->strings['Enable Webchat'] = 'Habilitar Webchat';
$a->strings['Individual Credentials'] = 'Credenciales individuales';
$a->strings['Jabber BOSH host'] = 'Jabber BOSH host';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Use central userbase'] = 'Utilice la base de usuarios central';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'Si está habilitado, los usuarios iniciarán sesión automáticamente en un servidor ejabberd que debe instalarse en esta máquina con credenciales sincronizadas a través del script "auth_ejabberd.php".';
