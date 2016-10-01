<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Permission denied.'] = 'Permesso negato.';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Client ID'] = 'Client ID';
$a->strings['Client Secret'] = 'Client Secret';
$a->strings['Error when registering buffer connection:'] = 'Errore registrando la connessione a buffer:';
$a->strings['You are now authenticated to buffer. '] = 'Sei autenticato su buffer.';
$a->strings['return to the connector page'] = 'ritorna alla pagina del connettore';
$a->strings['Post to Buffer'] = 'Invia a Buffer';
$a->strings['Buffer Export'] = 'Esporta Buffer';
$a->strings['Authenticate your Buffer connection'] = 'Autentica la tua connessione a Buffer';
$a->strings['Enable Buffer Post Plugin'] = 'Abilita il plugin di invio a Buffer';
$a->strings['Post to Buffer by default'] = 'Invia sempre a Buffer';
$a->strings['Check to delete this preset'] = 'Seleziona per eliminare questo preset';
$a->strings['Posts are going to all accounts that are enabled by default:'] = 'I messaggi andranno a tutti gli account che sono abilitati:';
