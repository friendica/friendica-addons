<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['generic profile image'] = 'صورة عامة للملف الشخصي';
$a->strings['random geometric pattern'] = 'نمط هندسي عشوائي';
$a->strings['monster face'] = 'وجه الوحش';
$a->strings['computer generated face'] = 'وجه مولد من  الكمبيوتر';
$a->strings['retro arcade style face'] = 'نمط الوجه برجعية  الممرات';
$a->strings['roboter face'] = 'وجه الروبوت';
$a->strings['retro adventure game character'] = 'رجعية المغامرة لشخصية اللعب ';
$a->strings['Information'] = 'معلومات';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'تم تثبيت ملحق الحرافاتار . يرجى تعطيل إضافة ملحق الجرافاتار <br>. ستعود إضافة ليبرافاتار إلى جرافاتار  إذا لم يتم العثور على شيء في ليبرافاتار.';
$a->strings['Save Settings'] = 'حفظ الإعدادات';
$a->strings['Default avatar image'] = 'الصورة الرمزية الافتراضية';
$a->strings['Select default avatar image if none was found. See README'] = 'حدد الصورة الرمزية الافتراضية إذا لم يتم العثور على أي منها. انظر الملف التمهيدي';
