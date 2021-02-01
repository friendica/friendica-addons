<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["IRC Settings"] = "Configurações do IRC";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "Aqui você pode mudar as configurações dos canais para dar opções de inscrição e de acesso automáticos pela barra lateral. Observe que as mudanças feitas aqui só afetam o canal selecionado se você tiver feito login.";
$a->strings["Save Settings"] = "Salvar Configurações";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Canais de conexão automática (separados por vírgula)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Lista de canais a serem conectados automaticamente quando o aplicativo for iniciado.";
$a->strings["Popular Channels (comma separated)"] = "Canais Populares (separados por vírgula)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "Lista de canais populares, que será exibida ao lado e terá link para facilitar a inscrição.";
$a->strings["IRC settings saved."] = "As configurações do IRC foram salvas.";
$a->strings["IRC Chatroom"] = "Sala de bate-papo IRC";
$a->strings["Popular Channels"] = "Canais Populares";
