<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Permission denied.'] = 'Permissão negada.';
$a->strings['You are now authenticated to app.net. '] = 'Você está autenticado no app.net.';
$a->strings['<p>Error fetching token. Please try again.</p>'] = 'Erro ocorrido na obtenção do token. Tente novamente.';
$a->strings['return to the connector page'] = 'Volte a página de conectores.';
$a->strings['Post to app.net'] = 'Publicar no App.net';
$a->strings['App.net Export'] = 'App.net exportar';
$a->strings['Currently connected to: '] = 'Atualmente conectado em: ';
$a->strings['Enable App.net Post Plugin'] = 'Habilitar plug-in para publicar no App.net';
$a->strings['Post to App.net by default'] = 'Publicar em App.net por padrão';
$a->strings['Import the remote timeline'] = 'Importar a linha do tempo remota';
$a->strings['<p>Error fetching user profile. Please clear the configuration and try again.</p>'] = 'Erro na obtenção do perfil do usuário. Confira as configurações e tente novamente.';
$a->strings['<p>You have two ways to connect to App.net.</p>'] = '<p>Você possui duas formas de conectar ao App.net</p>';
$a->strings['<p>First way: Register an application at <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> and enter Client ID and Client Secret. '] = '<p>1º Método: Registre uma aplicação em <a href="https://account.app.net/developer/apps/">https://account.app.net/developer/apps/</a> e entre o Client ID e Client Secret';
$a->strings["Use '%s' as Redirect URI<p>"] = "Use '%s' como URI redirecionador<p>";
$a->strings['Client ID'] = 'Client ID';
$a->strings['Client Secret'] = 'Client Secret';
$a->strings['<p>Second way: fetch a token at <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. '] = '<p>2º Método: obtenha um token em <a href="http://dev-lite.jonathonduerig.com/">http://dev-lite.jonathonduerig.com/</a>. ';
$a->strings["Set these scopes: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>"] = "Adicione valor as estas saídas: 'Basic', 'Stream', 'Write Post', 'Public Messages', 'Messages'.</p>";
$a->strings['Token'] = 'Token';
$a->strings['Sign in using App.net'] = 'Entre usando o App.net';
$a->strings['Clear OAuth configuration'] = 'Limpar configuração OAuth';
$a->strings['Save Settings'] = 'Salvar Configurações';
