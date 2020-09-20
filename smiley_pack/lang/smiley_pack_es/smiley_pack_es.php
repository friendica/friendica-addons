<?php
/*
 * Name: Smiley Pack (Español)
 * Description: Pack of smileys that make master too AOLish.
 * Version: 1.02
 * Author: Thomas Willingham (based on Mike Macgirvin's Adult Smile template) 
 * All smileys from sites offering them as Public Domain
 */
use Friendica\Core\Hook;
use Friendica\DI;

function smiley_pack_es_install() {
	Hook::register('smilie', 'addon/smiley_pack_es/smiley_pack_es.php', 'smiley_pack_smilies_es');
}

function smiley_pack_smilies_es(&$a,&$b) {

#Smileys are split into various directories by the intended range of emotions.  This is in case we get too big and need to modularise things.  We can then cut and paste the right lines, move the right directory, and just change the name of the addon to happy_smilies or whatever.

#Be careful with invocation strings.  If you have a smiley called foo, and another called foobar, typing :foobar will call foo.  Avoid this with clever naming, using ~ instead of : 
#when all else fails.



#Animal smileys.

	$b['texts'][] = ':conejitoflores';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bunnyflowers.gif' . '" alt="' . ':conejitoflores' . '" />';

	$b['texts'][] = ':pollito';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':pollito' . '" />';

	$b['texts'][] = ':abeja';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bee.gif' . '" alt="' . ':abeja' . '" />';

	$b['texts'][] = ':mariquita';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/ladybird.gif' . '" alt="' . ':mariquita' . '" />';

	$b['texts'][] = ':araña';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bigspider.gif' . '" alt="' . ':araña' . '" />';

	$b['texts'][] = ':gato';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/cat.gif' . '" alt="' . ':gato' . '" />';

	$b['texts'][] = ':conejito';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bunny.gif' . '" alt="' . ':conejito' . '" />';

	$b['texts'][] = ':vaca';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/cow.gif' . '" alt="' . ':vaca' . '" />';
    
	$b['texts'][] = ':cangrejo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/crab.gif' . '" alt="' . ':cangrejo' . '" />';

	$b['texts'][] = ':delfín';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dolphin.gif' . '" alt="' . ':delfín' . '" />';

	$b['texts'][] = ':libélula';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dragonfly.gif' . '" alt="' . ':libélula' . '" />';

	$b['texts'][] = ':rana';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/frog.gif' . '" alt="' . ':rana' . '" />';

	$b['texts'][] = ':hamster';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/hamster.gif' . '" alt="' . ':hamster' . '" />';

	$b['texts'][] = ':mono';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/monkey.gif' . '" alt="' . ':mono' . '" />';

	$b['texts'][] = ':caballo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/horse.gif' . '" alt="' . ':caballo' . '" />';
  
	$b['texts'][] = ':loro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/parrot.gif' . '" alt="' . ':loro' . '" />';

	$b['texts'][] = ':tux';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/tux.gif' . '" alt="' . ':tux' . '" />';

	$b['texts'][] = ':caracol';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/snail.gif' . '" alt="' . ':caracol' . '" />';

	$b['texts'][] = ':oveja';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/sheep.gif' . '" alt="' . ':oveja' . '" />';

	$b['texts'][] = ':perro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dog.gif' . '" alt="' . ':perro' . '" />';

	$b['texts'][] = ':elefante';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/elephant.gif' . '" alt="' . ':elefante' . '" />';

	$b['texts'][] = ':pez';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/fish.gif' . '" alt="' . ':pez' . '" />';

	$b['texts'][] = ':jirafa';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/giraffe.gif' . '" alt="' . ':jirafa' . '" />';

	$b['texts'][] = ':cerdo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/pig.gif' . '" alt="' . ':cerdo' . '" />';



#Baby Smileys

	$b['texts'][] = ':bebé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/baby.gif' . '" alt="' . ':bebé' . '" />';

	$b['texts'][] = ':cuna';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/babycot.gif' . '" alt="' . ':cuna' . '" />';
	

	$b['texts'][] = ':embarazada';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/pregnant.gif' . '" alt="' . ':embarazada' . '" />';

	$b['texts'][] = ':cigüeña';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/stork.gif' . '" alt="' . ':cigüeña' . '" />';


#Confused Smileys	
	$b['texts'][] = ':confundido';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/confused.gif' . '" alt="' . ':confundido' . '" />';
    
	$b['texts'][] = ':encogehombros';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/shrug.gif' . '" alt="' . ':encogehombros' . '" />';

	$b['texts'][] = ':estúpido';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/stupid.gif' . '" alt="' . ':estúpido' . '" />';

	$b['texts'][] = ':aturdidp';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/dazed.gif' . '" alt="' . ':aturdid' . '" />';


#Cool Smileys

	$b['texts'][] = ':afro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/cool/affro.gif' . '" alt="' . ':afro' . '" />';

	$b['texts'][] = ':guay';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/cool/cool.gif' . '" alt="' . ':guay' . '" />';

#Devil/Angel Smileys

	$b['texts'][] = ':ángel';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/angel.gif' . '" alt="' . ':ángel' . '" />';

	$b['texts'][] = ':querubín';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/cherub.gif' . '" alt="' . ':querubín' . '" />';

	$b['texts'][] = ':ángeldemonio';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/blondedevil.gif' . '" alt="' . ':ángeldemonio' . '" />';

	$b['texts'][] = ':gatodemonio';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/catdevil.gif' . '" alt="' . ':gatodemonio' . '" />';

	$b['texts'][] = ':diabólico';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/devil.gif' . '" alt="' . ':diabólico' . '" />';
	
	$b['texts'][] = ':adbalancín';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/daseesaw.gif' . '" alt="' . ':adbalancín' . '" />';

	$b['texts'][] = ':vuelvedemonio';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/turnevil.gif' . '" alt="' . ':vuelvedemonio' . '" />';
	
	$b['texts'][] = ':santo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/saint.gif' . '" alt="' . ':santo' . '" />';

	$b['texts'][] = ':tumba';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/graveside.gif' . '" alt="' . ':tumba' . '" />';

#Unpleasent smileys.

	$b['texts'][] = ':retrete';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/toilet.gif' . '" alt="' . ':retrete' . '" />';

	$b['texts'][] = ':pedoencama';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/fartinbed.gif' . '" alt="' . ':pedoencama' . '" />';

	$b['texts'][] = ':vómito';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/vomit.gif' . '" alt="' . ':vómito' . '" />';

	$b['texts'][] = ':pedosonrojo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/fartblush.gif' . '" alt="' . ':pedosonrojo' . '" />';

#Drinks

	$b['texts'][] = ':té';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/drink/tea.gif' . '" alt="' . ':té' . '" />';

	$b['texts'][] = ':baba';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/drool/drool.gif' . '" alt="' . ':baba' . '" />';

#Sad smileys

	$b['texts'][] = ':llorar';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/crying.png' . '" alt="' . ':llorar' . '" />';

	$b['texts'][] = ':prisonero';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/prisoner.gif' . '" alt="' . ':prisonero' . '" />';

	$b['texts'][] = ':suspiro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/sigh.gif' . '" alt="' . ':suspiro' . '" />';

#Smoking - only one smiley in here, maybe it needs moving elsewhere?

	$b['texts'][] = ':fumar';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/smoking/smoking.gif' . '" alt="' . ':fumar' . '" />';

#Sport smileys

	$b['texts'][] = ':baloncesto';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/basketball.gif' . '" alt="' . ':baloncesto' . '" />';

	$b['texts'][] = ':bolos';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/bowling.gif' . '" alt="' . ':bolos' . '" />';

	$b['texts'][] = ':enbici';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/cycling.gif' . '" alt="' . ':enbici' . '" />';

	$b['texts'][] = ':dardos';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/darts.gif' . '" alt="' . ':dardos' . '" />';

	$b['texts'][] = ':esgrima';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/fencing.gif' . '" alt="' . ':esgrima' . '" />';

	$b['texts'][] = ':golf';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/golf.gif' . '" alt="' . ':golf' . '" />';

	$b['texts'][] = ':malabares';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/juggling.gif' . '" alt="' . ':malabares' . '" />';

	$b['texts'][] = ':comba';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/skipping.gif' . '" alt="' . ':comba' . '" />';

	$b['texts'][] = ':tiroconarco';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/archery.gif' . '" alt="' . ':tiroconarco' . '" />';

	$b['texts'][] = ':fútbol';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/football.gif' . '" alt="' . ':fútbol' . '" />';

	$b['texts'][] = ':surf';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/surfing.gif' . '" alt="' . ':surf' . '" />';

	$b['texts'][] = ':billar';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/snooker.gif' . '" alt="' . ':billar' . '" />';
  
	$b['texts'][] = ':tenis';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/tennis.gif' . '" alt="' . ':tenis' . '" />';

	$b['texts'][] = ':acaballo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/horseriding.gif' . '" alt="' . ':acaballo' . '" />';

#Love smileys

	$b['texts'][] = ':tequiero';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/iloveyou.gif' . '" alt="' . ':tequiero' . '" />';

	$b['texts'][] = ':enamorada';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/inlove.gif' . '" alt="' . ':enamorada' . '" />';

	$b['texts'][] = ':amor';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/love.gif' . '" alt="' . ':amor' . '" />';

	$b['texts'][] = ':osoamoroso';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/lovebear.gif' . '" alt="' . ':osoamoroso' . '" />';

	$b['texts'][] = ':camaamor';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/lovebed.gif' . '" alt="' . ':camaamor' . '" />';

	$b['texts'][] = ':corazónamor';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/loveheart.gif' . '" alt="' . ':corazónamor' . '" />';

#Tired/Sleep smileys

	$b['texts'][] = ':contandoovejas';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/countsheep.gif' . '" alt="' . ':contandoovejas' . '" />';

	$b['texts'][] = ':hamaca';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/hammock.gif' . '" alt="' . ':hamaca' . '" />';

	$b['texts'][] = ':almohada';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/pillow.gif' . '" alt="' . ':almohada' . '" />';

	$b['texts'][] = ':bostezo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/yawn.gif' . '" alt="' . ':bostezo' . '" />';

#Fight/Flame/Violent smileys

	$b['texts'][] = ':pistolas';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/2guns.gif' . '" alt="' . ':pistolas' . '" />';

	$b['texts'][] = ':peleamarciano';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/alienfight.gif' . '" alt="' . ':peleamarciano' . '" />';

	$b['texts'][] = ':alfa';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/alpha.png' . '" alt="' . ':alfa' . '" />';

	$b['texts'][] = ':ejército';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/army.gif' . '" alt="' . ':ejército' . '" />';

	$b['texts'][] = ':cabezaflecha';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/arrowhead.gif' . '" alt="' . ':cabezaflecha' . '" />';

	$b['texts'][] = ':bfg';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/bfg.gif' . '" alt="' . ':bfg' . '" />';

	$b['texts'][] = ':arquero';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/bowman.gif' . '" alt="' . ':arquero' . '" />';

	$b['texts'][] = ':motosierra';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/chainsaw.gif' . '" alt="' . ':motosierra' . '" />';

	$b['texts'][] = ':ballesta';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/crossbow.gif' . '" alt="' . ':ballesta' . '" />';

	$b['texts'][] = ':cruzado';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/crusader.gif' . '" alt="' . ':cruzado' . '" />';

	$b['texts'][] = ':muerto';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/dead.gif' . '" alt="' . ':muerto' . '" />';

	$b['texts'][] = ':martillazo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/hammersplat.gif' . '" alt="' . ':martillazo' . '" />';

	$b['texts'][] = ':pistolalaser';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/lasergun.gif' . '" alt="' . ':pistolalaser' . '" />';

	$b['texts'][] = ':metralleta';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/machinegun.gif' . '" alt="' . ':metralleta' . '" />';

	$b['texts'][] = ':marine';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/marine.gif' . '" alt="' . ':marine' . '" />';

	$b['texts'][] = ':sable';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/sabre.gif' . '" alt="' . ':sable' . '" />';

	$b['texts'][] = ':tanque';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/tank.gif' . '" alt="' . ':tanque' . '" />';

	$b['texts'][] = ':vikingo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/viking.gif' . '" alt="' . ':vikingo' . '" />';

	$b['texts'][] = ':bandas';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/gangs.gif' . '" alt="' . ':bandas' . '" />';

	$b['texts'][] = ':ácido';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/acid.gif' . '" alt="' . ':ácido' . '" />';

#Fantasy smileys - monsters and dragons fantasy.  The other type of fantasy belongs in adult smileys

	$b['texts'][] = ':alien';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/alienmonster.gif' . '" alt="' . ':alien' . '" />';

	$b['texts'][] = ':bárbaro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/barbarian.gif' . '" alt="' . ':bárbaro' . '" />';

	$b['texts'][] = ':dinosaurio';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/dinosaur.gif' . '" alt="' . ':dinosaurio' . '" />';

	$b['texts'][] = ':dragón';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/dragon.gif' . '" alt="' . ':dragón' . '" />';

	$b['texts'][] = ':draco';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/dragonwhelp.gif' . '" alt="' . ':draco' . '" />';

	$b['texts'][] = ':fantasma';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/ghost.gif' . '" alt="' . ':fantasma' . '" />';

	$b['texts'][] = ':momia';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/mummy.gif' . '" alt="' . ':momia' . '" />';

#Food smileys

	$b['texts'][] = ':mazana';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/apple.gif' . '" alt="' . ':mazana' . '" />';

	$b['texts'][] = ':brócoli';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/broccoli.gif' . '" alt="' . ':brócoli' . '" />';

	$b['texts'][] = ':pastel';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/cake.gif' . '" alt="' . ':pastel' . '" />';

	$b['texts'][] = ':zanahoria';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/carrot.gif' . '" alt="' . ':zanahoria' . '" />';

	$b['texts'][] = ':palomitas';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/popcorn.gif' . '" alt="' . ':palomitas' . '" />';

	$b['texts'][] = ':tomate';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/tomato.gif' . '" alt="' . ':tomate' . '" />';

	$b['texts'][] = ':plátano';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/banana.gif' . '" alt="' . ':plátano' . '" />';

	$b['texts'][] = ':cocinar';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/cooking.gif' . '" alt="' . ':cocinar' . '" />';

	$b['texts'][] = ':huevofrito';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/fryegg.gif' . '" alt="' . ':huevofrito' . '" />';

#Happy smileys

	$b['texts'][] = ':cloud9';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/happy/cloud9.gif' . '" alt="' . ':cloud9' . '" />';

	$b['texts'][] = ':tearsofjoy';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/happy/tearsofjoy.gif' . '" alt="' . ':tearsofjoy' . '" />';

#Repsect smileys

	$b['texts'][] = ':reverencia';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/bow.gif' . '" alt="' . ':reverencia' . '" />';

	$b['texts'][] = ':bravo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/bravo.gif' . '" alt="' . ':bravo' . '" />';

	$b['texts'][] = ':vivaelrey';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/hailking.gif' . '" alt="' . ':vivaelrey' . '" />';

	$b['texts'][] = ':número1';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/number1.gif' . '" alt="' . ':número1' . '" />';

#Laugh smileys

	$b['texts'][] = ':jajaja';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/laugh/hahaha.gif' . '" alt="' . ':jajaja' . '" />';

	$b['texts'][] = ':jajatv';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/laugh/loltv.gif' . '" alt="' . ':jajatv' . '" />';

	$b['texts'][] = ':meparto';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/laugh/rofl.gif' . '" alt="' . ':meparto' . '" />';

#Music smileys

	$b['texts'][] = ':dj';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/dj.gif' . '" alt="' . ':dj' . '" />';

	$b['texts'][] = ':batería';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/drums.gif' . '" alt="' . ':batería' . '" />';

	$b['texts'][] = ':elvis';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/elvis.gif' . '" alt="' . ':elivs' . '" />';

	$b['texts'][] = ':guitarra';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/guitar.gif' . '" alt="' . ':guitarra' . '" />';

	$b['texts'][] = ':trompeta';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/trumpet.gif' . '" alt="' . ':trompeta' . '" />';

	$b['texts'][] = ':violín';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/violin.gif' . '" alt="' . ':violín' . '" />';

#Smileys that used to be in core

	$b['texts'][] = ':cabezagolpe';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/headbang.gif' . '" alt="' . ':cabezagolpe' . '" />';

		$b['texts'][] = ':barba';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/beard.png' . '" alt="' . ':barba' . '" />';

	$b['texts'][] = ':barbablanca';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/whitebeard.png' . '" alt="' . ':barbablanca' . '" />';

	$b['texts'][] = ':saludosurf';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':saludosurf' . '" />';

	$b['texts'][] = ':\\.../';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':\\.../' . '" />';

	$b['texts'][] = ':\\ooo/';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/shaka.gif' . '" alt="' . ':\\ooo/' . '" />';

	$b['texts'][] = ':cabezamesa';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/headbang.gif' . '" alt="' . ':cabezamesa' . '" />';

#These two are still in core, so oldcore isn't strictly right, but we don't want too many directories

	$b['texts'][] = ':-d';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/laughing.gif' . '" alt="' . ':-d' . '" />';

	$b['texts'][] = ':-o';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/surprised.gif' . '" alt="' . ':-o' . '" />';




}
