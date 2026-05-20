<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['New Member'] = 'Novo Membro';
$a->strings['Tips for New Members'] = 'Dicas para Novos Membros';
$a->strings['Save Settings'] = 'Salva configurações';
$a->strings['Message'] = 'Mensagem';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Sua mensagem para novos membros. Você pode usar bbcode aqui.';
$a->strings['Name of the local support group'] = 'Nome do fórum local de suporte';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Se você marcou  opção acima, especifique o <em>apelido</em> do grupo de suporte local aqui (exemplo: helpers)';
