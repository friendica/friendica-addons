<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Use Cat as Avatar'] = 'استخدم القط كصورة رمزية';
$a->strings['More Random Cat!'] = 'المزيد من القطط العشوائية!';
$a->strings['Reset to email Cat'] = 'إعادة التعيين إلى البريد الإلكتروني للقط';
$a->strings['Cat Avatar Settings'] = 'إعدادات الصورة رمزيةللقط';
$a->strings['Set default profile avatar or randomize the cat.'] = 'قم بتعيين الصورة الرمزية الافتراضية للملف الشخصي أو قم بتعيين القط بشكل عشوائي.';
$a->strings['The cat hadn\'t found itself.'] = 'القط لم يجد  نفسه';
$a->strings['There was an error, the cat ran away.'] = ' هرب القط  ،كان هناك خطأ';
$a->strings['Profile Photos'] = 'الصور الشخصية';
$a->strings['Meow!'] = 'مياوو';
