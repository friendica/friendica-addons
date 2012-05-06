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

function smiley_pack_uninstall() {
	unregister_hook('smilie', 'addon/smiley_pack/smiley_pack.php', 'smiley_pack_smilies');
}

 

function smiley_pack_smilies(&$a,&$b) {

#Smileys are split into various directories by the intended range of emotions.  This is in case we get too big and need to modularise things.  We can then cut and paste the right lines, move the right directory, and just change the name of the addon to happy_smilies or whatever.

#Be careful with invocation strings.  If you have a smiley called foo, and another called foobar, typing :foobar will call foo.  Avoid this with clever naming, using ~ instead of : 
#when all else fails.



#Animal smileys.

	$b['texts'][] = ':bunnyflowers';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/bunnyflowers.gif' . '" alt="' . ':bunnyflowers' . '" />';

	$b['texts'][] = ':chick';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':chick' . '" />';

	$b['texts'][] = ':bumblebee';
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

	$b['texts'][] = ':elephant';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/elephant.gif' . '" alt="' . ':elephant' . '" />';	

	$b['texts'][] = ':fish';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/fish.gif' . '" alt="' . ':fish' . '" />';	

	$b['texts'][] = ':giraffe';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/giraffe.gif' . '" alt="' . ':giraffe' . '" />';	

	$b['texts'][] = ':pig';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/animals/pig.gif' . '" alt="' . ':pig' . '" />';	



#Baby Smileys

	$b['texts'][] = ':baby';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/baby.gif' . '" alt="' . ':baby' . '" />';	

	$b['texts'][] = ':babycot';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/babycot.gif' . '" alt="' . ':babycot' . '" />';	
	

	$b['texts'][] = ':pregnant';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/pregnant.gif' . '" alt="' . ':pregnant' . '" />';	

	$b['texts'][] = ':stork';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/babies/stork.gif' . '" alt="' . ':stork' . '" />';	


#Confused Smileys	
	$b['texts'][] = ':confused';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/confused.gif' . '" alt="' . ':confused' . '" />';	
    
	$b['texts'][] = ':shrug';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/shrug.gif' . '" alt="' . ':shrug' . '" />';	

	$b['texts'][] = ':stupid';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/stupid.gif' . '" alt="' . ':stupid' . '" />';	

	$b['texts'][] = ':dazed';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/confused/dazed.gif' . '" alt="' . ':dazed' . '" />';	


#Cool Smileys

	$b['texts'][] = ':affro';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/cool/affro.gif' . '" alt="' . ':affro' . '" />';	

	$b['texts'][] = ':cool';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/cool/cool.gif' . '" alt="' . ':cool' . '" />';	

#Devil/Angel Smileys

	$b['texts'][] = ':angel';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/angel.gif' . '" alt="' . ':angel' . '" />';	

	$b['texts'][] = ':cherub';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/cherub.gif' . '" alt="' . ':cherub' . '" />';	

	$b['texts'][] = ':devilangel';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/blondedevil.gif' . '" alt="' . ':devilangel' . '" />';	

	$b['texts'][] = ':catdevil';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/catdevil.gif' . '" alt="' . ':catdevil' . '" />';	

	$b['texts'][] = ':devillish';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/devil.gif' . '" alt="' . ':devillish' . '" />';	
	
	$b['texts'][] = ':daseesaw';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/daseesaw.gif' . '" alt="' . ':daseesaw' . '" />';	

	$b['texts'][] = ':turnevil';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/turnevil.gif' . '" alt="' . ':turnevil' . '" />';	
	
	$b['texts'][] = ':saint';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/saint.gif' . '" alt="' . ':saint' . '" />';	

	$b['texts'][] = ':graveside';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/devilangel/graveside.gif' . '" alt="' . ':graveside' . '" />';	

#Unpleasent smileys.

	$b['texts'][] = ':toilet';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/toilet.gif' . '" alt="' . ':toilet' . '" />';	

	$b['texts'][] = ':fartinbed';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/fartinbed.gif' . '" alt="' . ':fartinbed' . '" />';

	$b['texts'][] = ':vomit';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/vomit.gif' . '" alt="' . ':vomit' . '" />';

	$b['texts'][] = ':fartblush';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/disgust/fartblush.gif' . '" alt="' . ':fartblush' . '" />';

#Drinks

	$b['texts'][] = ':tea';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/drink/tea.gif' . '" alt="' . ':tea' . '" />';

	$b['texts'][] = ':drool';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/drool/drool.gif' . '" alt="' . ':drool' . '" />';

#Sad smileys

	$b['texts'][] = ':crying';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sad/crying.png' . '" alt="' . ':crying' . '" />';

	$b['texts'][] = ':prisoner';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sad/prisoner.gif' . '" alt="' . ':prisoner' . '" />';

#Smoking - only one smiley in here, maybe it needs moving elsewhere?

	$b['texts'][] = ':smoking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/smoking/smoking.gif' . '" alt="' . ':smoking' . '" />';

#Sport smileys

	$b['texts'][] = ':basketball';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/basketball.gif' . '" alt="' . ':basketball' . '" />';

	$b['texts'][] = '~bowling';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/bowling.gif' . '" alt="' . '~bowling' . '" />';

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

	$b['texts'][] = ':archery';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/archery.gif' . '" alt="' . ':archery' . '" />';

	$b['texts'][] = ':football';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/football.gif' . '" alt="' . ':football' . '" />';

	$b['texts'][] = ':surfing';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/surfing.gif' . '" alt="' . ':surfing' . '" />';

	$b['texts'][] = ':snooker';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/snooker.gif' . '" alt="' . ':snooker' . '" />';
  
	$b['texts'][] = ':tennis';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/tennis.gif' . '" alt="' . ':tennis' . '" />';

	$b['texts'][] = ':horseriding';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/sport/horseriding.gif' . '" alt="' . ':horseriding' . '" />';

#Love smileys

	$b['texts'][] = ':iloveyou';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/iloveyou.gif' . '" alt="' . ':iloveyou' . '" />';

	$b['texts'][] = ':inlove';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/inlove.gif' . '" alt="' . ':inlove' . '" />';

	$b['texts'][] = '~love';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/love.gif' . '" alt="' . ':love' . '" />';

	$b['texts'][] = ':lovebear';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/lovebear.gif' . '" alt="' . ':lovebear' . '" />';

	$b['texts'][] = ':lovebed';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/lovebed.gif' . '" alt="' . ':lovebed' . '" />';

	$b['texts'][] = ':loveheart';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/love/loveheart.gif' . '" alt="' . ':loveheart' . '" />';

#Tired/Sleep smileys

	$b['texts'][] = ':countsheep';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/countsheep.gif' . '" alt="' . ':countsheep' . '" />';

	$b['texts'][] = ':hammock';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/hammock.gif' . '" alt="' . ':hammock' . '" />';

	$b['texts'][] = ':pillow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/pillow.gif' . '" alt="' . ':pillow' . '" />';

	$b['texts'][] = ':yawn';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/tired/yawn.gif' . '" alt="' . ':yawn' . '" />';

#Fight/Flame/Violent smileys

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

	$b['texts'][] = ':gangs';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/gangs.gif' . '" alt="' . ':gangs' . '" />';

	$b['texts'][] = ':acid';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fight/acid.gif' . '" alt="' . ':acid' . '" />';

#Fantasy smileys - monsters and dragons fantasy.  The other type of fantasy belongs in adult smileys

	$b['texts'][] = ':alienmonster';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/alienmonster.gif' . '" alt="' . ':alienmonster' . '" />';

	$b['texts'][] = ':barbarian';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/barbarian.gif' . '" alt="' . ':barbarian' . '" />';

	$b['texts'][] = ':dinosaur';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dinosaur.gif' . '" alt="' . ':dinosaur' . '" />';

	$b['texts'][] = ':dragon';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dragon.gif' . '" alt="' . ':dragon' . '" />';

	$b['texts'][] = ':draco';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/dragonwhelp.gif' . '" alt="' . ':draco' . '" />';

	$b['texts'][] = ':ghost';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/ghost.gif' . '" alt="' . ':ghost' . '" />';

	$b['texts'][] = ':mummy';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/fantasy/mummy.gif' . '" alt="' . ':mummy' . '" />';

#Food smileys

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

	$b['texts'][] = ':cooking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/cooking.gif' . '" alt="' . ':cooking' . '" />';

	$b['texts'][] = ':fryegg';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/food/fryegg.gif' . '" alt="' . ':fryegg' . '" />';

#Happy smileys

	$b['texts'][] = ':cloud9';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/happy/cloud9.gif' . '" alt="' . ':cloud9' . '" />';

	$b['texts'][] = ':tearsofjoy';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/happy/tearsofjoy.gif' . '" alt="' . ':tearsofjoy' . '" />';

#Repsect smileys

	$b['texts'][] = ':bow';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/bow.gif' . '" alt="' . ':bow' . '" />';

	$b['texts'][] = ':bravo';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/bravo.gif' . '" alt="' . ':bravo' . '" />';

	$b['texts'][] = ':hailking';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/hailking.gif' . '" alt="' . ':hailking' . '" />';

	$b['texts'][] = ':number1';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/respect/number1.gif' . '" alt="' . ':number1' . '" />';

#Laugh smileys

	$b['texts'][] = ':hahaha';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/hahaha.gif' . '" alt="' . ':hahaha' . '" />';

	$b['texts'][] = ':loltv';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/loltv.gif' . '" alt="' . ':loltv' . '" />';

	$b['texts'][] = ':rofl';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/laugh/rofl.gif' . '" alt="' . ':rofl' . '" />';

#Music smileys

	$b['texts'][] = ':dj';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/dj.gif' . '" alt="' . ':dj' . '" />';

	$b['texts'][] = ':drums';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/drums.gif' . '" alt="' . ':drums' . '" />';

	$b['texts'][] = ':elvis';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/elvis.gif' . '" alt="' . ':elivs' . '" />';

	$b['texts'][] = ':guitar';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/guitar.gif' . '" alt="' . ':guitar' . '" />';

	$b['texts'][] = ':trumpet';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/trumpet.gif' . '" alt="' . ':trumpet' . '" />';

	$b['texts'][] = ':violin';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/music/violin.gif' . '" alt="' . ':violin' . '" />';

#Smileys that used to be in core

	$b['texts'][] = ':headbang';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/headbang.gif' . '" alt="' . ':headbang' . '" />';

		$b['texts'][] = ':beard';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/beard.png' . '" alt="' . ':beard' . '" />';

	$b['texts'][] = ':whitebeard';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/whitebeard.png' . '" alt="' . ':whitebeard' . '" />';

	$b['texts'][] = ':shaka';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':shaka' . '" />';

	$b['texts'][] = ':\\.../';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':\\.../' . '" />';

	$b['texts'][] = ':\\ooo/';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':\\ooo/' . '" />';

	$b['texts'][] = ':headdesk';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/headdesk.gif' . '" alt="' . ':headdesk' . '" />';

#These two are still in core, so oldcore isn't strictly right, but we don't want too many directories

	$b['texts'][] = ':-d';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/laughing.gif' . '" alt="' . ':-d' . '" />';

	$b['texts'][] = ':-o';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smiley_pack/icons/oldcore/surprised.gif' . '" alt="' . ':-o' . '" />';




}
