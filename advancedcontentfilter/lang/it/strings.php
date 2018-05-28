<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "Filtrato dalla regola: %s";
$a->strings["Advanced Content Filter"] = "Filtro Avanzato Contenuti";
$a->strings["Back to Addon Settings"] = "Torna alle impostazioni del componente aggiuntivo";
$a->strings["Add a Rule"] = "Aggiungi una regola";
$a->strings["Help"] = "Aiuto";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "Aggiungi e gestisci le tue regole personali di filtro contenuti in questa schermata. Le regole hanno un nome e un'espressione arbitraria che sarà confrontata con i dati del messaggio. Per un elenco completo delle operazioni disponibili, controlla la <a href=\"advancedcontentfilter/help\">pagina di aiuto</a>.";
$a->strings["Your rules"] = "Le tue regole";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "Non hai ancora nessuna regola! Aggiungine una cliccando sul bottone qui sopra a fianco al titolo.";
$a->strings["Disabled"] = "Disabilitato";
$a->strings["Enabled"] = "Abilitato";
$a->strings["Disable this rule"] = "Diabilita questa regola";
$a->strings["Enable this rule"] = "Abilita questa regola";
$a->strings["Edit this rule"] = "Modifica questa regola";
$a->strings["Edit the rule"] = "Modifica la regola";
$a->strings["Save this rule"] = "Salva questa regola";
$a->strings["Delete this rule"] = "Elimina questa regola";
$a->strings["Rule"] = "Regola";
$a->strings["Close"] = "Chiudi";
$a->strings["Add new rule"] = "Aggiungi nuova regola";
$a->strings["Rule Name"] = "Nome Regola";
$a->strings["Rule Expression"] = "Espressione Regola";
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "<p>Esempi:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>";
$a->strings["Cancel"] = "Annulla";
$a->strings["You must be logged in to use this method"] = "Devi essere autenticato per usare questo metodo";
$a->strings["Invalid form security token, please refresh the page."] = "Token di sicurezza invalido, aggiorna la pagina.";
$a->strings["The rule name and expression are required."] = "Il nome e l'espressione della regola sono richiesti.";
$a->strings["Rule successfully added"] = "Regola aggiunta con successo";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "La regola non esiste o non ti appartiene.";
$a->strings["Rule successfully updated"] = "Regola aggiornata con successo";
$a->strings["Rule successfully deleted"] = "Regola eliminata con successo";
$a->strings["Missing argument: guid."] = "Argomento mancante: guid.";
$a->strings["Unknown post with guid: %s"] = "Messaggio con guid %s sconosciuto";
$a->strings["Method not found"] = "Metodo non trovato";
