<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["bitchslap"] = "spoliczkować";
$a->strings["bitchslapped"] = "spoliczkowanie";
$a->strings["shag"] = "wścieka się";
$a->strings["shagged"] = "wściekły";
$a->strings["do something obscenely biological to"] = "zrobić coś nieprzyzwoicie biologicznego";
$a->strings["did something obscenely biological to"] = "zrobił coś nieprzyzwoicie biologicznego";
$a->strings["point out the poke feature to"] = "wskaż funkcję szturchnąć";
$a->strings["pointed out the poke feature to"] = "wskaż na funkcję zaczepić do";
$a->strings["declare undying love for"] = "wyznaję dozgonną miłość do";
$a->strings["declared undying love for"] = "deklaruję dozgonną miłość dla";
$a->strings["patent"] = "patent";
$a->strings["patented"] = "opatentowane";
$a->strings["stroke beard"] = "pogłaskać brodę";
$a->strings["stroked their beard at"] = "pogłaskał ich brody o";
$a->strings["bemoan the declining standards of modern secondary and tertiary education to"] = "opłakują upadające standardy nowoczesnej szkoły średniej i wyższej";
$a->strings["bemoans the declining standards of modern secondary and tertiary education to"] = "żałuje upadających standardów nowoczesnej edukacji na poziomie średnim i wyższym";
$a->strings["hug"] = "objął";
$a->strings["hugged"] = "objąć";
$a->strings["kiss"] = "całus";
$a->strings["kissed"] = "ucałować";
$a->strings["raise eyebrows at"] = "podnieś brwi";
$a->strings["raised their eyebrows at"] = "podnieśli brwi";
$a->strings["insult"] = "obraza";
$a->strings["insulted"] = "znieważony";
$a->strings["praise"] = "pochwała";
$a->strings["praised"] = "pochwalić";
$a->strings["be dubious of"] = "jest wątpliwe";
$a->strings["was dubious of"] = "było wątpliwe";
$a->strings["eat"] = "jeść";
$a->strings["ate"] = "jadł";
$a->strings["giggle and fawn at"] = "chichotać i płakać";
$a->strings["giggled and fawned at"] = "roześmiał się przymilająco";
$a->strings["doubt"] = "wątpi";
$a->strings["doubted"] = "powątpiewa";
$a->strings["glare"] = "blask";
$a->strings["glared at"] = "spojrzał na";
