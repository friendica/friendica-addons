<?php

if (!function_exists('string_plural_select_es')) {
    function string_plural_select_es($n)
    {
        return $n != 1;
    }
}

$a->strings['Post to Diaspora'] = 'Publicar hacia Diaspora*';
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = 'No se puede ingresar a tu cuenta de Diaspora*. Por favor verificar nombre de usuario, contraseña y asegura de usar la dirección completa, incluyendo https.. .';
$a->strings['Diaspora Export'] = 'Exportar a Diaspora*';
$a->strings['Enable Diaspora Post Plugin'] = 'Habilitar publicar a traves de Diaspora* plugin.';
$a->strings['Diaspora username'] = 'Nombre de usuario de Diaspora*.';
$a->strings['Diaspora password'] = 'Contraseña Diaspora*';
$a->strings['Diaspora site URL'] = 'URL sitio Diaspora*';
$a->strings['Post to Diaspora by default'] = 'Publicar hacia Diaspora* como estándar.';
$a->strings['Save Settings'] = 'Guardar configuración';
$a->strings['Diaspora post failed. Queued for retry.'] = 'La publicación hacia Diaspora* fallo, puesto en espera para nuevo intento.';
