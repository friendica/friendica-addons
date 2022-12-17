<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'] = 'Créez un compte sur <a href="http://www.ifttt.com">IFTTT</a>. Créer trois \'recipe\' Facebook qui sont connectées avec <a href="https://ifttt.com/maker">Maker</a> (Sous la forme "if Facebook then Maker") avec les paramètres suivants:';
$a->strings['URL'] = 'URL';
$a->strings['Method'] = 'Méthode';
$a->strings['Content Type'] = 'Type de contenu';
$a->strings['Body for "new status message"'] = 'Corps du "nouveau message de statut"';
$a->strings['Body for "new photo upload"'] = 'Corps du "nouveau téléversement de photo"';
$a->strings['Body for "new link post"'] = 'Corps du "nouveau lien de publication"';
$a->strings['IFTTT Mirror'] = 'Mirroir IFTTT';
$a->strings['Generate new key'] = 'Générer une nouvelle clé';
