<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Post to Diaspora'] = 'انشر في الدياسبورا';
$a->strings['Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. '] = 'يرجى تذكر: يامكانية وصولك من الشتات دائمًا  باستخدام مقبض Friendica الخاص بك1 %s';
$a->strings['This connector is only meant if you still want to use your old Diaspora account for some time. '] = 'هذا الرابط مخصص فقط إذا كنت لا تزال ترغب في استخدام حساب الشتات القديم الخاص بك لبعض الوقت.';
$a->strings['However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead.'] = 'ومع ذلك ، يُفضل أن تخبر جهات اتصالك في الشتات بالمعامل الجديد بدلاً من ذلك. 1 1%s';
$a->strings['All aspects'] = 'كل الأوجه';
$a->strings['Public'] = 'عام';
$a->strings['Post to aspect:'] = 'النشر إلى المنظور:';
$a->strings['Connected with your Diaspora account <strong>%s</strong>'] = 'متصل بحساب الدياسبورا الخاص بك1 %s1';
$a->strings['Can\'t login to your Diaspora account. Please check handle (in the format user@domain.tld) and password.'] = 'لا يمكن تسجيل الدخول إلى حساب الدياسبورا الخاص بك. يرجى التحقق من المقبض (كالاتي user@domain.tld) وكلمة المرور.';
$a->strings['Diaspora Export'] = 'تصدير الدياسبورا';
$a->strings['Information'] = 'معلومات';
$a->strings['Error'] = 'أخطاء';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
$a->strings['Enable Diaspora Post Addon'] = 'تمكين الدياسبورا نشر ملحق ';
$a->strings['Diaspora handle'] = 'مقبض الدياسبورا';
$a->strings['Diaspora password'] = 'كلمة مرور الدياسبورا';
$a->strings['Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it.'] = 'إشعار الخصوصية: سيتم تخزين كلمة المرور الخاصة بك في الدياسبورا دون تشفير للمصادقة عليك مع جروب الشتات الخاص بك. هذا يعني أنه يمكن لمسؤول عقدة Friendica الوصول إليهز';
$a->strings['Post to Diaspora by default'] = '1
انشر في الدياسبورا بشكل افتراضي';
