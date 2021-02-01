<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3"] = "Podría NO instalar Libravatar con éxito.<br>Requiere PHP >= 5.3";
$a->strings["generic profile image"] = "Imagen de perfil genérica";
$a->strings["random geometric pattern"] = "Estampado geométrico aleatorio";
$a->strings["monster face"] = "cara de monstruo";
$a->strings["computer generated face"] = "Cara generada por ordenador";
$a->strings["retro arcade style face"] = "Cara de estilo retro";
$a->strings["Warning"] = "Advertencia";
$a->strings["Your PHP version %s is lower than the required PHP >= 5.3."] = "Su versión PHP %s es inferior a la requerida PHP >= 5.3.";
$a->strings["This addon is not functional on your server."] = "Este complemento no es funcional en su servidor";
$a->strings["Information"] = "Información";
$a->strings["Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar."] = "El complemento Gravatar se ha instalado. Por favor desactive el complemento Gravatar. <br>El complemento Libravatar quedará por detrás de Gravatar si no se encuentra nada en Libravatar.";
$a->strings["Submit"] = "Enviar";
$a->strings["Default avatar image"] = "Imagen de avatar por defecto";
$a->strings["Select default avatar image if none was found. See README"] = "Selecione el avatar por defecto si no se encuentra ninguno. Vea README";
$a->strings["Libravatar settings updated."] = "Ajustes de Libravatar actualizados";
