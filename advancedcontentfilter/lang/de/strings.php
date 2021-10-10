<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Method not found'] = 'Methode nicht gefunden';
$a->strings['Filtered by rule: %s'] = 'Nach dieser Regel gefiltert: %s';
$a->strings['Advanced Content Filter'] = 'Erweiterter Inhaltsfilter';
$a->strings['Back to Addon Settings'] = 'Zurück zu den Addon Einstellungen';
$a->strings['Add a Rule'] = 'Eine Regel hinzufügen';
$a->strings['Help'] = 'Hilfe';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the help page.'] = 'Auf dieser Seite kannst du deine persönlichen Filterregeln verwalten. Regeln müssen einen Namen und einen frei wählbaren Ausdruck besitzen. Dieser Ausdruck wird mit den Daten der Beiträge abgeglichen und diese dann gegebenenfalls gefiltert. Für eine Übersicht der verfügbaren Operatoren für die Filter, wirf bitte einen Blick auf die Hilfsseite des Addons.';
$a->strings['Your rules'] = 'Deine Regeln';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'Du hast bisher noch keine Regeln definiert. Um eine neue Regel zu erstellen, verwende bitte den Button neben dem Titel.';
$a->strings['Disabled'] = 'Deaktiviert';
$a->strings['Enabled'] = 'Aktiv';
$a->strings['Disable this rule'] = 'Diese Regel deaktivieren';
$a->strings['Enable this rule'] = 'Diese Regel aktivieren';
$a->strings['Edit this rule'] = 'Diese Regel bearbeiten';
$a->strings['Edit the rule'] = 'Die Regel bearbeiten';
$a->strings['Save this rule'] = 'Regel speichern';
$a->strings['Delete this rule'] = 'Diese Regel löschen';
$a->strings['Rule'] = 'Regel';
$a->strings['Close'] = 'Schließen';
$a->strings['Add new rule'] = 'Neue Regel hinzufügen';
$a->strings['Rule Name'] = 'Name der Regel';
$a->strings['Rule Expression'] = 'Ausdruck der Regel';
$a->strings['Cancel'] = 'Abbrechen';
$a->strings['You must be logged in to use this method'] = 'Du musst angemeldet sein, um diese Methode verwenden zu können ';
$a->strings['Invalid form security token, please refresh the page.'] = 'Ungültiges Sciherheitstoken, bitte die Seite neu laden.';
$a->strings['The rule name and expression are required.'] = 'Der Name der Regel und der Ausdruck sind erforderlich.';
$a->strings['Rule successfully added'] = 'Regel erfolgreich hinzugefügt.';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'Entweder existiert die Regel nicht, oder sie gehört dir nicht.';
$a->strings['Rule successfully updated'] = 'Regel wurde erfolgreich aktualisiert.';
$a->strings['Rule successfully deleted'] = 'Regel erfolgreich gelöscht.';
$a->strings['Missing argument: guid.'] = 'Fehlendes Argument: guid.';
$a->strings['Unknown post with guid: %s'] = 'Unbekannter Beitrag mit der guid: %s';
