<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Deținător Site';
$a->strings['Email Address'] = 'Adresă de mail';
$a->strings['Postal Address'] = 'Adresă poștală';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'Modulul Impressum trebuie configurat! <br />Vă rugăm să adăugați cel puțin variabila <tt>deținător</tt> în fișierul de configurare. Pentru alte variabile vă rugăm să consultați fișierul README al modulului.';
$a->strings['Settings updated.'] = 'Configurări actualizate.';
$a->strings['Submit'] = 'Trimite';
$a->strings['The page operators name.'] = 'Pagina numelor operatorilor.';
$a->strings['Site Owners Profile'] = 'Profil Deținători Site ';
$a->strings['Profile address of the operator.'] = 'Adresa de profil a operatorului.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Cum să contactați operatorul prin corespondență lentă. Puteți folosi aici Codul BB.';
$a->strings['Notes'] = 'Note';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Notele suplimentare care sunt afișate sub informațiile de contact. Puteți folosi aici Codul BB.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'Modul de contactare al operatorului prin e-mail. (va fi afișat eclipsat)';
$a->strings['Footer note'] = 'Notă de Subsol';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Textul de pagina de subsol. Puteți folosi aici Codul BB.';
