<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Taalfilter";
$a->strings["This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings."] = "Deze addon probeert de taal van berichten automatisch te bepalen. Als het niet overeenkomt met een taal die jij spreekt (zie verder) zal het bericht worden verborgen. Onthoudt hierbij wel dat taaldetectie niet perfect is, vooral bij korte berichten.";
$a->strings["Use the language filter"] = "Gebruik de taalfilter";
$a->strings["I speak"] = "Ik spreek";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lijst van afkortingen (ISO2 codes) voor talen die jij spreekt, door komma's gescheiden. Bijvoorbeeld \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Language Filter Settings saved."] = "Taalfilter-instellingen bijgewerkt.";
$a->strings["unspoken language %s - Click to open/close"] = "niet gesproken taal %s - Klik om open/dicht te klappen";
