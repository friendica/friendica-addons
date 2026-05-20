<?php

if(! function_exists("string_plural_select_da_DK")) {
function string_plural_select_da_DK($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Nyt medlem';
$a->strings['Tips for New Members'] = 'Tips til nye medlemmer';
$a->strings['Global Support Group'] = 'Global Hjælpegruppe';
$a->strings['Local Support Group'] = 'Lokal Hjælpegruppe';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Message'] = 'Besked';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Din besked til nye medlemmer. Du kan bruge BBCode her.';
$a->strings['Add a link to global support group'] = 'Tilføj et link til global hjælpegruppe';
$a->strings['Should a link to the global support group be displayed?'] = 'Skal et link til den globale hjælpegruppe vises?';
$a->strings['Add a link to the local support group'] = 'Tilføj et link til den lokale hjælpegruppe';
$a->strings['If you have a local support group and want to have a link displayed in the widget, check this box.'] = 'Hvis du har en lokal hjælpegruppe og gerne vil have et link vist i widgetten), tjek denne boks.';
$a->strings['Name of the local support group'] = 'Navn på det lokale supportforum';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Hvis du afkrydsede det ovenover, så definer et <em>kaldenavn</em> for den lokale supportgruppe her (fx hjælpere)';
