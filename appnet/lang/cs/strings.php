<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Permission denied.'] = 'Přístup odmítnut.';
$a->strings['You are now authenticated to app.net. '] = 'Nyní jste přihlášen k app.net.';
$a->strings['<p>Error fetching token. Please try again.</p>'] = '<p>Chyba v přenesení tokenu. Prosím zkuste to znovu.</p>';
$a->strings['return to the connector page'] = 'návrat ke stránce konektor';
$a->strings['Post to app.net'] = 'Poslat příspěvek na app.net';
$a->strings['App.net Export'] = 'App.net Export';
$a->strings['Currently connected to: '] = 'V současné době připojen k:';
$a->strings['Enable App.net Post Plugin'] = 'Aktivovat App.net Post Plugin';
$a->strings['Post to App.net by default'] = 'Defaultně poslat na App.net';
$a->strings['Import the remote timeline'] = 'Importovat vzdálenou časovou osu';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = '<p>Chyba v přenesení uživatelského profilu. Prosím zkuste smazat konfiguraci a zkusit to znovu.</p>';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Máte nyní dvě možnosti jak se připojit k App.net.</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = '<p>První možnost: Registrovat svou žádost na <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> a zadat Client ID and Client Secret. ';
$a->strings["Use '%s' as Redirect URI<p>"] = "Použít '%s' jako URI pro přesměrování<p>";
$a->strings['Client ID'] = 'Client ID';
$a->strings['Client Secret'] = 'Client Secret';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '<p>Druhá možnost: vložit token do <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. ';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Nastavte tyto rámce: 'Základní', 'Stream', 'Psaní příspěvků, 'Veřejné zprávy', 'Zprávy'.</p>";
$a->strings['Token'] = 'Token';
$a->strings['Sign in using App.net'] = 'Přihlásit se s použitím App.net';
$a->strings['Clear OAuth configuration'] = 'Vymazat konfiguraci OAuth';
$a->strings['Save Settings'] = 'Uložit Nastavení';
