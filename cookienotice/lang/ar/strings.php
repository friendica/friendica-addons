<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['This website uses cookies. If you continue browsing this website, you agree to the usage of cookies.'] = 'هذا الموقع يستخدم ملف تعريف الارتباط. إذا واصلت تصفح هذا الموقع ، فإنك توافق على استخدام ملفات تعريف الارتباط.';
$a->strings['OK'] = 'موافق';
$a->strings['<b>Configure your cookie usage notice.</b> It should just be a notice, saying that the website uses cookies. It is shown as long as a user didnt confirm clicking the OK button.'] = 'تهيئة إشعار استخدام ملف تعريف الارتباط الخاص بك.
 يجب أن يكون مجرد إشعار ، يقول أن الموقع يستخدم ملفات تعريف الارتباط. 
يتم عرضه طالما لم يؤكد المستخدم النقر فوق الزر "موافق"<b>';
$a->strings['Cookie Usage Notice'] = 'إشعار استخدام ملفات تعريف الارتباط';
$a->strings['OK Button Text'] = 'زر الموافقة';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['This website uses cookies to recognize revisiting and logged in users. You accept the usage of these cookies by continue browsing this website.'] = 'يستخدم هذا الموقع ملفات تعريف الارتباط للتعرف على إعادة الزيارة والمستخدمين الذين ولجوا.
  استمراريتك في تصفح هذا الموقع يعني  تقبلك استخدام ملفات تعريف الارتباط ';
