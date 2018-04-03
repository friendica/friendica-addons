<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["bitchslap"] = "";
$a->strings["bitchslapped"] = "";
$a->strings["shag"] = "czupryna";
$a->strings["shagged"] = "";
$a->strings["do something obscenely biological to"] = "zrobić coś nieprzyzwoicie biologicznego";
$a->strings["did something obscenely biological to"] = "zrobił coś nieprzyzwoicie biologicznego";
$a->strings["point out the poke feature to"] = "zwróć uwagę na funkcję poke";
$a->strings["pointed out the poke feature to"] = "wskazał na funkcję poke do";
$a->strings["declare undying love for"] = "zadeklaruję dozgonną miłość do";
$a->strings["declared undying love for"] = "deklaruję dozgonną miłość dla";
$a->strings["patent"] = "patent";
$a->strings["patented"] = "opatentowane";
$a->strings["stroke beard"] = "";
$a->strings["stroked their beard at"] = "";
$a->strings["bemoan the declining standards of modern secondary and tertiary education to"] = "opłakują upadające standardy nowoczesnej szkoły średniej i wyższej";
$a->strings["bemoans the declining standards of modern secondary and tertiary education to"] = "żałuje upadających standardów nowoczesnej edukacji na poziomie średnim i wyższym";
$a->strings["hug"] = "objął";
$a->strings["hugged"] = "objąć";
$a->strings["kiss"] = "pocałunek";
$a->strings["kissed"] = "ucałować";
$a->strings["raise eyebrows at"] = "podnieś brwi";
$a->strings["raised their eyebrows at"] = "podnieśli brwi";
$a->strings["insult"] = "obraza";
$a->strings["insulted"] = "znieważony";
$a->strings["praise"] = "pochwała";
$a->strings["praised"] = "pochwalić";
$a->strings["be dubious of"] = "być wątpliwe";
$a->strings["was dubious of"] = "było wątpliwe";
$a->strings["eat"] = "jeść";
$a->strings["ate"] = "jadł";
$a->strings["giggle and fawn at"] = "chichotać i płakać";
$a->strings["giggled and fawned at"] = "";
$a->strings["doubt"] = "wątpić";
$a->strings["doubted"] = "powątpiewać";
$a->strings["glare"] = "blask";
$a->strings["glared at"] = "spojrzał na";
