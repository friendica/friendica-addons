<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	return ($n%10==1 && $n%100!=11 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : $n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)? 2 : 3);;
}}
;
$a->strings["Post to Twitter"] = "Отправить в Twitter";
$a->strings["Twitter settings updated."] = "Настройки Twitter обновлены.";
$a->strings["Twitter Posting Settings"] = "Настройка отправки сообщений в Twitter";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Не найдено пары потребительских ключей для Twitter. Пожалуйста, обратитесь к администратору сайта.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "Чтобы подключиться к Twitter аккаунту, нажмите на кнопку ниже, чтобы получить код безопасности от Twitter, который нужно скопировать в поле ввода ниже, и отправить форму. Только ваши <strong>публичные сообщения</strong> будут отправляться на Twitter.";
$a->strings["Log in with Twitter"] = "Войдите через Twitter";
$a->strings["Copy the PIN from Twitter here"] = "Скопируйте PIN с Twitter сюда";
$a->strings["Submit"] = "Подтвердить";
$a->strings["Currently connected to: "] = "В настоящее время соединены с: ";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Если включено, то все ваши <strong>общественные сообщения</strong> могут быть отправлены на связанный аккаунт Twitter. Вы можете сделать это по умолчанию (здесь) или для каждого сообщения отдельно при написании записи.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Внимание</strong>: Из-за настроек приватности (<em>Hide your profile details from unknown viewers?</em>) ссылка, которая может быть включена в твит, будет вести посетителя на пустую страницу с информированием о том, что доступ к профилю запрещен.";
$a->strings["Allow posting to Twitter"] = "Разрешить отправку сообщений на Twitter";
$a->strings["Send public postings to Twitter by default"] = "Отправлять сообщения для всех в Twitter по умолчанию";
$a->strings["Mirror all posts from twitter that are no replies or retweets"] = "Получать посты с Twitter у которых нет ответов и ретвитов";
$a->strings["Shortening method that optimizes the tweet"] = "Метод сокращения ссылок для оптимизации твита";
$a->strings["Send linked #-tags and @-names to Twitter"] = "Отправлять #-теги и @-имена в Twitter ссылками";
$a->strings["Clear OAuth configuration"] = "Удалить конфигурацию OAuth";
$a->strings["Settings updated."] = "Настройки обновлены.";
$a->strings["Consumer key"] = "Consumer key";
$a->strings["Consumer secret"] = "Consumer secret";
$a->strings["Name of the Twitter Application"] = "Имя приложения для Twitter";
$a->strings["set this to avoid mirroring postings from ~friendica back to ~friendica"] = "установите это для избежания отправки сообщений из Friendica обратно в Friendica";
