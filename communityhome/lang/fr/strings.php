<?php

if (!function_exists('string_plural_select_fr')) {
    function string_plural_select_fr($n)
    {
        return $n > 1;
    }
}

$a->strings['Login'] = 'Identifiant';
$a->strings['OpenID'] = 'OpenID';
$a->strings['Latest users'] = 'Derniers utilisateurs';
$a->strings['Most active users'] = 'Utilisateurs les plus actifs';
$a->strings['Latest photos'] = 'Dernières photos';
$a->strings['Contact Photos'] = 'Photos du contact';
$a->strings['Profile Photos'] = 'Photos de profil';
$a->strings['Latest likes'] = 'Derniers likes';
$a->strings['event'] = 'événement';
$a->strings['status'] = 'statut';
$a->strings['photo'] = 'photo';
$a->strings["%1\$s likes %2\$s's %3\$s"] = '%1$s aime %3$s de %2$s';
$a->strings['Welcome to %s'] = 'Bienvenue sur %s';
