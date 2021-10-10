<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Filtered by rule: %s'] = ' Filtruj według reguły: %s';
$a->strings['Advanced Content Filter'] = 'Zaawansowany filtr zawartości';
$a->strings['Back to Addon Settings'] = 'Powrót do ustawień dodatków';
$a->strings['Add a Rule'] = 'Dodaj regułę';
$a->strings['Help'] = 'Pomoc';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href="advancedcontentfilter/help">help page</a>.'] = 'Dodaj i zarządzaj regułami filtrowania treści osobistych na tym ekranie. Reguły mają nazwę i dowolne wyrażenie, które zostanie dopasowane do danych postów. Aby uzyskać pełne informacje o dostępnych operacjach i zmiennych, sprawdź <a href="advancedcontentfilter/help">stronę pomocy</a>.';
$a->strings['Your rules'] = 'Twoje zasady';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'Nie masz jeszcze żadnych zasad! Zacznij dodawać jedną, klikając przycisk znajdujący się nad tytułem.';
$a->strings['Disabled'] = 'Wyłącz';
$a->strings['Enabled'] = 'Włącz';
$a->strings['Disable this rule'] = 'Wyłącz tę regułę';
$a->strings['Enable this rule'] = 'Włącz tę regułę';
$a->strings['Edit this rule'] = 'Edytuj tę regułę';
$a->strings['Edit the rule'] = 'Edytuj regułę';
$a->strings['Save this rule'] = 'Zapisz tę regułę';
$a->strings['Delete this rule'] = 'Usuń tę regułę';
$a->strings['Rule'] = 'Reguła';
$a->strings['Close'] = 'Zamknij';
$a->strings['Add new rule'] = 'Dodaj nową regułę';
$a->strings['Rule Name'] = 'Nazwa reguły';
$a->strings['Rule Expression'] = 'Wyrażanie reguły';
$a->strings['<p>Examples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>'] = '<p>przykład</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tagi</li></ul>';
$a->strings['Cancel'] = 'Anuluj';
$a->strings['You must be logged in to use this method'] = 'Musisz być zalogowany, aby skorzystać z tej metody';
$a->strings['Invalid form security token, please refresh the page.'] = 'Nieprawidłowy token zabezpieczający formularz, odśwież stronę.';
$a->strings['The rule name and expression are required.'] = 'Nazwa reguły i wyrażenie są wymagane.';
$a->strings['Rule successfully added'] = 'Reguła została pomyślnie dodana';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'Reguła nie istnieje lub nie należy do ciebie.';
$a->strings['Rule successfully updated'] = 'Reguła została pomyślnie zaktualizowana';
$a->strings['Rule successfully deleted'] = 'Reguła została pomyślnie usunięta';
$a->strings['Missing argument: guid.'] = 'Brakujący argument: guid.';
$a->strings['Unknown post with guid: %s'] = 'Nieznany post z guid:%s';
$a->strings['Method not found'] = 'Nie znaleziono metody';
