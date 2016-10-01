<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Forum Directory'] = 'Adresář Fór';
$a->strings['Public access denied.'] = 'Veřejný přístup odepřen.';
$a->strings['Global Directory'] = 'Globální adresář';
$a->strings['Find on this site'] = 'Nalézt na tomto webu';
$a->strings['Finding: '] = 'Zjištění: ';
$a->strings['Site Directory'] = 'Adresář serveru';
$a->strings['Find'] = 'Najít';
$a->strings['Age: '] = 'Věk: ';
$a->strings['Gender: '] = 'Pohlaví: ';
$a->strings['Location:'] = 'Místo:';
$a->strings['Gender:'] = 'Pohlaví:';
$a->strings['Status:'] = 'Status:';
$a->strings['Homepage:'] = 'Domácí stránka:';
$a->strings['About:'] = 'O mě:';
$a->strings['No entries (some entries may be hidden).'] = 'Žádné záznamy (některé položky mohou být skryty).';
