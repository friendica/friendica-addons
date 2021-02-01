<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["IRC Settings"] = "Ajustes de IRC";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "Aquí puede cambiar los ajustes de todo el sistema de los canales para unirse y acceder automáticamente mediante la barra lateral. Note que los cambios que hace aquí sólo afectan a la selección del canal si usted está conectado.";
$a->strings["Save Settings"] = "Guardar Ajustes";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Canal(s) para autocorregir (separados por comas)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Lista de canales que se conectarán automáticamente cuando la aplicación sea lanzada.";
$a->strings["Popular Channels (comma separated)"] = "Canales Populares (separados por comas)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "Lista de canales populares, se mostrará al lado y tendrá un enlace para unirse fácilmente.";
$a->strings["IRC settings saved."] = "Ajustes de IRC guardados.";
$a->strings["IRC Chatroom"] = "Sala de chat de IRC";
$a->strings["Popular Channels"] = "Canales Populares";
