<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'La data di fine è precedente alla data di inizio blackout, dovresti sistemarle.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Per favore ricontrolla le impostazioni attuali per il blackout. L\'inizio sarà il <strong>%s</strong> e terminerà il <strong>%s</strong>.';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Redirect URL'] = 'URL di reindirizzamento';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Tutti i tuoi visitatori dal web verranno reindirizzati a questo URL.';
$a->strings['Begin of the Blackout'] = 'Inzio del blackout';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Il formato è <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> anno, <em>MM</em> mese, <em>DD</em> giorno, <em>hh</em> ora e <em>mm</em> minuto.';
$a->strings['End of the Blackout'] = 'Fine del blackout';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Nota</strong>: Il reindirizzamento sarà attivo dal momento in cui premerai il pulsante di invio. Gli utenti attualmente autenticati <strong>non</strong> saranno disconnessi ma non potranno accedere in caso di disconnessione fintanto che il blackout sarà attivo.';
