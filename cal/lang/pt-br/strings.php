<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Event Export'] = 'Exportar Evento';
$a->strings['You can download public events from: '] = 'Você pode baixar eventos públicos de:';
$a->strings['The user does not export the calendar.'] = 'O usuário não exportou o calendário.';
$a->strings['This calendar format is not supported'] = 'Esse formato de calendário não é suportado.';
$a->strings['Export Events'] = 'Exporta Eventos';
$a->strings['If this is enabled, your public events will be available at'] = 'Se isso estiver habiltiado, seus eventos públicos estarão disponíveis';
$a->strings['Currently supported formats are ical and csv.'] = 'Os formatos disponíveis atualmente são ical e csv.';
$a->strings['Enable calendar export'] = 'Habilite exportar calendário';
$a->strings['Save Settings'] = 'Salvar as Configurações';
