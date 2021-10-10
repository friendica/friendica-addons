<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'La fecha de finalización es anterior a la fecha de inicio del bloqueo, debe corregirlo.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Verifique la configuración actual del bloqueo. Iniciará  <strong>%s</strong> finalizará <strong>%s</strong>.';
$a->strings['Save Settings'] = 'Guardar configuración';
$a->strings['Redirect URL'] = 'Redirigir URL';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Todos sus visitantes de la web serán redirigidos a esta URL.';
$a->strings['Begin of the Blackout'] = 'Inicio del apagón.';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> año, <em>MM</em> mes, <em>DD</em> dia, <em>hh</em> hora y <em>mm</em> minuto.';
$a->strings['End of the Blackout'] = 'Fin del apagón.';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Nota</strong>: La redirección estará activa desde el momento en que presione el botón Enviar. Los usuarios que hayan iniciado sesión actualmente  <strong>no</strong> serán expulsados, pero no podrán volver a iniciar sesión después de cerrar la sesión mientras el apagón continúe.';
