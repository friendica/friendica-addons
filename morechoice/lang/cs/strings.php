<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Androgyne'] = 'Androgyn';
$a->strings['Bear'] = 'Medvěd';
$a->strings['Bigender'] = 'Bigender';
$a->strings['Cross dresser'] = 'Crossdresser';
$a->strings['Eunuch'] = 'Eunuch';
$a->strings['Lady'] = 'Dáma';
$a->strings['Metrosexual'] = 'Metrosexuál';
$a->strings['Monk'] = 'Mnich';
$a->strings['Nun'] = 'Jeptiška';
$a->strings['Transman'] = 'Transmuž';
$a->strings['Transwoman'] = 'Transžena';
$a->strings['Transvesti'] = 'Transvestita';
$a->strings['Can\'t remember'] = 'Nepamatuju si';
$a->strings['Hard to tell these days'] = 'Těžké říct touhle dobou';
$a->strings['Girls with big tits'] = 'Holky s velkýma kozama';
$a->strings['Millionaires'] = 'Milionáři';
$a->strings['Guys with big schlongs'] = 'Chlapi s velkýma ptákama';
$a->strings['Easy women'] = 'Lehké ženy';
$a->strings['People with impaired mobility'] = 'Lidé s pohybovým postižením';
$a->strings['Amputees'] = 'Amputovaní';
$a->strings['Statues, mannequins and immobility'] = 'Sochy, figuríny a nepohyblivost';
$a->strings['Pain'] = 'Bolest';
$a->strings['Trans men'] = 'Transmuži';
$a->strings['Older women'] = 'Starší ženy';
$a->strings['In public'] = 'Na veřejnosti';
$a->strings['In danger'] = 'V nebezpečí';
$a->strings['Pretending to be male'] = 'Předstírají, že jsou muži';
$a->strings['Pretending to be female'] = 'Předstírají, že jsou ženy';
$a->strings['Crying'] = 'Pláčí';
$a->strings['Nappies/Diapers'] = 'Plenky';
$a->strings['Trees'] = 'Stromy';
$a->strings['Vomit'] = 'Zvracení';
$a->strings['Murder'] = 'Vražda';
$a->strings['Fat people'] = 'Tlustí lidé';
$a->strings['Feet'] = 'Nohy';
$a->strings['Covered in insects'] = 'Pokryti hmyzem';
$a->strings['Turning a human being into furniture'] = 'Mění lidské bytosti v nábytek';
$a->strings['Elderly people'] = 'Postarší lidé';
$a->strings['Transgender people'] = 'Transgenderoví lidé';
$a->strings['Criminals'] = 'Zločinci';
$a->strings['Stealing'] = 'Kradou';
$a->strings['Giants'] = 'Obři';
$a->strings['Masochism'] = 'Masochismus';
$a->strings['Cars'] = 'Auto';
$a->strings['Menstruation'] = 'Menstruace';
$a->strings['Obscene language'] = 'Sprostý jazyk';
$a->strings['Noses'] = 'Nosy';
$a->strings['Navels'] = 'Pupky';
$a->strings['Corpses'] = 'Mrtvoly';
$a->strings['Smells'] = 'Pachy';
$a->strings['Nonliving objects'] = 'Neživoucí předměty';
$a->strings['Sleeping people'] = 'Spící lidé';
$a->strings['Urination'] = 'Močení';
$a->strings['Eating people'] = 'Žerou lidi';
$a->strings['Animals'] = 'Zvířata';
$a->strings['I\'d rather just have some chocolate'] = 'Radši bych si prostě dal čokoládu';
$a->strings['Polygamist'] = 'Polygamist(k)a';
$a->strings['Half married'] = 'Napůl ženatý/vdaná';
$a->strings['Living in the past'] = 'Žiju v minulosti';
$a->strings['Hurt in the past'] = 'Ublížen v minulosti';
