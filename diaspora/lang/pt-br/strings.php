<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to Diaspora'] = 'Publicar na Diaspora';
$a->strings['Can\'t login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)'] = 'Não foi possível entrar na sua conta Diaspora. Verifique seu nome de usuário e senha e certifique-se que usou o endereço completo (incluindo http...).';
$a->strings['Enable Diaspora Post Addon'] = 'Habilitar plug-in para publicar na Diaspora';
$a->strings['Diaspora username'] = 'Nome de usuário da Diaspora';
$a->strings['Diaspora password'] = 'Senha da Diaspora';
$a->strings['Post to Diaspora by default'] = 'Publicar na Diaspora por padrão';
$a->strings['Save Settings'] = 'Salvar Configurações';
$a->strings['Diaspora post failed. Queued for retry.'] = 'Falha ao publicar na Diaspora. Na fila para tentar novamente.';
