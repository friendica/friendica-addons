<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['IRC Settings'] = 'إعدادات IRC';
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'هنا يمكنك تغيير الإعدادات الواسعة لنظام القنوات للانضمام والوصول تلقائيًا عبر الشريط الجانبي.
 لاحظ التغييرات التي تجريها هنا ، تؤثر  إذا قمت بتسجيل  الدخول على اختيار القناة فقط .';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'القناة (القنوات) للاتصال التلقائي (مفصولة بفواصل)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'قائمة القنوات التي سيتم الاتصال بها تلقائيًا عند بدء تشغيل التطبيق.';
$a->strings['Popular Channels (comma separated)'] = 'أشهر القنوات (مفصولة بفواصل)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'قائمة القنوات المشهورة ، سيتم عرضها على الجانب ويتم ربطها بسهولة للانضمام إليها.';
$a->strings['IRC Chatroom'] = 'غرفة محادثة IRC';
$a->strings['Popular Channels'] = 'أشهر القنوات';
