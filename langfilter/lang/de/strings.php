<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Language Filter'] = 'Sprachfilter';
$a->strings['This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings.'] = 'Dieses Addon versucht die Sprache eines Beitrags zu ermitteln und verbirgt den Inhalt, wenn du die Sprache nicht sprichst. Die Spracherkennung ist allerdings nicht perfekt, insbesondere bei kurzen Beiträgen.';
$a->strings['Use the language filter'] = 'Den Sprachfilter verwenden';
$a->strings['I speak'] = 'Ich spreche';
$a->strings['List of abbreviations (iso2 codes) for languages you speak, comma separated. For example "de,it".'] = 'Liste von Abkürzungen (ISO2 Codes) der Sprachen die du sprichst, getrennt durch Komma. Zum Beispiel: "de,it".';
$a->strings['Minimum confidence in language detection'] = 'Minimales Vertrauenslevel in die Spracherkennung';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimales Vertrauen in die Richtigkeit der erkannten Sprache. Wert zwischen 0 und 100. Beiträge mit einem niedrigeren Vertrauenslevel werden nicht gefiltert.';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Language Filter Settings saved.'] = 'Sprachfilter Einstellungen gespeichert.';
$a->strings['unspoken language %s - Click to open/close'] = 'nicht gesprochene Sprache %s - Zum öffnen/schließen klicken';
