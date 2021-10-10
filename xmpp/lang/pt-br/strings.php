<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['XMPP settings updated.'] = 'Configurações de XMPP atualizadas.';
$a->strings['XMPP-Chat (Jabber)'] = 'Bate-papo XMPP (Jabber)';
$a->strings['Enable Webchat'] = 'Habilitar webchat';
$a->strings['Individual Credentials'] = 'Credenciais individuais';
$a->strings['Jabber BOSH host'] = 'Host BOSH de Jabber';
$a->strings['Save Settings'] = 'Salvar configurações';
$a->strings['Use central userbase'] = 'Usar base de usuários central';
$a->strings['If enabled, users will automatically login to an ejabberd server that has to be installed on this machine with synchronized credentials via the "auth_ejabberd.php" script.'] = 'Se habilitado, a autenticação será feita automaticamente em um servidor ejabberd, que precisa ser instalado neste computador com credenciais sincronizadas por meio do script "auth_ejabberd.php".';
$a->strings['Settings updated.'] = 'Configurações atualizadas.';
