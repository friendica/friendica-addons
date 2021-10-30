<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Permission denied.'] = 'رُفض الإذن.';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['Client ID'] = 'معرف العميل';
$a->strings['Client Secret'] = 'الرمز السري للعميل';
$a->strings['Error when registering buffer connection:'] = 'خطأ عند تسجيل اتصال بافر:';
$a->strings['You are now authenticated to buffer. '] = 'خولت بافر.';
$a->strings['return to the connector page'] = 'ارجع إلى صفحة الموصل';
$a->strings['Post to Buffer'] = 'شارك في بافر';
$a->strings['Buffer Export'] = 'تصدير بافر';
$a->strings['Authenticate your Buffer connection'] = 'استوثق اتصال بافر';
$a->strings['Enable Buffer Post Addon'] = 'تفعيل إضافة مشركة بافر';
$a->strings['Post to Buffer by default'] = 'شارك في بافر افتراضيا';
$a->strings['Check to delete this preset'] = 'تحقق لحذف هذا الإعداد المسبق';
$a->strings['Posts are going to all accounts that are enabled by default:'] = ' جميع المشاركات ستنتقل إلى الحسابات التي تم تمكينها افتراضيًا:';
