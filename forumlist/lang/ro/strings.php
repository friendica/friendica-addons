<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Forums'] = 'Forumuri';
$a->strings['show/hide'] = 'afișare/ascundere';
$a->strings['No forum subscriptions'] = 'Nu există subscrieri pe forum';
$a->strings['Forums:'] = 'Forumuri:';
$a->strings['Forumlist settings updated.'] = 'Configurările Forumlist au fost actualizate.';
$a->strings['Forumlist Settings'] = 'Configurări Forumlist ';
$a->strings['Randomise forum list'] = 'Randomizare listă forum';
$a->strings['Show forums on profile page'] = 'Afișare forumuri pe pagina de profil';
$a->strings['Show forums on network page'] = 'Afișare forumuri pe pagina de rețea';
$a->strings['Submit'] = 'Trimite';
