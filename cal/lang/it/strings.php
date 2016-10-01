<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Event Export'] = 'Esporta Evento';
$a->strings['You can download public events from: '] = 'Puoi scaricare gli eventi publici da:';
$a->strings['The user does not export the calendar.'] = "L'utente non esporta il calendario.";
$a->strings['This calendar format is not supported'] = 'Il formato del calendario non è supportato';
$a->strings['Export Events'] = 'Esporta Eventi';
$a->strings['If this is enabled, your public events will be available at'] = 'Se abilitato, i tuoi eventi pubblici saranno disponibili a';
$a->strings['Currently supported formats are ical and csv.'] = 'I formati supportati sono ical e csv.';
$a->strings['Enable calendar export'] = 'Abilita esporazione calendario';
$a->strings['Save Settings'] = 'Salva Impostazioni';
