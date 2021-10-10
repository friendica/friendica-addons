<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['"pageheader" Settings'] = 'إعدادات رأس الصفحة';
$a->strings['Message'] = 'رسالة';
$a->strings['Message to display on every page on this server (or put a pageheader.html file in your docroot)'] = 'رسالة ليتم عرضها في كل صفحة على هذا الملقم (أو ضع ملف pageheader.html في docroot)';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
