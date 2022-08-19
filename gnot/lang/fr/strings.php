<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Allows threading of email comment notifications on Gmail and anonymising the subject line.'] = 'Permettre le filtrage des notifications de commentaires par courriel sur Gmail et l\'anonymisation de l\'objet.';
$a->strings['Enable this addon?'] = 'Activer cette application complémentaire ?';
$a->strings['Gnot Settings'] = 'Paramètres Gnot';
$a->strings['[Friendica:Notify] Comment to conversation #%d'] = '[Friendica:Notify] Commentaire vers conversation #%d';
