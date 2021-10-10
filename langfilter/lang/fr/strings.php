<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Language Filter'] = 'Filtre de langues';
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Cette extension essaie de reconnaître la langue dans laquelle les publications sont écrites. Si elle ne correspond à aucune de la liste donnée plus bas, les publications seront réduites.';
$a->strings['Use the language filter'] = 'Utiliser le filtre de langues';
$a->strings['Able to read'] = 'Peut lire';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Liste des abréviations (codes ISO 639-1) des langues que vous parlez, séparées par des virgules.
Par exemple "de,it".';
$a->strings['Minimum confidence in language detection'] = 'Confiance minimale dans la détection de langues';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Seuil de confiance minimal pour la détection des langues, de 0 à 100. Une publication ne sera pas filtrée si elle est détectée avec une confiance moindre.';
$a->strings['Minimum length of message body'] = 'Longueur minimale du corps de message.';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Nombre minimal de signes dans le corps de message pour déclencher le filtre. Une publication plus courte ne sera pas filtrée. Remarque: la détection de langue n\'est pas fiable pour du contenu court (<200 signes).';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Filtered language: %s'] = 'Langues filtrées: %s';
