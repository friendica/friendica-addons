<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Permission denied.'] = 'الطلب مرفوض.';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Client ID'] = 'بطاقة العميل';
$a->strings['Client Secret'] = 'أسرار العميل';
$a->strings['Error when registering buffer connection:'] = 'خطأ عند تسجيل اتصال المخزن المؤقت:';
$a->strings['You are now authenticated to buffer. '] = 'لقد تمت تصديقك الآن للتخزين المؤقت.';
$a->strings['return to the connector page'] = 'الرجوع إلى صفحة الموصل';
$a->strings['Post to Buffer'] = 'أضف إلى المخزن المؤقت';
$a->strings['Buffer Export'] = 'تصدير المخزن المؤقت';
$a->strings['Authenticate your Buffer connection'] = 'تصديق اتصال مخزنك المؤقت ';
$a->strings['Enable Buffer Post Addon'] = 'تفعيل ملحق الإضافة للمخزن المؤقت ';
$a->strings['Post to Buffer by default'] = 'إضافة إلي المخزن المؤقت بشكل افتراضي';
$a->strings['Check to delete this preset'] = 'تحقق لحذف هذا الإعداد المسبق';
$a->strings['Posts are going to all accounts that are enabled by default:'] = ' جميع المشاركات ستنتقل إلى الحسابات التي تم تمكينها افتراضيًا:';
