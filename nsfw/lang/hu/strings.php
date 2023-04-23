<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Ez a bővítmény megadott szavakra vagy szövegre keres a bejegyzésekben, és összecsukja azokat. Használható például az #NSFW megjelölésű tartalmak szűréséhez, amelyek bizonyos időkben vagy helyeken nem megfelelőnek tekinthetők, mint például munka közben. Ez hasznos a nem kapcsolódó vagy idegesítő tartalom elrejtéséhez a közvetlen megtekintés elől.';
$a->strings['Enable Content filter'] = 'Tartalomszűrő engedélyezése';
$a->strings['Comma separated list of keywords to hide'] = 'Kulcsszavak vesszővel elválasztott listája az elrejtéshez';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = 'Használjon /kifejezést/ reguláris kifejezések megadásához, #címkét a kettőskeresztescímkék kifejezett illesztéséhez (nem érzékeny a kis- és nagybetűkre) vagy szabályos szavakat (kis- és nagybetűérzékeny)';
$a->strings['Content Filter (NSFW and more)'] = 'Tartalomszűrő (érzékeny tartalmak és egyebek)';
$a->strings['Regular expression "%s" fails to compile'] = 'A(z) „%s” reguláris kifejezés nem fordítható le';
$a->strings['Filtered tag: %s'] = 'Kiszűrt címke: %s';
$a->strings['Filtered word: %s'] = 'Kiszűrt szó: %s';
