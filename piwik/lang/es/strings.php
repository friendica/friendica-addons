<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Este sitio web se rastrea mediante la herramienta de análisis <a href='http://www.matomo.org'>Matomo</a>.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Si no desea que sus visitas se registren de esta manera, <a href='%s'> puede configurar una cookie para evitar que Matomo / Piwik rastree más visitas del sitio </a> (optar por no participar).";
$a->strings["Save Settings"] = "Guardar ajustes";
$a->strings["Matomo (Piwik) Base URL"] = "Matomo (Piwik) URL Base";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Ruta absoluta a su instalación de Matomo (Piwik). (sin protocolo (http/s), con barra diagonal al final)";
$a->strings["Site ID"] = "ID de la página";
$a->strings["Show opt-out cookie link?"] = "Mostrar enlace a cláusula de opción de cookie";
$a->strings["Asynchronous tracking"] = "Rastreo asíncrono";
