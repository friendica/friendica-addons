<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'A data final é anterior à data inicial do blecaute, por favor, corrija isso.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Por favor, confira as configurações atuais para o blecaute. Ele irá iniciar em<strong>%s</strong> e terminar em <strong>%s</strong>.';
$a->strings['Save Settings'] = 'Salvar configurações';
$a->strings['Redirect URL'] = 'URL de redirecionamento';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Todos os internautas visitantes serão redirecionados para essa URL';
$a->strings['Begin of the Blackout'] = 'Início do Blecaute';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'O formato é <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> ano <em>MM</em> mês, <em>DD</em> dia, <em>hh</em> hora e <em>mm</em> minuto.';
$a->strings['End of the Blackout'] = 'Término do Blecaute';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Nota</strong>: O redirecionamento estará ativo desde o momento em que você pressionar o botão de submissão. Usuários logados <strong>não</strong> serão desconectados mas não conseguirão fazer login novamente após sair da conta enquanto o bloqueio ainda estiver em vigor.';
