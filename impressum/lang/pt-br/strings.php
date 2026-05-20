<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Responsável pelo site';
$a->strings['Email Address'] = 'Endereço de e-mail';
$a->strings['Postal Address'] = 'Endereço postal';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'O addon impressum precisa ser configurado. <br />Por favor adicione ao menos a variável  <tt> responsável</tt> ao seu arquivo de configuração. Para as outras variáveis por favor consulte o arquivo LEIA-ME do addon.';
$a->strings['Save Settings'] = 'Salvar configurações';
$a->strings['The page operators name.'] = 'Página do nome do operador.';
$a->strings['Site Owners Profile'] = 'Perfil do responsável pelo site';
$a->strings['Profile address of the operator.'] = 'Endereço do perfil do operador.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Como entrar em contato com o operador via e-mail. Você pode usar BBCode aqui.';
$a->strings['Notes'] = 'Notas';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Notas adicionais que são mostradas abaixo das informações de contato. Você pode usar BBCode aqui.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Como contactar o operador via e-mail. (será ofuscado)';
$a->strings['Footer note'] = 'Nota de rodapé';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Texto para o rodapé. Você pode usar BBCode aqui.';
