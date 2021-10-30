<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Post to Diaspora'] = 'شارك في دياسبورا';
$a->strings['Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. '] = 'تذكر: يمكن الوصول إليك من دياسبورا باستخدام معرف فرنديكا<strong>%s</strong>.';
$a->strings['This connector is only meant if you still want to use your old Diaspora account for some time. '] = 'يجب استخدام هذا الموصل في حالة كنت تريد استخدام حساب دياسبورا القديم.';
$a->strings['However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead.'] = 'على أي حال ، من الأفضل أن تطلع متراسليك في دياسبورا بمعرفك الجديد <strong>%s</strong>. ';
$a->strings['All aspects'] = 'كل الفئات';
$a->strings['Public'] = 'علني';
$a->strings['Post to aspect:'] = 'انشر إلى الفئات:';
$a->strings['Connected with your Diaspora account <strong>%s</strong>'] = 'متصل بحساب دياسبورا <strong>%s</strong>';
$a->strings['Can\'t login to your Diaspora account. Please check handle (in the format user@domain.tld) and password.'] = 'لا يمكن تسجيل الدخول إلى حساب الدياسبورا. يرجى التحقق من المعرف (على شاكلة user@domain.tld) وكلمة المرور.';
$a->strings['Diaspora Export'] = 'تصدير الدياسبورا';
$a->strings['Information'] = 'معلومات';
$a->strings['Error'] = 'خطأ';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['Enable Diaspora Post Addon'] = 'تفعيل إضافة مشاركة دياسبورا';
$a->strings['Diaspora handle'] = 'معرف الدياسبورا';
$a->strings['Diaspora password'] = 'كلمة مرور الدياسبورا';
$a->strings['Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it.'] = 'تنبيه خصوصية: سيتم تخزين كلمة مرور دياسبورا دون تشفير للاستيثاق منك في خادم دياسبورا. هذا يعني أنه يمكن لمسؤول عقدة فرنديكا الوصول إليها.';
$a->strings['Post to Diaspora by default'] = 'شارك في دياسبورا افتراضيا';
