<?php

if (!function_exists('string_plural_select_nl')) {
    function string_plural_select_nl($n)
    {
        return $n != 1;
    }
}

$a->strings['Permission denied.'] = 'Toegang geweigerd';
$a->strings['You are now authenticated to app.net. '] = 'Je bent nu aangemeld bij app.net.';
$a->strings['<p>Error fetching token. Please try again.</p>'] = '<p>Fout tijdens token fetching. Probeer het nogmaals.</p>';
$a->strings['return to the connector page'] = 'ga terug naar de connector pagina';
$a->strings['Post to app.net'] = 'Post naar app.net.';
$a->strings['App.net Export'] = 'App.net Export';
$a->strings['Currently connected to: '] = 'Momenteel verbonden met:';
$a->strings['Enable App.net Post Plugin'] = 'App.net Post Plugin inschakelen';
$a->strings['Post to App.net by default'] = 'Naar App.net posten als standaard instellen';
$a->strings['Import the remote timeline'] = 'The tijdlijn op afstand importeren';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = '<p>Fout tijdens het ophalen van gebruikersprofiel. Leeg de configuratie en probeer het opnieuw.</p>';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Er zijn twee manieren om met App.net te verbinden.</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = '';
$a->strings["Use '%s' as Redirect URI<p>"] = '';
$a->strings['Client ID'] = '';
$a->strings['Client Secret'] = '';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = '';
$a->strings['Token'] = '';
$a->strings['Sign in using App.net'] = '';
$a->strings['Clear OAuth configuration'] = '';
$a->strings['Save Settings'] = '';
