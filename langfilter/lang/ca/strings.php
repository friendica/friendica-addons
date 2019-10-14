<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Filtre d'Idioma";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Aquest complement tracta d’identificar les publicacions d’idioma en què s’escriuen. Si no coincideix amb cap idioma especificat a continuació, les publicacions s’ocultaran en col·lapsar-les.";
$a->strings["Use the language filter"] = "Emprar el filtre d'idioma";
$a->strings["Able to read"] = "Capacitat de llegir";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "llista d'abreviatures (ISO2 codes), separada per comes,  per idiomes que tú parles. Per exemple \"ca,es,de,it\".";
$a->strings["Minimum confidence in language detection"] = "Precissió mínima en la detecció d'idioma";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Precissió mínima en la detecció d'idioma per ser correcta, de 0 a 100. Els misssatges no seràn filtrats mentre que la precissió en la detecció d'idioma estigui per sota d'aquest valor.";
$a->strings["Minimum length of message body"] = "Durada mínima del cos del missatge";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Nombre mínim de caràcters en el cos de missatges per utilitzar el filtre. Les publicacions inferiors a aquesta no es filtraran. Nota: la detecció del llenguatge no és fiable per a contingut curt (<200 caràcters).";
$a->strings["Save Settings"] = "Desa la configuració";
$a->strings["Language Filter Settings saved."] = "S'ha desat la configuració del filtre d'idioma";
$a->strings["Filtered language: %s"] = "%sIdioma filtrat";
