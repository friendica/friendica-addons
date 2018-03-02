<?php

if(! function_exists("string_plural_select_zh_cn")) {
function string_plural_select_zh_cn($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Language Filter"] = "";
$a->strings["This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings."] = "";
$a->strings["Use the language filter"] = "";
$a->strings["I speak"] = "";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "";
$a->strings["Minimum confidence in language detection"] = "";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "";
$a->strings["Save Settings"] = "";
$a->strings["Language Filter Settings saved."] = "";
$a->strings["unspoken language %s - Click to open/close"] = "";
