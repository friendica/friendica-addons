<?php

if (!function_exists('string_plural_select_fr')) {
    function string_plural_select_fr($n)
    {
        return $n > 1;
    }
}

$a->strings['Impressum'] = 'Mentions légales';
$a->strings['Site Owner'] = 'Propriétaire du site';
$a->strings['Email Address'] = 'Adresse courriel';
$a->strings['Postal Address'] = 'Adresse postale';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'L’extension “Impressum” (Mentions légales) n’est pas configurée !<br />Veuillez renseigner au minimum la variable <tt>owner</tt> dans votre fichier de configuration. Pour les autres variables, reportez-vous au fichier README accompagnant l’extension.';
$a->strings['Settings updated.'] = 'Réglages mis à jour.';
$a->strings['Submit'] = 'Envoyer';
$a->strings['The page operators name.'] = "Le nom de l'administrateur de la page.";
$a->strings['Site Owners Profile'] = 'Profil du propriétaire';
$a->strings['Profile address of the operator.'] = 'L’adresse du profil de l’administrateur.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Comment contacter l’administrateur par courrier postal. Vous pouvez utiliser du BBCode.';
$a->strings['Notes'] = 'Notes';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Notes additionnelles à afficher sous les informations de contact. Vous pouvez utiliser du BBCode.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Comment contacter l’administrateur par courriel. (L’adresse sera modifiée à l’affichage pour brouiller les collecteurs d’adresses.)';
$a->strings['Footer note'] = 'Note de bas de page';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Texte du pied de page. Vous pouvez utiliser du BBCode.';
