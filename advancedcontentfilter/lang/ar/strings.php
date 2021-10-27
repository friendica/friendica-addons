<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Filtered by rule: %s'] = 'رشّح حسب القاعدة: %s';
$a->strings['Advanced Content Filter'] = 'ترشيح المحتوى المتقدم';
$a->strings['Back to Addon Settings'] = 'الرجوع إلى إعدادات الإضافات';
$a->strings['Add a Rule'] = 'أضف قاعدة';
$a->strings['Help'] = 'مساعدة';
$a->strings['Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the help page.'] = 'إضافة وإدارة قواعد ترشيح المحتوى الشخصية هنا.
 القواعد لها اسم وتعبير سيتم مطابقته مع بيانات المشاركة. للحصول على مرجع كامل للعمليات والمتغيرات المتاحة ، راجع صفحة المساعدة.';
$a->strings['Your rules'] = 'القواعد';
$a->strings['You have no rules yet! Start adding one by clicking on the button above next to the title.'] = 'لا يوجد قواعد!
أضف واحدة من خلال النقر على الزر أعلاه بجوار العنوان.';
$a->strings['Disabled'] = 'معطل';
$a->strings['Enabled'] = 'مفعل';
$a->strings['Disable this rule'] = 'عطّل القاعدة';
$a->strings['Enable this rule'] = 'فعّل القاعدة';
$a->strings['Edit this rule'] = 'عدّل هذه القاعدة';
$a->strings['Edit the rule'] = 'عدّل القاعدة';
$a->strings['Save this rule'] = 'احفظ هذه القاعدة';
$a->strings['Delete this rule'] = 'احذف هذه القاعدة';
$a->strings['Rule'] = 'القاعدة';
$a->strings['Close'] = 'اغلق';
$a->strings['Add new rule'] = 'أضف قاعدة جديدة';
$a->strings['Rule Name'] = 'اسم القاعدة';
$a->strings['Rule Expression'] = 'تعبير القاعدة';
$a->strings['Cancel'] = 'الغ';
$a->strings['You must be logged in to use this method'] = 'عليك الولوج لاستخدام هذه الطريقة';
$a->strings['Invalid form security token, please refresh the page.'] = 'رمز أمان النموذج غير صالح ، يرجى تحديث الصفحة.';
$a->strings['The rule name and expression are required.'] = 'يلزم اسم وتعبير للقاعدة.';
$a->strings['Rule successfully added'] = 'نجحت إضافة القاعدة';
$a->strings['Rule doesn\'t exist or doesn\'t belong to you.'] = 'القاعدة غير موجودة أو لا تنتمي إليك.';
$a->strings['Rule successfully updated'] = 'نجح تحديث القاعدة';
$a->strings['Rule successfully deleted'] = 'نجح حذف القاعدة';
$a->strings['Missing argument: guid.'] = 'معامل ناقص: دليل.';
$a->strings['Unknown post with guid: %s'] = 'مشاركة غير معروفة ذات الدليل: 1%s';
$a->strings['Method not found'] = 'لم يُعثر على التطبيق';
