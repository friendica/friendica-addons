<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['IFTTT Mirror'] = 'مرآة IFTTT  ';
$a->strings['Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'] = 'قم بإنشاء حساب على IFTTT. أنشئ ثلاث طرق على Facebook بتطبيق الMaker (في النموذج "if Facebook ثم Maker") بالمعايير التالية:<a href="http://www.ifttt.com"></a><a href="https://ifttt.com/maker"></a>';
$a->strings['Body for "new status message"'] = 'نص "رسالة الحالة الجديدة"';
$a->strings['Body for "new photo upload"'] = 'نص "تحميل صورة جديدة"';
$a->strings['Body for "new link post"'] = 'نص "مشاركة ارتباط جديدة"';
$a->strings['Generate new key'] = 'إنشاء مفتاح جديد';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
