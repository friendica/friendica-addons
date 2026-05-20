<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Uusi jäsen';
$a->strings['Tips for New Members'] = 'Vinkkejä uusille käyttäjille';
$a->strings['Global Support Group'] = 'Yleinen tukiryhmä';
$a->strings['Local Support Group'] = 'Paikallinen tukiryhmä';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Message'] = 'Viesti';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Viestisi uusille jäsenille. Tässä voi käyttää bbcodea.';
$a->strings['Add a link to global support group'] = 'Lisää linkki yleiseen tukiryhmään';
$a->strings['Should a link to the global support group be displayed?'] = 'Tulisiko näyttää linkki yleiseen tukiryhmään?';
$a->strings['Add a link to the local support group'] = 'Lisää linkki paikalliseen tukiryhmään';
$a->strings['If you have a local support group and want to have a link displayed in the widget, check this box.'] = 'Jos on olemassa paikallinen tukiryhmä ja haluat sen linkin näkyvän sovelmassa, valitse tämä.';
$a->strings['Name of the local support group'] = 'Paikallisen tukifoorumin nimi';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Jos valitsit yllä olevan asetuksen, määritä paikallisen tukiryhmän <em>lempinimi</em> tähän (esim. ohjeita)';
