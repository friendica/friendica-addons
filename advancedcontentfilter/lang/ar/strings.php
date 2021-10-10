<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Filtered by rule: %s'] = 'فلتر حسب القاعدة: %s';
$a->strings['Advanced Content Filter'] = 'فلتر المحتوى المتقدم';
$a->strings['Back to Addon Settings'] = 'الرجوع إلى ملحق الإعدادات';
$a->strings['Add a Rule'] = 'أضف توصية';
$a->strings['Help'] = 'مساعدة';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the help page.'] = 'إضافة وإدارة قواعد فلتر  المحتوى الشخصية هنا.
 القواعد لها اسم وتعبير سيتم مطابقته مع بيانات المشاركة. للحصول على مرجع كامل للعمليات والمتغيرات المتاحة ، راجع صفحة المساعدة.';
$a->strings['Your rules'] = 'توصيات';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'لا يوجد توصيات لك بعد!
 ابدأ في إضافة واحدة من خلال النقر على الزر أعلاه بجوار العنوان';
$a->strings['Disabled'] = 'معطل';
$a->strings['Enabled'] = 'مفعل';
$a->strings['Disable this rule'] = ' تعطيل القاعدة';
$a->strings['Enable this rule'] = 'تمكين القاعدة';
$a->strings['Edit this rule'] = 'تحرير القاعدة';
$a->strings['Edit the rule'] = 'تحرير القاعدة';
$a->strings['Save this rule'] = 'حفظ القاعدة';
$a->strings['Delete this rule'] = 'حذف القاعدة';
$a->strings['Rule'] = ' القاعدة';
$a->strings['Close'] = 'غلق';
$a->strings['Add new rule'] = 'أضف قاعدة جديدة';
$a->strings['Rule Name'] = ' اسم القاعدة ';
$a->strings['Rule Expression'] = 'التعبير عن القاعدة';
$a->strings['Cancel'] = 'الغ';
$a->strings['You must be logged in to use this method'] = 'يجب عليك تسجيل الدخول لاستخدام هذه الطريقة';
$a->strings['Invalid form security token, please refresh the page.'] = 'رمز أمان النموذج غير صالح ، يرجى تحديث الصفحة.';
$a->strings['The rule name and expression are required.'] = 'يلزم اسم  وتعبير للقاعدة.';
$a->strings['Rule successfully added'] = 'تمت إضافة القاعدة بنجاح';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'القاعدة غير موجودة أو لا تنتمي إليك.';
$a->strings['Rule successfully updated'] = 'تم تحديث القاعدة بنجاح';
$a->strings['Rule successfully deleted'] = 'تم حذف القاعدة بنجاح';
$a->strings['Missing argument: guid.'] = 'تفقد النواقص: دليل.';
$a->strings['Unknown post with guid: %s'] = 'مشاركة غير معروفة مع دليل: 1%s';
$a->strings['Method not found'] = 'لم يُعثر على التطبيق ';
