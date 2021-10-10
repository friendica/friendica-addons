<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Group Text'] = 'نص المجموعة:';
$a->strings['Use a text only (non-image) group selector in the "group edit" menu'] = 'استخدم محدد نصي للمجموعة فقط (غير مصور) في قائمة "تعديل المجموعة"';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
