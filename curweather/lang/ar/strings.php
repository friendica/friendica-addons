<?php

if(! function_exists("string_plural_select_ar")) {
function string_plural_select_ar($n){
	$n = intval($n);
	if ($n==0) { return 0; } else if ($n==1) { return 1; } else if ($n==2) { return 2; } else if ($n%100>=3 && $n%100<=10) { return 3; } else if ($n%100>=11 && $n%100<=99) { return 4; } else  { return 5; }
}}
$a->strings['Error fetching weather data. Error was: '] = 'خطأ في جلب بيانات الطقس. كان الخطأ:';
$a->strings['Current Weather'] = 'الطقس الحالي';
$a->strings['Relative Humidity'] = 'نسبة الرطوبة ';
$a->strings['Pressure'] = 'الضغط';
$a->strings['Wind'] = 'الرياح';
$a->strings['Last Updated'] = 'آخر تحديث';
$a->strings['Data by'] = 'البيانات بواسطة';
$a->strings['Show on map'] = 'إظهار على الخريطة';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'حدثت مشكلة في الوصول إلى بيانات الطقس.  الق نظرة';
$a->strings['at OpenWeatherMap'] = 'في OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = 'لم يتم العثور على معرف التطبيق ، يرجى الاتصال بالمدير للحصول على واحد.';
$a->strings['Save Settings'] = 'احفظ الإعدادات';
$a->strings['Settings'] = 'الإعدادات';
$a->strings['Enter either the name of your location or the zip code.'] = 'أدخل اسم موقعك الجغرافي أو الرمز البريدي.';
$a->strings['Your Location'] = 'موقعك';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'تعرّيف موقعك (الاسم أو الرمز البريدي) ، على سبيل المثال <em>Berlin,DE</em> أو<em>14476,DE</em> .';
$a->strings['Units'] = 'الوحدات';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = 'حدد ما إذا كان يجب عرض درجة الحرارة &deg;C أو &deg;F.';
$a->strings['Show weather data'] = 'أظهر بيانات الطقس';
$a->strings['Caching Interval'] = 'فترة التخزين المؤقت';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'إلى متى يجب تخزين بيانات الطقس مؤقتًا؟ اختر وفقًا لنوع حساب OpenWeatherMap. ';
$a->strings['no cache'] = ' لا تخزين مؤقت';
$a->strings['minutes'] = 'دقائق';
$a->strings['Your APPID'] = 'معرف التطبيق الخاص بك';
$a->strings['Your API key provided by OpenWeatherMap'] = ' مفتاح API الخاص بك في OpenWeatherMap ';
