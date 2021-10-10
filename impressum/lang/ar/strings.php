<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Impressum'] = 'تحرير';
$a->strings['Site Owner'] = 'مالك الموقع';
$a->strings['Email Address'] = 'البريد الالكتروني';
$a->strings['Postal Address'] = 'العنوان البريدي';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'يجب تكوين ملحق تحرير. <br />الرجاء إضافة <tt> المالك </tt>على الأقل إلى ملف التكوين المتغير الخاص بك. بالنسبة للمتغيرات الأخرى ، يرجى الرجوع إلى ملف README الخاص بالملحق';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['The page operators name.'] = 'اسم مشغلي الصفحة.';
$a->strings['Site Owners Profile'] = 'الملف الشخصي لمالك الموقع';
$a->strings['Profile address of the operator.'] = 'عنوان مشغلي الصفحة.';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'كيفية الاتصال بالمشغل عبر البريد العادي. يمكنك استخدام BBCode هنا.';
$a->strings['Notes'] = 'ملاحظات';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'الملاحظات الإضافية المعروضة أسفل معلومات الاتصال. يمكنك استخدام BBCode هنا';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = 'كيفية الاتصال بالمشغل عبر البريد الإلكتروني. (سيتم عرضه معتم)';
$a->strings['Footer note'] = 'ملاحظة التذييل';
$a->strings['Text for the footer. You can use BBCode here.'] = 'نص التذييل. يمكنك استخدام BBCode هنا.';
