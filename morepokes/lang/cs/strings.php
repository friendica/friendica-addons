<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["bitchslap"] = "dát facku";
$a->strings["bitchslapped"] = "dal facku";
$a->strings["shag"] = "";
$a->strings["shagged"] = "";
$a->strings["do something obscenely biological to"] = "udělat něco obscéně biologického uživateli";
$a->strings["did something obscenely biological to"] = "udělal něco obscéně biologického uživateli";
$a->strings["point out the poke feature to"] = "upozornit na funkci šťouchnutí";
$a->strings["pointed out the poke feature to"] = "upozornil na funkci šťouchnutí";
$a->strings["declare undying love for"] = "vyjadřit nehynoucí lásku ke";
$a->strings["declared undying love for"] = "vyjadřil nehynoucí lásku ke";
$a->strings["patent"] = "patentovat";
$a->strings["patented"] = "patentoval";
$a->strings["stroke beard"] = "pohladit plnovous";
$a->strings["stroked their beard at"] = "pohladil jeho/její plnovous na";
$a->strings["bemoan the declining standards of modern secondary and tertiary education to"] = "stěžovat si na klesající úroveň moderního sekundárního a terciárního vzdělávání u";
$a->strings["bemoans the declining standards of modern secondary and tertiary education to"] = "si stěžoval na klesající úroveň moderního sekundárního a terciárního vzdělávání u";
$a->strings["hug"] = "obejmout";
$a->strings["hugged"] = "obejmut ";
$a->strings["kiss"] = "políbit";
$a->strings["kissed"] = "políbil";
$a->strings["raise eyebrows at"] = "zvednout obočí na";
$a->strings["raised their eyebrows at"] = "zvednul obočí na";
$a->strings["insult"] = "urazit";
$a->strings["insulted"] = "urazil";
$a->strings["praise"] = "pochválit";
$a->strings["praised"] = "pochválil";
$a->strings["be dubious of"] = "mít pochyby o";
$a->strings["was dubious of"] = "měl pochyby o";
$a->strings["eat"] = "sníst";
$a->strings["ate"] = "snědl";
$a->strings["giggle and fawn at"] = "hihňat se";
$a->strings["giggled and fawned at"] = "se hihňal";
$a->strings["doubt"] = "pochybovat";
$a->strings["doubted"] = "pochyboval";
$a->strings["glare"] = "zabodávát pohledem";
$a->strings["glared at"] = "zabodával pohledem ";
