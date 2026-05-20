<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Sen sovelluksen nimi, josta haluat julkaisujesi näyttävän olevan tulossa. Erota eri sovellusten nimet pilkuin. Joka julkaisulla valitaan näistä satunnainen.';
$a->strings['Use this application name even if another application was used.'] = 'Käytä tämän sovelluksen nimeä, vaikka toista käytettäisiin.';
$a->strings['FromApp Settings'] = 'Sovelluksesta-asetukset';
