<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Permission denied.'] = 'Permisiune refuzată.';
$a->strings['You are now authenticated to app.net. '] = 'Acum sunteți autentificat pe App.net.';
$a->strings['<p>Error fetching token. Please try again.</p>'] = '<p>Eroare la procesarea token-ului. Vă rugăm să reîncercați.</p>';
$a->strings['return to the connector page'] = 'revenire la pagina de conectare';
$a->strings['Post to app.net'] = 'Postați pe App.net';
$a->strings['App.net Export'] = 'Exportare pe App.net';
$a->strings['Currently connected to: '] = 'Conectat curent la:';
$a->strings['Enable App.net Post Plugin'] = 'Activare Modul Postare pe App.net';
$a->strings['Post to App.net by default'] = 'Postați implicit pe App.net';
$a->strings['Import the remote timeline'] = 'Importare cronologie la distanță';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = '<p>Eroare la procesarea profilului de utilizator. Vă rugăm să ștergeți configurarea şi apoi reîncercați.</p>';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Aveți două modalități de a vă conecta la App.net.</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = '<p>Prima modalitate: Înregistrați o cerere pe <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> şi introduceți ID Client şi Cheia Secretă Client.';
$a->strings["Use '%s' as Redirect URI<p>"] = "Utilizați '%s' ca URI de Redirecţionare<p>";
$a->strings['Client ID'] = 'ID Client';
$a->strings['Client Secret'] = 'Cheia Secretă Client';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '<p>A doua cale: autorizați un indicativ de acces token de pe <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a> .';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Stabiliți aceste scopuri: 'De Bază', 'Flux', 'Scriere Postare', 'Mesaje Publice', 'Mesaje'.</p>";
$a->strings['Token'] = 'Token';
$a->strings['Sign in using App.net'] = 'Autentificați-vă utilizând App.net';
$a->strings['Clear OAuth configuration'] = 'Ștergeți configurările OAuth';
$a->strings['Save Settings'] = 'Salvare Configurări';
