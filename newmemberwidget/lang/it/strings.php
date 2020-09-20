<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["New Member"] = "Nuovi Utenti";
$a->strings["Tips for New Members"] = "Consigli per i Nuovi Utenti";
$a->strings["Global Support Forum"] = "Forum Globale di Supporto";
$a->strings["Local Support Forum"] = "Forum Locale di Supporto";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Message"] = "Messaggio";
$a->strings["Your message for new members. You can use bbcode here."] = "Il tuo messaggio per i nuovi utenti. Puoi usare BBCode";
$a->strings["Add a link to global support forum"] = "Aggiunge un collegamento al forum di supporto globale";
$a->strings["Should a link to the global support forum be displayed?"] = "Mostrare il collegamento al forum di supporto globale?";
$a->strings["Add a link to the local support forum"] = "Aggiunge un collegamento al forum di supporto locale";
$a->strings["If you have a local support forum and wand to have a link displayed in the widget, check this box."] = "Se hai un forum di supporto locale e vuoi che sia mostrato il collegamento nel widget, seleziona questo box.";
$a->strings["Name of the local support group"] = "Nome del gruppo locale di supporto";
$a->strings["If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)"] = "Se hai selezionato il box sopra, specifica qui il <em>nome utente</em> del gruppo locale di supporto (e.s. 'supporto')";
