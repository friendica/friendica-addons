<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Este complemento tenta identificar o idioma em que as publicações estão escritas. Se não corresponder a nenhum idioma especificado abaixo, as publicações serão ocultadas, recolhendo-as.';
$a->strings['Use the language filter'] = 'Usar o filtro de idiomas';
$a->strings['Able to read'] = 'Capaz de ler';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Lista de abreviaturas (códigos ISO 639-1) para os idiomas que você fala, separadas por vírgulas. Por exemplo"pt-br,it".';
$a->strings['Minimum confidence in language detection'] = 'Confiança mínima na detecção do idioma';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Confiança mínima na exatidão da detecção do idioma, de 0 a 100. As publicações não serão filtradas quando a confiança na detecção do idioma estiver abaixo desta porcentagem.';
$a->strings['Minimum length of message body'] = 'Tamanho mínimo do corpo da mensagem';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Número mínimo de caracteres no corpo da mensagem para aplicação do filtro. As publicações mais curtas que o estipulado não serão filtradas. Atenção: a detecção de idiomas não é confiável para conteúdos curtos (< 200 caracteres).';
$a->strings['Language Filter'] = 'Filtro de Idiomas';
$a->strings['Save Settings'] = 'Salvar configurações';
$a->strings['Filtered language: %s'] = 'Idioma filtrado: %s';
