<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['From Address'] = 'Depuis l\'adresse';
$a->strings['Email address that stream items will appear to be from.'] = 'Adresse de courriel de laquelle les éléments du flux sembleront provenir.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Re:'] = 'Re :';
$a->strings['Friendica post'] = 'Message Friendica';
$a->strings['Diaspora post'] = 'Message Diaspora';
$a->strings['Feed item'] = 'Élément du flux';
$a->strings['Email'] = 'Courriel';
$a->strings['Friendica Item'] = 'Élément de Friendica';
$a->strings['Upstream'] = 'En amont';
$a->strings['Local'] = 'Local';
$a->strings['Enabled'] = 'Activer';
$a->strings['Email Address'] = 'Adresse de courriel';
$a->strings['Leave blank to use your account email address'] = 'Laissez vide pour utiliser l\'adresse de courriel de votre compte';
$a->strings['Exclude Likes'] = 'Exclure les "j\'aime"';
$a->strings['Check this to omit mailing "Like" notifications'] = 'Cochez ceci pour éviter d\'envoyer les notifications des "J\'aime"';
$a->strings['Attach Images'] = 'Attacher les images';
$a->strings['Download images in posts and attach them to the email.  Useful for reading email while offline.'] = 'Télécharger les images des messages et les attacher au courriel. Utile pour les les courriels hors-ligne.';
$a->strings['Mail Stream Settings'] = 'Paramètres de Mail Stream';
