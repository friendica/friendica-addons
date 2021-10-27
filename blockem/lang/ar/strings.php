<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Blockem'] = 'احجبه<br>';
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'إخفاء محتوى المستخدم عن طريق تصغير المشاركات. و استبدال الصورة الرمزية الخاصة بهم بصورة عامة.';
$a->strings['Comma separated profile URLS:'] = 'عناوين  URLS لملف التعريف مقسمة بفواصل:';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['Filtered user: %s'] = ' تصفية حسب المستخدم :1%s';
$a->strings['Unblock Author'] = 'ألغ الحجب عن المدون';
$a->strings['Block Author'] = 'احجب المدون';
