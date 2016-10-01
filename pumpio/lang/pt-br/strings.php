<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Permission denied.'] = 'Permissão negada.';
$a->strings["Unable to register the client at the pump.io server '%s'."] = '';
$a->strings['You are now authenticated to pumpio.'] = 'Você se autenticou no Pump.io.';
$a->strings['return to the connector page'] = 'voltar à página de conectores';
$a->strings['Post to pumpio'] = 'Publicar no Pump.io';
$a->strings['Pump.io Import/Export/Mirror'] = '';
$a->strings['pump.io username (without the servername)'] = 'Nome de usuário no pump.io (sem o nome do servidor)';
$a->strings['pump.io servername (without "http://" or "https://" )'] = '';
$a->strings['Authenticate your pump.io connection'] = '';
$a->strings['Import the remote timeline'] = 'Importar a linha do tempo remota';
$a->strings['Enable pump.io Post Plugin'] = 'Habilitar plug-in para publicar no Pump.io';
$a->strings['Post to pump.io by default'] = 'Publicar no Pump.io por padrão';
$a->strings['Should posts be public?'] = '';
$a->strings['Mirror all public posts'] = '';
$a->strings['Check to delete this preset'] = '';
$a->strings['Save Settings'] = 'Salvar Configurações';
$a->strings['Pump.io post failed. Queued for retry.'] = 'Falha ao publicar no Pump.io. Na fila para tentar novamente.';
$a->strings['Pump.io like failed. Queued for retry.'] = 'Falha ao curtir no Pump.io. Na fila para tentar novamente.';
$a->strings['status'] = '';
$a->strings["%1\$s likes %2\$s's %3\$s"] = '%1$s curtiu o %3$s de %2$s';
