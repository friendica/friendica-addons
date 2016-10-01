<?php

if (!function_exists('string_plural_select_es')) {
    function string_plural_select_es($n)
    {
        return $n != 1;
    }
}

$a->strings['"Blockem"'] = '"Bloquealos"';
$a->strings['Comma separated profile URLS to block'] = 'URLS separados por coma para bloquear.';
$a->strings['Save Settings'] = 'Guardar configuración';
$a->strings['BLOCKEM Settings saved.'] = 'Configuración de BLOQUEALOS guardado.';
$a->strings['Blocked %s - Click to open/close'] = '%s bloqueado - click para abrir/cerrar';
$a->strings['Unblock Author'] = 'Desbloquear autor';
$a->strings['Block Author'] = 'Bloquear autor';
$a->strings['blockem settings updated'] = 'configuración de BLOQUEALOS actualizado';
