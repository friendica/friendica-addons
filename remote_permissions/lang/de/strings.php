<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Remote Permissions Settings'] = 'Entfernte Privatsphäreneinstellungen';
$a->strings['Allow recipients of your private posts to see the other recipients of the posts'] = 'Erlaube Empfängern von privaten Nachrichten zu sehen wer die anderen Empfänger sind';
$a->strings['Submit'] = 'Senden';
$a->strings['Remote Permissions settings updated.'] = 'Entfernte Privatsphäreneinstellungen aktualisiert.';
$a->strings['Visible to:'] = 'Sichtbar für:';
$a->strings['Visible to'] = 'Sichtbar für';
$a->strings['may only be a partial list'] = 'könnte nur ein Teil der Liste sein';
$a->strings['Global'] = 'Global';
$a->strings['The posts of every user on this server show the post recipients'] = 'Die Beiträge jedes Nutzers dieses Servers werden die Empfänger des Beitrags anzeigen';
$a->strings['Individual'] = 'Individuell';
$a->strings['Each user chooses whether his/her posts show the post recipients'] = 'Jede/r Nutzer/in kann wählen ob die Empfänger der Beiträge angezeigt werden sollen oder nicht';
$a->strings['Settings updated.'] = 'Einstellungen aktualisiert.';
