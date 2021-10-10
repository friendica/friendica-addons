<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Filtered by rule: %s'] = 'Filtrat per regla: %s';
$a->strings['Advanced Content Filter'] = 'Contingut avançat Filtre';
$a->strings['Back to Addon Settings'] = 'Torna Addon Configuració';
$a->strings['Add a Rule'] = 'Afegiu una regla';
$a->strings['Help'] = 'Ajuda';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href="advancedcontentfilter/help">help page</a>.'] = 'Afegiu i gestioneu les vostres regles de filtre de contingut personal en aquesta pantalla. Les regles tenen un nom i una expressió arbitrària que es combinen amb les dades de la publicació. Per obtenir una referència completa de les variables i operacions disponibles, comproveu el botó <a href="advancedcontentfilter/help">pàgina d’ajuda</a>.';
$a->strings['Your rules'] = 'Les seves normes';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'Encara no teniu normes. Comenceu a afegir-ne un fent clic al botó situat al costat del títol.';
$a->strings['Disabled'] = 'Desactivat';
$a->strings['Enabled'] = 'Permetre';
$a->strings['Disable this rule'] = 'Desactiva aquesta regla';
$a->strings['Enable this rule'] = 'Activa aquesta regla';
$a->strings['Edit this rule'] = 'Edita aquesta regla';
$a->strings['Edit the rule'] = 'Edita la regla';
$a->strings['Save this rule'] = 'Deseu aquesta regla';
$a->strings['Delete this rule'] = 'Suprimeix aquesta regla';
$a->strings['Rule'] = 'Regla';
$a->strings['Close'] = 'Tancar';
$a->strings['Add new rule'] = 'Add nova regla';
$a->strings['Rule Name'] = 'Nom de la regla';
$a->strings['Rule Expression'] = 'Expressió de regla';
$a->strings['<p>Examples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>'] = '<p>Exemples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>';
$a->strings['Cancel'] = 'cancel·lar';
$a->strings['You must be logged in to use this method'] = 'Per utilitzar aquest mètode, heu d’iniciar sessió';
$a->strings['Invalid form security token, please refresh the page.'] = 'El testimoni de seguretat del formulari no és vàlid. Actualitza la pàgina';
$a->strings['The rule name and expression are required.'] = 'El nom i l’expressió de la regla són obligatoris';
$a->strings['Rule successfully added'] = 'La regla s\'ha afegit correctament';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'La regla no existeix o no us pertany.';
$a->strings['Rule successfully updated'] = 'La regla s\'ha actualitzat correctament';
$a->strings['Rule successfully deleted'] = 'S\'ha suprimit la regla correctament';
$a->strings['Missing argument: guid.'] = 'Falta un argument: guia';
$a->strings['Unknown post with guid: %s'] = 'Publicació desconeguda amb guia: %s';
$a->strings['Method not found'] = 'Mètode no trobat';
