<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['New Member'] = 'Nouveau Membre';
$a->strings['Tips for New Members'] = 'Conseils aux nouveaux venus';
$a->strings['Global Support Forum'] = 'Forum de support global';
$a->strings['Local Support Forum'] = 'Forum de support local';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['Message'] = 'Message';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Votre messages aux nouveaux venus. Vous pouvez utiliser des BBCodes.';
$a->strings['Add a link to global support forum'] = 'Ajouter un lien vers le forum de support global';
$a->strings['Should a link to the global support forum be displayed?'] = 'Montrer un lien vers le forum de support global?';
$a->strings['Add a link to the local support forum'] = 'Ajouter un lien vers le forum de support local';
$a->strings['If you have a local support forum and want to have a link displayed in the widget, check this box.'] = 'Si vous avez un forum d\'assistance local et désirez avoir un lien affiché dans l\'appliquette/widget, cochez cette case.';
$a->strings['Name of the local support group'] = 'Nom du groupe de support local';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Si vous avez coché la case ci-dessus, spécifiez le <em>nom d\'utilisateur</em> du groupe de support local (par ex. "helpers")';
