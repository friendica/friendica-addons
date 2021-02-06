<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Publicar hacia Diaspora*";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "";
$a->strings["All aspects"] = "Todos los aspectos";
$a->strings["Public"] = "Publico";
$a->strings["Post to aspect:"] = "Publicar para aspecto";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Conectado con tu cuenta de Diaspora <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "";
$a->strings["Diaspora Export"] = "Exportar a Diaspora*";
$a->strings["Information"] = "";
$a->strings["Error"] = "";
$a->strings["Save Settings"] = "Guardar configuración";
$a->strings["Enable Diaspora Post Addon"] = "";
$a->strings["Diaspora handle"] = "";
$a->strings["Diaspora password"] = "Contraseña Diaspora*";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "";
$a->strings["Post to Diaspora by default"] = "Publicar hacia Diaspora* como estándar.";
