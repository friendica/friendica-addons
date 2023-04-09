<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to GNU Social'] = 'Publicar en GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Por favor contacte con el administrador de su página.<br />La URL de API provista no es válida.';
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'No pudimos contactar con la API de GNU Social con el Camino que introdujo.';
$a->strings['Save Settings'] = 'Guardar Ajustes';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Nota</strong>: Debido a sus ajustes de privacidad (<em>?Ocultar los detalles de su perfil a espectadores desconocidos?</em>) el enlace potencialmente incluído en publicaciones públicas transmitidas a GNU Social llevarán al visitante a una página en blanco informándole de que el acceso a su perfil ha sido restringido.';
$a->strings['Clear OAuth configuration'] = 'Limpiar la configuración de OAuth';
$a->strings['Cancel GNU Social Connection'] = 'Cancelar la conexión a GNU Social';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'Disponible globalmente GNU Social OAuthKeys';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = 'Hay pares de clave preconfigurados OAuth para algunos servidores disponibles de GNU Social. Si está utilizando uno de ellos, por favor utilice estas credenciales. Si no se siente libre de conectar a alguna otra instancia de GNU Social (vea abajo).';
$a->strings['Provide your own OAuth Credentials'] = 'Proveer sus propias credenciales de OAuth';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = 'Para conectarse a su cuenta GNU Social click en el botón de abajo para obtener un código de seguridad de GNU Social que puede copiar en la caja de abajo y enviar el formulario. Sólo sus entradas <strong>públicas</strong> se publicarán en GNU Social.';
$a->strings['Log in with GNU Social'] = 'Acceder a GNU Social';
$a->strings['Cancel Connection Process'] = 'Cancelar el Proceso de Conexión';
$a->strings['OAuth Consumer Key'] = 'Clave de Consumidor de OAuth';
$a->strings['OAuth Consumer Secret'] = 'Secreto de Consumidor de OAuth';
$a->strings['Base API Path (remember the trailing /)'] = 'Camino Base de API (Recordar la cola /)';
$a->strings['Copy the security code from GNU Social here'] = 'Copiar el código de seguridad de GNU Social aquí';
$a->strings['Allow posting to GNU Social'] = 'Permitir publicar en GNU Social';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Si está habilitado, todas sus publicaciones <strong>públicas</strong> pueden publicarse en la cuenta asociada de GNU Social. Puede elegir hacer eso por defecto (aquí) o por cada publicación por separado en las opciones de publicación mientras escribe la entrada.';
$a->strings['GNU Social Import/Export/Mirror'] = 'Importar/Exportar/Reflejar GNU Social';
$a->strings['Site name'] = 'Nombre de la página';
$a->strings['Consumer Secret'] = 'Secreto de Consumidor';
$a->strings['Consumer Key'] = 'Clave de Consumidor';
