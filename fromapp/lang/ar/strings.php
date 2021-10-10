<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['FromApp Settings'] = 'من إعدادات التطبيق';
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'اسم التطبيق الذي تود إظهار منشوراتك منه. 
افصل بين أسماء التطبيقات المختلفة بفاصلة.
  سيتم اختيار اسم  واحد عشوائي لكل عملية نشر.';
$a->strings['Use this application name even if another application was used.'] = 'استخدم اسم هذا التطبيق حتى إذا تم استخدام تطبيق آخر.';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
