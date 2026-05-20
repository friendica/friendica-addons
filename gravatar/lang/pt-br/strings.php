<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['generic profile image'] = 'imagem de perfil genérica';
$a->strings['random geometric pattern'] = 'estampa geométrica aleatória';
$a->strings['monster face'] = 'careta';
$a->strings['computer generated face'] = 'rosto gerado por computador';
$a->strings['retro arcade style face'] = 'rosto de personagem de fliperama';
$a->strings['Information'] = 'Informação';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'O complemento Libravatar também está instalado. Desabilite o  Libravatar ou este complemento, o Gravatar.<br>Se não encontrar nada, o Libravatar remeterá ao Gravatar.';
$a->strings['Default avatar image'] = 'Avatar padrão';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Selecione a imagem de avatar padrão que será usada se nenhuma for encontrada no Gravatar. Veja README.';
$a->strings['Rating of images'] = 'Classificação de imagens';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Selecione a classificação apropriada para os avatares que serão mostrados no seu site. Veja README.';
