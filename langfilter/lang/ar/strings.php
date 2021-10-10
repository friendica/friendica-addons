<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Language Filter'] = 'اللغة';
$a->strings['Use the language filter'] = 'اختيار اللغة';
$a->strings['Able to read'] = '  قابل  للقراءة';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'قائمة الرموز ( ISO 639-1) للغات ، مفصولة بفواصل. على سبيل المثال "de، it"';
$a->strings['Minimum confidence in language detection'] = 'الحد الأدنى من نسبة اكتشاف اللغة';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'الحد الأدنى من صحة اكتشاف اللغة  ، من 0 إلى 100. لن تتم فلترة المشاركات عندما تكون صحة اكتشاف اللغة أقل من هذه النسبة المئوية.';
$a->strings['Minimum length of message body'] = 'الحد الأدنى لنص الرسالة';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'الحد الأدنى لأحرف نص الرسالة لاستخدام الفلتر. لن يتم فلترة المشاركات الأقصر من هذا. ملاحظة: لا يمكن الاعتماد على اكتشاف اللغة للمحتوى القصير (<200 حرف).';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
$a->strings['Filtered language: %s'] = 'اختيار اللغة: %s';
