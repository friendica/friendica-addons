<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Filtered by rule: %s'] = 'Gefilterd volgens regel: %s';
$a->strings['Advanced Content Filter'] = 'Geavanceerd filter voor berichtsinhoud';
$a->strings['Back to Addon Settings'] = 'Terug naar Addon instellingen';
$a->strings['Add a Rule'] = 'Filterregel toevoegen';
$a->strings['Help'] = 'Help';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href="advancedcontentfilter/help">help page</a>.'] = 'Beheer de filterregels van je persoonlijke filter voor berichtsinhoud in dit scherm. Regels hebben een naam en bewoording welke we automatisch controleren door te vergelijken met de inhoud van elk bericht. Voor een compleet naslagwerk van de beschikbare bewerkingen en variabelen, zie de  <a href="advancedcontentfilter/help">help pagina</a>.';
$a->strings['Your rules'] = 'Jouw regels';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'Je hebt nog geen regels! Klik op de knop bovenin naast de titel om een regel toe te voegen.';
$a->strings['Disabled'] = 'Uitgeschakeld';
$a->strings['Enabled'] = 'Geactiveerd';
$a->strings['Disable this rule'] = 'Deze regel uitschakelen';
$a->strings['Enable this rule'] = 'Deze regel inschakelen';
$a->strings['Edit this rule'] = 'Regel bewerken';
$a->strings['Edit the rule'] = 'Regel bewerken';
$a->strings['Save this rule'] = 'Deze regel opslaan';
$a->strings['Delete this rule'] = 'Deze regel verwijderen';
$a->strings['Rule'] = 'Regel';
$a->strings['Close'] = 'Sluiten';
$a->strings['Add new rule'] = 'Voeg nieuwe regel toe';
$a->strings['Rule Name'] = 'Regel naam';
$a->strings['Rule Expression'] = 'Regel bewoording';
$a->strings['<p>Examples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>'] = '<p>Voorbeelden:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>label</li></ul>';
$a->strings['Cancel'] = 'Annuleren';
$a->strings['You must be logged in to use this method'] = 'Je moet ingelogd zijn om deze methode te gebruiken';
$a->strings['Invalid form security token, please refresh the page.'] = 'Ongeldige formulier beveiligings token, vernieuw de pagina a.u.b.';
$a->strings['The rule name and expression are required.'] = 'De regelnaam en bewoording zijn vereist.';
$a->strings['Rule successfully added'] = 'Regel succesvol toegevoegd';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'Deze regel bestaat niet, of is niet van jou.';
$a->strings['Rule successfully updated'] = 'Regel succesvol opgeslagen';
$a->strings['Rule successfully deleted'] = 'Regel succesvol verwijderd';
$a->strings['Missing argument: guid.'] = 'Parameter guid niet aanwezig';
$a->strings['Unknown post with guid: %s'] = 'Onbekend bericht met guid: %s';
$a->strings['Method not found'] = 'Methode niet gevonden';
