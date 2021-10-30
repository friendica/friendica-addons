<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Post to blogger'] = 'شارك على بلوغر';
$a->strings['Blogger Export'] = 'تصدير بلوغر';
$a->strings['Enable Blogger Post Addon'] = 'تفعيل إضافة مشاركة بلوغر';
$a->strings['Blogger username'] = 'اسم مستخدم بلوغر';
$a->strings['Blogger password'] = 'كلمة السر بلوغر';
$a->strings['Blogger API URL'] = 'عنوان URL لواجهة برمجة تطبيقات  API بلوغر';
$a->strings['Post to Blogger by default'] = 'شارك في بلوغر إفتراضيا';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['Post from Friendica'] = 'شارك من فرينديكا Friendica';
