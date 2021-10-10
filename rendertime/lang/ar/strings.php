<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s'] = 'قاعدة البيانات: %s/ %s، الشبكة:%s ، التقديم: %s، الجلسة:%s ، الإدخال / الإخراج: %s، أخرى: %s، المجموع:%s';
$a->strings['Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s'] = 'تهيئة النوع:%s ، التمهيد: %s، التهيئة: %s، المحتوى: %s، أخرى:%s ، الإجمالي:%s';
