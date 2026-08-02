<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Nyt medlem';
$a->strings['Tips for New Members'] = 'Tips til nye medlemmer';
$a->strings['Global Support Forum'] = 'Globalt supportforum';
$a->strings['Local Support Forum'] = 'Lokalt supportforum';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Message'] = 'Besked';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Din besked til nye medlemmer. Du kan bruge BBCode her.';
$a->strings['Add a link to global support forum'] = 'Tilføj et link til det globale supportforum';
$a->strings['Should a link to the global support forum be displayed?'] = 'Skal et link til det globale supportforum vises?';
$a->strings['Add a link to the local support forum'] = 'Tilføj et link til det lokale supportforum';
$a->strings['If you have a local support forum and want to have a link displayed in the widget, check this box.'] = 'Hvis du har et lokalt supportforum og gerne vil have et link vist i widgeten, så afkryds denne boks.';
$a->strings['Name of the local support group'] = 'Navn på det lokale supportforum';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Hvis du afkrydsede det ovenover, så definer et <em>kaldenavn</em> for den lokale supportgruppe her (fx hjælpere)';
