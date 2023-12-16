<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['New Member'] = 'Nový člen';
$a->strings['Tips for New Members'] = 'Tipy pro nové členy';
$a->strings['Save Settings'] = 'Uložit nastavení';
$a->strings['Message'] = 'Zpráva';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Vaše zpráva pro nové členy. Zde můžete použít BBCode.';
$a->strings['Name of the local support group'] = 'Název místního fóra podpory';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Pokud jste zaškrtl/a výše uvedenou možnost, specifikujte zde <em>přezdívku</em> místní skupiny podpory (např. pomocnici)';
