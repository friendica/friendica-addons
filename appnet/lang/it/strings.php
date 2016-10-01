<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Permission denied.'] = 'Permesso negato.';
$a->strings['You are now authenticated to app.net. '] = 'Sei autenticato su app.net';
$a->strings['<p>Error fetching token. Please try again.</p>'] = '<p>Errore recuperando il token. Prova di nuovo</p>';
$a->strings['return to the connector page'] = 'ritorna alla pagina del connettore';
$a->strings['Post to app.net'] = 'Invia ad app.net';
$a->strings['App.net Export'] = 'Esporta App.net';
$a->strings['Currently connected to: '] = 'Al momento connesso con:';
$a->strings['Enable App.net Post Plugin'] = 'Abilita il plugin di invio ad App.net';
$a->strings['Post to App.net by default'] = 'Invia sempre ad App.net';
$a->strings['Import the remote timeline'] = 'Importa la timeline remota';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = '<p>Errore recuperando il profilo utente. Svuota la configurazione e prova di nuovo.</p>';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Puoi collegarti ad App.net in due modi.</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = "<p>Registrare un'applicazione su <a href=\"https://account.app.net/developer/apps/\">https://account.app.net/developer/apps/</a> e inserire Client ID e Client Secret.";
$a->strings["Use '%s' as Redirect URI<p>"] = "Usa '%s' come Redirect URI</p>";
$a->strings['Client ID'] = 'Client ID';
$a->strings['Client Secret'] = 'Client Secret';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '<p>Oppure puoi recuperare un token su <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>.';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Imposta gli ambiti 'Basic', 'Stream', 'Scrivi Post', 'Messaggi Pubblici', 'Messaggi'.</p>";
$a->strings['Token'] = 'Token';
$a->strings['Sign in using App.net'] = 'Autenticati con App.net';
$a->strings['Clear OAuth configuration'] = 'Pulisci configurazione OAuth';
$a->strings['Save Settings'] = 'Salva Impostazioni';
