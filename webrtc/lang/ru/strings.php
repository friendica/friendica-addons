<?php

if(! function_exists("string_plural_select_ru")) {
function string_plural_select_ru($n){
	$n = intval($n);
	if ($n%10==1 && $n%100!=11) { return 0; } else if ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14)) { return 1; } else if ($n%10==0 || ($n%10>=5 && $n%10<=9) || ($n%100>=11 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['WebRTC Videochat'] = 'Видеочат WebRTC';
$a->strings['Save Settings'] = 'Сохранить настройки';
$a->strings['WebRTC Base URL'] = 'Основная URL WebRTC';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Страница, на которой ваши пользователи будут создавать чат WebRTC. Например, https://live.mayfirst.org .';
$a->strings['Video Chat'] = 'Видеочат';
$a->strings['WebRTC is a video and audio conferencing tool that works in all modern browsers. Just create a new chat room and send the link to someone you want to chat with.'] = 'WebRTC это средство для видео и аудиоконференций, которое работает во всех современных браузерах. Просто создайте новую комнату и пошлите ссылку тем, с кем хотите пообщаться.';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Пожалуйста, свяжитесь со своим администратором Friendica и напомните сконфигурировать дополнение WebRTC.';
