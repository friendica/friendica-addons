<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['New Member'] = 'Novo Membro';
$a->strings['Tips for New Members'] = 'Dicas para Novos Membros';
$a->strings['Global Support Forum'] = 'Fórum de suporte global';
$a->strings['Local Support Forum'] = 'Fórum de suporte local';
$a->strings['Save Settings'] = 'Salva configurações';
$a->strings['Message'] = 'Mensagem';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Sua mensagem para novos membros. Você pode usar bbcode aqui.';
$a->strings['Add a link to global support forum'] = 'Adicione um link para o fórum de suporte global';
$a->strings['Should a link to the global support forum be displayed?'] = 'Um link para o fórum de suporte global deve ser mostrado?';
$a->strings['Add a link to the local support forum'] = 'Adicione um link para o fórum de suporte local';
$a->strings['If you have a local support forum and wand to have a link displayed in the widget, check this box.'] = 'Se você tem um fórum de suporte local e quer mostrar um link no widget marque essa opção';
$a->strings['Name of the local support group'] = 'Nome do fórum local de suporte';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Se você marcou  opção acima, especifique o <em>apelido</em> do grupo de suporte local aqui (exemplo: helpers)';
