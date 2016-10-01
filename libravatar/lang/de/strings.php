<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3'] = 'Kann Libravatar NICHT erfolgreich installieren.<br>PHP >=5.3 wird benötigt';
$a->strings['generic profile image'] = 'allgemeines Profilbild';
$a->strings['random geometric pattern'] = 'zufällig erzeugtes geometrisches Muster';
$a->strings['monster face'] = 'Monstergesicht';
$a->strings['computer generated face'] = 'Computergesicht';
$a->strings['retro arcade style face'] = 'Retro Arcade Design Gesicht';
$a->strings['Warning'] = 'Warnung';
$a->strings['Your PHP version %s is lower than the required PHP >= 5.3.'] = 'Deine PHP Version %s ist niedriger als die benötigte Version PHP >= 5.3.';
$a->strings['This addon is not functional on your server.'] = 'Dieses Addon funktioniert auf deinem Server nicht.';
$a->strings['Information'] = 'Information';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Das Gravatar Addon ist installiert. Bitte schalte das Gravatar Addon aus.<br>Das Libravatar Addon nutzt Gravater, sollte nichts auf Libravatar gefunden werden.';
$a->strings['Submit'] = 'Senden';
$a->strings['Default avatar image'] = 'Standard Profilbild ';
$a->strings['Select default avatar image if none was found. See README'] = 'Das Standard Avatar Bild wurde nicht gefunden. Siehe README';
$a->strings['Libravatar settings updated.'] = 'Libravatar Einstellungen sind aktualisiert.';
