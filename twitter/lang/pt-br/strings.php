<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to Twitter'] = 'Publicar no Twitter';
$a->strings['Twitter settings updated.'] = 'As configurações do Twitter foram atualizadas.';
$a->strings['Twitter Posting Settings'] = 'Configurações de publicação no Twitter';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Não foi encontrado nenhum par de "consumer keys" para o Twitter. Por favor, entre em contato com a administração do site.';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'O plug-in do Twitter está habilitado nesta instância do Friendica, mas você ainda não conectou sua conta aqui à sua conta no Twitter. Para fazer isso, clique no botão abaixo. Você vai receber um código de verificação do Twitter. Copie-o para o campo abaixo e envie o formulário. Apenas os seus posts <strong>públicos</strong> serão publicados no Twitter.';
$a->strings['Log in with Twitter'] = 'Entrar com o Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Cole o código de verificação do Twitter aqui';
$a->strings['Submit'] = 'Enviar';
$a->strings['Currently connected to: '] = 'Atualmente conectado a:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Se habilitado, todos os seus posts <strong>públicos</strong> poderão ser replicados na conta do Twitter associada. Você pode escolher entre fazer isso por padrão (aqui) ou separadamente, quando escrever cada mensagem, nas opções de publicação.';
$a->strings['Allow posting to Twitter'] = 'Permitir a publicação no Twitter';
$a->strings['Send public postings to Twitter by default'] = 'Publicar posts públicos no Twitter por padrão';
$a->strings['Settings updated.'] = 'As configurações foram atualizadas.';
