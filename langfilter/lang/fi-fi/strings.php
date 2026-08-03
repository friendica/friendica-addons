<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Tämä lisäosa yrittää tunnistaa millä kielellä julkaisut on kirjoitettu. Jos julkaisut eivät vastaa mitään alla määritellyistä kielistä, ne piilotetaan kokoontaittamalla.';
$a->strings['Use the language filter'] = 'Ota kielisuodatin käyttöön';
$a->strings['Able to read'] = 'Pystyy lukemaan';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Lista puhumiesi kielten lyhenteistä (ISO 639-1-koodit) pilkulla eroteltuna. Esimerkiksi "de,it".';
$a->strings['Minimum confidence in language detection'] = 'Kielentunnistuksen vähimmäisvarmuus';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Prosenttiarvo välillä 0–100 kielentunnistuksen oikeellisuuden vähimmäisvarmuudelle. Julkaisuja ei suodateta, kun kielentunnistuksen varmuus on alle tämän prosenttiarvon.';
$a->strings['Minimum length of message body'] = 'Viestin sisällön vähimmäispituus';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Viestin sisältämien merkkien vähimmäismäärä, johon perustuen suodatinta käytetään. Viestejä, jotka ovat lyhyempiä kuin tämä arvo, ei suodateta. Huomaa: Kielentunnistus toimii epäluotettavasti lyhyissä sisällöissä (< 200 merkkiä).';
$a->strings['Language Filter'] = 'Kielisuodatin';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Filtered language: %s'] = 'Suodatettu kieli: %s';
