<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Diaspora"] = "Publicar hacia Diaspora*";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "Recuerde: siempre puede ser contactado desde Diaspora con su identificador de Friendica <strong>%s</strong>. ";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "Este conector solo está diseñado si aún desea usar su antigua cuenta de Diaspora durante algún tiempo.";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "Sin embargo, es preferible que le diga a sus contactos de Diaspora el nuevo identificador <strong>%s</strong> en su lugar.";
$a->strings["All aspects"] = "Todos los aspectos";
$a->strings["Public"] = "Publico";
$a->strings["Post to aspect:"] = "Publicar para aspecto";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "Conectado con tu cuenta de Diaspora <strong>%s</strong>";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "No puedo iniciar sesión en su cuenta de Diaspora. Compruebe el identificador (en formato user@domain.tld) y la contraseña.";
$a->strings["Diaspora Export"] = "Exportar a Diaspora*";
$a->strings["Information"] = "Información";
$a->strings["Error"] = "Error";
$a->strings["Save Settings"] = "Guardar configuración";
$a->strings["Enable Diaspora Post Addon"] = "Habilitar publicar a traves de Diaspora* plugin.";
$a->strings["Diaspora handle"] = "Diaspora handle";
$a->strings["Diaspora password"] = "Contraseña Diaspora*";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "Aviso de privacidad: su contraseña de Diaspora se almacenará sin cifrar para autenticarlo con su pod de Diaspora. Esto significa que su administrador de nodo de Friendica puede tener acceso a él.";
$a->strings["Post to Diaspora by default"] = "Publicar hacia Diaspora* como estándar.";
