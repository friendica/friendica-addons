<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'Data końcowa jest wcześniejsza niż data rozpoczęcia blackoutu, powinieneś to naprawić.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'Proszę dokładnie sprawdzić aktualne ustawienia blackoutu. Zacznie się o <strong>%s</strong> i zakończy o <strong>%s</strong>.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Redirect URL'] = 'Przekierowanie URL';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'Wszyscy Twoi goście z Internetu zostaną przekierowani na ten adres URL.';
$a->strings['Begin of the Blackout'] = 'Rozpocznij blackout';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'Format to <tt>RRRR-MM-DD gg:mm</tt>; <em>RRRR</em> rok, <em>MM</em> miesiąc, <em>DD</em> dzień, <em>gg</em> godzina i <em>mm</em> minuta.';
$a->strings['End of the Blackout'] = 'Koniec blackoutu';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = '<strong>Uwaga</strong>: Przekierowanie będzie aktywne od momentu naciśnięcia przycisku przesyłania. Użytkownicy aktualnie zalogowani <strong>nie</strong> zostaną wyrzuceni, ale nie będą mogli zalogować się ponownie po wylogowaniu, jeśli blackout będzie nadal obowiązywać.';
