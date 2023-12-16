<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Twitter'] = 'Отправить в Twitter';
$a->strings['Allow posting to Twitter'] = 'Разрешить отправку сообщений на Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Если включено, то все ваши <strong>общественные сообщения</strong> могут быть отправлены на связанный аккаунт Twitter. Вы можете сделать это по умолчанию (здесь) или для каждого сообщения отдельно при написании записи.';
$a->strings['Send public postings to Twitter by default'] = 'Отправлять сообщения для всех в Twitter по умолчанию';
