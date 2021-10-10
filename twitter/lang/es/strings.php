<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Entrada para Twitter';
$a->strings['You submitted an empty PIN, please Sign In with Twitter again to get a new one.'] = 'Envió un PIN vacío, inicie sesión con Twitter nuevamente para obtener uno nuevo.';
$a->strings['Twitter Import/Export/Mirror'] = 'Importación / Exportación / Espejo de Twitter';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'No se ha encontrado ningún par de claves de consumidor para Twitter. Por favor, póngase en contacto con el administrador de su sitio. ';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'En esta instancia de Friendica, se habilitó el complemento de Twitter, pero aún no ha conectado su cuenta a su cuenta de Twitter. Para hacerlo, haga clic en el botón de abajo para obtener un PIN de Twitter que debe copiar en el cuadro de entrada a continuación y enviar el formulario. Solo sus publicaciones de <strong> public </strong> se publicarán en Twitter.';
$a->strings['Log in with Twitter'] = 'Iniciar sesión con Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Copie el PIN de Twitter aquí';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['An error occured: '] = 'Ocurrió un error:';
$a->strings['Currently connected to: '] = 'Moneda conectada a:';
$a->strings['Disconnect'] = 'Desconectar';
$a->strings['Allow posting to Twitter'] = 'Permitir publicar en Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Si habilita todas sus publicaciones <strong>públicas</strong> pueden ser publicadas en la cuenta de Twitter asociada. Puede elegir hacer eso por defecto (aquí) o por cada publicación por separado en las opciones de entrada cuando escriba la entrada.';
$a->strings['<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Nota</strong>: Debido a tu privacidad (<em>Ocultar tu perfil de desconocidos?</em>) el enlace potencialmente incluido en publicaciones públicas retransmitidas a Twitter llevará al visitante a una página en blanco que le informa que el acceso a su perfil ha sido restringido.';
$a->strings['Send public postings to Twitter by default'] = 'Enviar publicaciones públicas a Twitter por defecto';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Refleje todas las publicaciones de Twitter que no sean respuestas';
$a->strings['Import the remote timeline'] = 'Importar la línea de tiempo remota';
$a->strings['Automatically create contacts'] = 'Crea contactos automáticamente';
$a->strings['This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again.'] = 'Esto creará automáticamente un contacto en Friendica tan pronto como reciba un mensaje de un contacto existente a través de la red de Twitter. Si no habilita esto, debe agregar manualmente los contactos de Twitter en Friendica de los que le gustaría ver las publicaciones aquí. Sin embargo, si está habilitado, no puede simplemente eliminar un contacto de Twitter de la lista de contactos de Friendica, ya que volverá a crear este contacto cuando vuelva a publicar.';
$a->strings['Consumer key'] = 'Clave de consumidor';
$a->strings['Consumer secret'] = 'Secreto de consumidor';
$a->strings['%s on Twitter'] = '%s en Twitter';
