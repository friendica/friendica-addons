<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'Päättymispäivä on aikaisempi kuin katkoksen alkamispäivä. Korjaa päivämäärä.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Tarkista vielä kerran tämänhetkiset katkos-asetukset. Katkos alkaa <strong>%s</strong> ja se päätty <strong>%s</strong>.';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Redirect URL'] = 'Uudelleenohjauksen URL-osoite';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Kaikki verkkovierailijasi ohjataan tähän URL-osoitteseen.';
$a->strings['Begin of the Blackout'] = 'Katkos alkaa';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Muoto on <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> vuosi, <em>MM</em> kuukausi, <em>DD</em> päivä, <em>hh</em> tunti ja <em>mm</em> minuutti.';
$a->strings['End of the Blackout'] = 'Katkos loppuu';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Huomaa</strong>: Uudelleen ohjaus aktivoituu siitä hetkestä kun painat lähetä-painiketta. Tällä hetkellä kirjautuneena olevia käyttäjiä <strong>ei</strong> kirjata ulos, mutta he eivät voi kirjautua takaisin sisään, jos he kirjautuvat ulos katkoksen ollessa voimassa.';
