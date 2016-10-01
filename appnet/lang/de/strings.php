<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Permission denied.'] = 'Zugriff verweigert.';
$a->strings['You are now authenticated to app.net. '] = 'Du bist nun auf app.net authentifiziert.';
$a->strings['<p>Error fetching token. Please try again.</p>'] = '<p>Fehler beim Holen des Tokens, bitte versuche es später noch einmal.</p>';
$a->strings['return to the connector page'] = 'zurück zur Connector Seite';
$a->strings['Post to app.net'] = 'Nach app.net senden';
$a->strings['App.net Export'] = 'App.net Export';
$a->strings['Currently connected to: '] = 'Momentan verbunden mit: ';
$a->strings['Enable App.net Post Plugin'] = 'Veröffentlichungen bei App.net erlauben';
$a->strings['Post to App.net by default'] = 'Standardmäßig bei App.net veröffentlichen';
$a->strings['Import the remote timeline'] = 'Importiere die entfernte Zeitleiste';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = '<p>Beim Laden des Nutzerprofils ist ein Fehler aufgetreten. Bitte versuche es später noch einmal.</p>';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Du hast zwei Wege deinen friendica Account mit App.net zu verbinden.</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = '<p>Erster Weg: Registriere eine Anwendung unter <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> und wähle eine Client ID und ein Client Secret.';
$a->strings["Use '%s' as Redirect URI<p>"] = "Verwende '%s' als Redirect URI<p>";
$a->strings['Client ID'] = 'Client ID';
$a->strings['Client Secret'] = 'Client Secret';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '<p>Zweiter Weg: Beantrage ein Token unter <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. ';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Verwende folgende Scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>";
$a->strings['Token'] = 'Token';
$a->strings['Sign in using App.net'] = 'Per App.net anmelden';
$a->strings['Clear OAuth configuration'] = 'OAuth Konfiguration löschen';
$a->strings['Save Settings'] = 'Einstellungen speichern';
