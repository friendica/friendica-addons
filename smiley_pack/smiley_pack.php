<?php
/*
 * Name: Smiley Pack
 * Description: Pack of smileys that make master too AOLish.
 * Version: 1.0
 * Author: Thomas Willingham (based on Mike Macgirvin's Adult Smile template) 
 * All smileys from sites offering them as Public Domain
 * 
 * 
 */

function smiley_pack_install() {
	register_hook('smilie', 'addon/smiley_pack/smiley_pack.php', 'smiley_pack_smilies');
}

function smiley_pack__uninstall() {
	unregister_hook('smilie', 'addon/smiley_pack/smiley_pack.php', 'smiley_pack_smilies');
}

 

function smiley_pack_smilies(&$a,&$b) {

	$b['texts'][] = ':bunnyflowers';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/bunnyflowers.gif' . '" alt="' . ':bunnyflowers' . '" />';

	$b['texts'][] = ':chick';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':chick' . '" />';

	$b['texts'][] = ':bee';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/bee.gif' . '" alt="' . ':bee' . '" />';	

	$b['texts'][] = ':ladybird';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/ladybird.gif' . '" alt="' . ':ladybird' . '" />';	

	$b['texts'][] = ':bigspider';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/bigspider.gif' . '" alt="' . ':bigspider' . '" />';	

	$b['texts'][] = ':cat';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/cat.gif' . '" alt="' . ':cat' . '" />';	

	$b['texts'][] = ':bunny';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/bunny.gif' . '" alt="' . ':bunny' . '" />';	

	$b['texts'][] = ':chick';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':chick' . '" />';	

	$b['texts'][] = ':cow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/cow.gif' . '" alt="' . ':cow' . '" />';	
    
	$b['texts'][] = ':crab';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/crab.gif' . '" alt="' . ':crab' . '" />';	

	$b['texts'][] = ':dolphin';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/dolphin.gif' . '" alt="' . ':dolphin' . '" />';	

	$b['texts'][] = ':dragonfly';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/dragonfly.gif' . '" alt="' . ':dragonfly' . '" />';	

	$b['texts'][] = ':frog';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/frog.gif' . '" alt="' . ':frog' . '" />';	

	$b['texts'][] = ':hamster';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/hamster.gif' . '" alt="' . ':hamster' . '" />';	

	$b['texts'][] = ':monkey';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/monkey.gif' . '" alt="' . ':monkey' . '" />';	

	$b['texts'][] = ':horse';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/horse.gif' . '" alt="' . ':horse' . '" />';	
  
	$b['texts'][] = ':parrot';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/parrot.gif' . '" alt="' . ':parrot' . '" />';	

	$b['texts'][] = ':tux';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/tux.gif' . '" alt="' . ':tux' . '" />';	

	$b['texts'][] = ':snail';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/snail.gif' . '" alt="' . ':snail' . '" />';	

	$b['texts'][] = ':sheep';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/sheep.gif' . '" alt="' . ':sheep' . '" />';	

	$b['texts'][] = ':dog';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/dog.gif' . '" alt="' . ':dog' . '" />';	


	$b['texts'][] = ':baby';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/baby.gif' . '" alt="' . ':baby' . '" />';	

	$b['texts'][] = ':babycot';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/babycot.gif' . '" alt="' . ':babycot' . '" />';	
	

	$b['texts'][] = ':pregnant';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/pregnant.gif' . '" alt="' . ':pregnant' . '" />';	

	$b['texts'][] = ':stork';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/stork.gif' . '" alt="' . ':stork' . '" />';	

	
	$b['texts'][] = ':confused';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/confused.gif' . '" alt="' . ':confused' . '" />';	
    
	$b['texts'][] = ':shrug';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/shrug.gif' . '" alt="' . ':shrug' . '" />';	

	$b['texts'][] = ':stupid';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/stupid.gif' . '" alt="' . ':stupid' . '" />';	

	$b['texts'][] = ':affro';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/cool/affro.gif' . '" alt="' . ':affro' . '" />';	

	$b['texts'][] = ':cool';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/cool/cool.gif' . '" alt="' . ':cool' . '" />';	

	$b['texts'][] = ':angel';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/angel.gif' . '" alt="' . ':angel' . '" />';	

	$b['texts'][] = ':cherub';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/cherub.gif' . '" alt="' . ':cherub' . '" />';	

	$b['texts'][] = ':devilangel';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/blondedevil.gif' . '" alt="' . ':devilangel' . '" />';	

	$b['texts'][] = ':catdevil';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/catdevil.gif' . '" alt="' . ':catdevil' . '" />';	

	$b['texts'][] = ':devil';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/devil.gif' . '" alt="' . ':devil' . '" />';	
	

	$b['texts'][] = ':graveside';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/graveside.gif' . '" alt="' . ':graveside' . '" />';	

	$b['texts'][] = ':toilet';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/toilet.gif' . '" alt="' . ':toilet' . '" />';	

	$b['texts'][] = ':fartinbed';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/fartinbed.gif' . '" alt="' . ':fartinbed' . '" />';

	$b['texts'][] = ':vomit';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/vomit.gif' . '" alt="' . ':vomit' . '" />';

	$b['texts'][] = ':tea';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/drink/tea.gif' . '" alt="' . ':tea' . '" />';

	$b['texts'][] = ':drool';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/drool/drool.gif' . '" alt="' . ':drool' . '" />';

	$b['texts'][] = ':crying';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sad/crying.png' . '" alt="' . ':crying' . '" />';

	$b['texts'][] = ':prisoner';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sad/prisoner.gif' . '" alt="' . ':prisoner' . '" />';

	$b['texts'][] = ':smoking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/smoking/smoking.gif' . '" alt="' . ':smoking' . '" />';

	$b['texts'][] = ':basketball';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/basketball.gif' . '" alt="' . ':basketball' . '" />';

	$b['texts'][] = ':bowling';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/bowling.gif' . '" alt="' . ':bowling' . '" />';

	$b['texts'][] = ':cycling';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/cycling.gif' . '" alt="' . ':cycling' . '" />';

	$b['texts'][] = ':darts';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/darts.gif' . '" alt="' . ':darts' . '" />';

	$b['texts'][] = ':fencing';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/fencing.gif' . '" alt="' . ':fencing' . '" />';

	$b['texts'][] = ':golf';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/golf.gif' . '" alt="' . ':golf' . '" />';

	$b['texts'][] = ':juggling';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/juggling.gif' . '" alt="' . ':juggling' . '" />';

	$b['texts'][] = ':skipping';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/skipping.gif' . '" alt="' . ':skipping' . '" />';

	$b['texts'][] = ':iloveyou';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/iloveyou.gif' . '" alt="' . ':iloveyou' . '" />';

	$b['texts'][] = ':inlove';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/inlove.gif' . '" alt="' . ':inlove' . '" />';

	$b['texts'][] = ':love';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/love.gif' . '" alt="' . ':love' . '" />';

	$b['texts'][] = ':lovebear';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/lovebear.gif' . '" alt="' . ':lovebear' . '" />';

	$b['texts'][] = ':lovebed';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/lovebed.gif' . '" alt="' . ':lovebed' . '" />';

	$b['texts'][] = ':loveheart';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/loveheart.gif' . '" alt="' . ':loveheart' . '" />';

	$b['texts'][] = ':countsheep';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/countsheep.gif' . '" alt="' . ':countsheep' . '" />';

	$b['texts'][] = ':hammock';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/hammock.gif' . '" alt="' . ':hammock' . '" />';

	$b['texts'][] = ':pillow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/pillow.gif' . '" alt="' . ':pillow' . '" />';

	$b['texts'][] = ':2guns';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/2guns.gif' . '" alt="' . ':2guns' . '" />';

	$b['texts'][] = ':alienfight';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/alienfight.gif' . '" alt="' . ':alienfight' . '" />';

	$b['texts'][] = ':army';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/army.gif' . '" alt="' . ':army' . '" />';

	$b['texts'][] = ':arrowhead';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/arrowhead.gif' . '" alt="' . ':arrowhead' . '" />';

	$b['texts'][] = ':bfg';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/bfg.gif' . '" alt="' . ':bfg' . '" />';

	$b['texts'][] = ':bowman';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/bowman.gif' . '" alt="' . ':bowman' . '" />';

	$b['texts'][] = ':chainsaw';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/chainsaw.gif' . '" alt="' . ':chainsaw' . '" />';

	$b['texts'][] = ':crossbow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/crossbow.gif' . '" alt="' . ':crossbow' . '" />';

	$b['texts'][] = ':crusader';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/crusader.gif' . '" alt="' . ':crusader' . '" />';

	$b['texts'][] = ':dead';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/dead.gif' . '" alt="' . ':dead' . '" />';

	$b['texts'][] = ':hammersplat';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/hammersplat.gif' . '" alt="' . ':hammersplat' . '" />';

	$b['texts'][] = ':lasergun';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/lasergun.gif' . '" alt="' . ':lasergun' . '" />';

	$b['texts'][] = ':machinegun';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/machinegun.gif' . '" alt="' . ':machinegun' . '" />';

	$b['texts'][] = ':marine';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/marine.gif' . '" alt="' . ':marine' . '" />';

	$b['texts'][] = ':sabre';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/sabre.gif' . '" alt="' . ':sabre' . '" />';

	$b['texts'][] = ':tank';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/tank.gif' . '" alt="' . ':tank' . '" />';

	$b['texts'][] = ':viking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/viking.gif' . '" alt="' . ':viking' . '" />';

	$b['texts'][] = ':alienmonster';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/alienmonster.gif' . '" alt="' . ':alienmonster' . '" />';

	$b['texts'][] = ':barbarian';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/barbarian.gif' . '" alt="' . ':barbarian' . '" />';

	$b['texts'][] = ':dinosaur';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dinosaur.gif' . '" alt="' . ':dinosaur' . '" />';

	$b['texts'][] = ':dragon';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dragon.gif' . '" alt="' . ':dragon' . '" />';

	$b['texts'][] = ':dragonwhelp';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dragonwhelp.gif' . '" alt="' . ':dragonwhelp' . '" />';

	$b['texts'][] = ':ghost';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/ghost.gif' . '" alt="' . ':ghost' . '" />';

	$b['texts'][] = ':mummy';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/mummy.gif' . '" alt="' . ':mummy' . '" />';

	$b['texts'][] = ':apple';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/apple.gif' . '" alt="' . ':apple' . '" />';

	$b['texts'][] = ':broccoli';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/broccoli.gif' . '" alt="' . ':brocolli' . '" />';

	$b['texts'][] = ':cake';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/cake.gif' . '" alt="' . ':cake' . '" />';

	$b['texts'][] = ':carrot';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/carrot.gif' . '" alt="' . ':carrot' . '" />';

	$b['texts'][] = ':popcorn';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/popcorn.gif' . '" alt="' . ':popcorn' . '" />';

	$b['texts'][] = ':tomato';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/tomato.gif' . '" alt="' . ':tomato' . '" />';

	$b['texts'][] = ':banana';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/banana.gif' . '" alt="' . ':banana' . '" />';


	$b['texts'][] = ':cloud9';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/happy/cloud9.gif' . '" alt="' . ':cloud9' . '" />';

	$b['texts'][] = ':tearsofjoy';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/happy/tearsofjoy.gif' . '" alt="' . ':tearsofjoy' . '" />';

	$b['texts'][] = ':bow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/bow.gif' . '" alt="' . ':bow' . '" />';

	$b['texts'][] = ':bravo';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/bravo.gif' . '" alt="' . ':bravo' . '" />';

	$b['texts'][] = ':hailking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/hailking.gif' . '" alt="' . ':hailking' . '" />';

	$b['texts'][] = ':number1';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/number1.gif' . '" alt="' . ':number1' . '" />';

	$b['texts'][] = ':hahaha';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/hahaha.gif' . '" alt="' . ':hahaha' . '" />';

	$b['texts'][] = ':loltv';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/loltv.gif' . '" alt="' . ':loltv' . '" />';

	$b['texts'][] = ':rofl';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/rofl.gif' . '" alt="' . ':rofl' . '" />';




}
