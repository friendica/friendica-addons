<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Permission denied.'] = 'Permiso denegado';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['Client ID'] = 'ID de cliente';
$a->strings['Client Secret'] = 'Secreto de cliente';
$a->strings['Error when registering buffer connection:'] = 'Error al registrar cunexión de buffer';
$a->strings['You are now authenticated to buffer. '] = 'Ahora está autenticado al fufer';
$a->strings['return to the connector page'] = 'Vuelva a la página de conexión';
$a->strings['Post to Buffer'] = 'Publique en Buffer';
$a->strings['Buffer Export'] = 'Exportar Buffer';
$a->strings['Authenticate your Buffer connection'] = 'Autenticar su conexión de Buffer';
$a->strings['Enable Buffer Post Addon'] = 'Habilitar el complemento de publicación de Buffer';
$a->strings['Post to Buffer by default'] = 'Publicar en Buffer por defecto';
$a->strings['Check to delete this preset'] = 'Verificar para eliminar este preajuste';
$a->strings['Posts are going to all accounts that are enabled by default:'] = 'Las publicaciones van a todas las cuentas que estén habilitadas por defecto';
