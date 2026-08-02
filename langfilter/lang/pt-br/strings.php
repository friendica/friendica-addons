<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Language Filter'] = 'Filtro de Idiomas';
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Este complemento tenta identificar a língua em que as publicações são escritas. Uma publicação que não se encaixe em nenhum dos idiomas especificados abaixo será ocultada por colapsamento.';
$a->strings['Use the language filter'] = 'Usar o filtro de idiomas';
$a->strings['Able to read'] = 'Falo';
$a->strings['List of abbreviations (iso2 codes) for languages you speak, comma separated. For example "de,it".'] = 'Lista de abreviações (códigos ISO 2) para as línguas que você fala, separadas por vírgula. Por exemplo, "de,it".';
$a->strings['Minimum confidence in language detection'] = 'Confiança mínima na detecção do idioma';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Confiança mínima na exatidão da detecção do idioma, de 0 a 100. As publicações não serão filtradas quando a confiança na detecção do idioma estiver abaixo desta porcentagem.';
$a->strings['Minimum length of message body'] = 'Tamanho mínimo do corpo da mensagem';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Número mínimo de caracteres no corpo da mensagem para aplicação do filtro. As publicações mais curtas que o estipulado não serão filtradas. Atenção: a detecção de idiomas não é confiável para conteúdos curtos (< 200 caracteres).';
$a->strings['Save Settings'] = 'Salvar configurações';
$a->strings['Language Filter Settings saved.'] = 'Configurações do Filtro de Idiomas salvas.';
$a->strings['Filtered language: %s'] = 'Idioma filtrado: %s';
