<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['bitchslap'] = 'palmuire-javră';
$a->strings['bitchslapped'] = 'javră-pălmuită';
$a->strings['shag'] = 'pune-o';
$a->strings['shagged'] = 'a pus-o';
$a->strings['do something obscenely biological to'] = 'fă-i ceva biologic obscen lui';
$a->strings['did something obscenely biological to'] = 'i s-a făcut ceva biologic obscen lui';
$a->strings['point out the poke feature to'] = 'arată caracteristica de abordare lui';
$a->strings['pointed out the poke feature to'] = 'caracteristică de abordare arătată lui';
$a->strings['declare undying love for'] = 'declară dragoste veșnică pentru';
$a->strings['declared undying love for'] = 'dragoste veșnică declarată lui';
$a->strings['patent'] = 'brevet';
$a->strings['patented'] = 'brevetat';
$a->strings['stroke beard'] = 'lovire în barbă';
$a->strings['stroked their beard at'] = 'loviți în barbă la';
$a->strings['bemoan the declining standards of modern secondary and tertiary education to'] = 'deplângerea standardelor decăzute ale educației moderne secundare și terțiare pentru';
$a->strings['bemoans the declining standards of modern secondary and tertiary education to'] = 'deplânge standardele decăzute ale educației moderne secundare și terțiare pentru';
$a->strings['hug'] = 'îmbrățișare';
$a->strings['hugged'] = 'îmbrățișat(ă)';
$a->strings['kiss'] = 'sărut';
$a->strings['kissed'] = 'sărutat(ă)';
$a->strings['raise eyebrows at'] = 'ridică sprâncenele către';
$a->strings['raised their eyebrows at'] = 'a ridicat sprâncenele către';
$a->strings['insult'] = 'insultă';
$a->strings['insulted'] = 'insultat(ă)';
$a->strings['praise'] = 'laudă';
$a->strings['praised'] = 'lăudat(ă)';
$a->strings['be dubious of'] = 'arată-ți sceptitudinea față de';
$a->strings['was dubious of'] = 'a fost catalogat(ă) ca ciudat(ă) de către';
$a->strings['eat'] = 'devorează';
$a->strings['ate'] = 'devorat(ă)';
$a->strings['giggle and fawn at'] = 'chicotește și lingușește-l pe';
$a->strings['giggled and fawned at'] = 'a fost chicotit(ă) și lingușit(ă) de către';
$a->strings['doubt'] = 'îndoială';
$a->strings['doubted'] = 'nu a prezentat încredere';
$a->strings['glare'] = 'fixează cu privirea';
$a->strings['glared at'] = 'luat(ă) în colimator';
