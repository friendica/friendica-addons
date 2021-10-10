<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'يعرض ملحق MathJax الصيغ الرياضية المكتوبة باستخدام صيغة LaTeX محاطة بمجموعة $$ أو eqnarray المعتادة في منشورات الحائط وعلامة تبويب الشبكة والبريد الخاص.';
$a->strings['Use the MathJax renderer'] = 'استخدم عارض  MathJax';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
