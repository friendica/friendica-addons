<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'تاريخ الانتهاء يسبق تاريخ بدء التعتيم ، يجب إصلاح هذا.';
$a->strings['Please double check the current settings for the blackout. It will begin on <strong>%s</strong> and end on <strong>%s</strong>.'] = 'يرجى التحقق مرة أخرى من الإعدادات الحالية لحالة التعتيم. بدأ في 1 ٪ 1 %s وانتهاء في2٪2 %s.';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
$a->strings['Redirect URL'] = 'اعد توجيه URL';
$a->strings['All your visitors from the web will be redirected to this URL.'] = 'سيتم إعادة توجيه جميع الزوار من الويب إلى عنوان URL ';
$a->strings['Begin of the Blackout'] = 'بدء التعتيم';
$a->strings['Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.'] = 'الصيغة:
الشهر
اليوم
الساعة
الدقيقة ';
$a->strings['End of the Blackout'] = 'نهاية التعتيم';
$a->strings['<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can\'t login again after logging out while the blackout is still in place.'] = 'ملاحظة: ستكون إعادة التوجيه جاهزة من اللحظة التي تضغط فيها على زر الإرسال. لن يتم استبعاد المستخدمين الذين قاموا بتسجيل الدخول حاليًا ولكن لا يمكنهم تسجيل الدخول مرة أخرى بعد تسجيل الخروج بينما لا يزال التعتيم ساريًا.';
