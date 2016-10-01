<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['New Member'] = 'Nový člen';
$a->strings['Tips for New Members'] = 'Tipy pro nové členy';
$a->strings['Global Support Forum'] = 'Globální fórum podpory';
$a->strings['Local Support Forum'] = 'Lokální fórum podpory';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Message'] = 'Zpráva';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Vaše zpráva pro nové členy. Zde můžete použít BBCode.';
$a->strings['Add a link to global support forum'] = 'Přidejte odkaz na globální fórum podpory';
$a->strings['Should a link to the global support forum be displayed?'] = 'Má být odkaz na globální fórum podpory zobrazen?';
$a->strings['Add a link to the local support forum'] = 'Přidejte odkaz na lokální fórum podpory';
$a->strings['If you have a local support forum and wand to have a link displayed in the widget, check this box.'] = 'Pokud máte lokální fórum podpory a chcete mít zobrazen jeho odkaz, zvolte tuto volbu.';
$a->strings['Name of the local support group'] = 'Název lokálního fóra podpory';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Pokud jste výše uvedené zaškrtli, specifikujte zde <em>přezdívku</em> lokální podpůrné skupiny (např. pomahači)';
