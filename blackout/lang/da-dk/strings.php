<?php

if(! function_exists("string_plural_select_da_DK")) {
function string_plural_select_da_DK($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'Slutdatoen ligger før startdatoen for blackoutet, dette bør du ordne.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Vær venlig at kontrollere dine nuværende indstillinger for blackoutet. Det starter <strong>%s</strong> og slutter <strong>%s</strong>';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Redirect URL'] = 'Viderestil URL';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Alle dine besøgende fra interenettet vil blive viderestillet til denne URL.';
$a->strings['Begin of the Blackout'] = 'Blackout starter';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Formatet er <strong>ÅÅÅÅ-MM-DD tt:mm</strong>; <strong>ÅÅÅÅ</strong>år, <strong>MM</strong> måned, <strong>DD</strong> dag, <strong>hh</strong> time og <strong>mm</strong> minut.';
$a->strings['End of the Blackout'] = 'Blackout slutter';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Note</strong>: Viderestillingenvil være aktiveret fra det øjeblik, du trykker på send-knappen. Indloggede brugere vil <strong>ikke</strong> blive smidt af, men kan ikke logge på igen efter at have logget ud, mens blackoutet er aktivt.';
