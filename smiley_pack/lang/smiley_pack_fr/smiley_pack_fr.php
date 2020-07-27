<?php
/*
 * Name: Smiley Pack (Français)
 * Description: Pack of smileys that make master too AOLish.
 * Version: 1.01
 * Author: Thomas Willingham (based on Mike Macgirvin's Adult Smile template) 
 * All smileys from sites offering them as Public Domain
 * 
 * 
 */
use Friendica\Core\Hook;
use Friendica\DI;

function smiley_pack_fr_install() {
	Hook::register('smilie', 'addon/smiley_pack_fr/smiley_pack_fr.php', 'smiley_pack_fr_smilies');
}

function smiley_pack_fr_smilies(&$a,&$b) {

#Smileys are split into various directories by the intended range of emotions.  This is in case we get too big and need to modularise things.  We can then cut and paste the right lines, move the right directory, and just change the name of the addon to happy_smilies or whatever.

#Be careful with invocation strings.  If you have a smiley called foo, and another called foobar, typing :foobar will call foo.  Avoid this with clever naming, using ~ instead of : 
#when all else fails.



#Animal smileys.

	$b['texts'][] = ':fleurslapin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bunnyflowers.gif' . '" alt="' . ':fleurslapin' . '" />';

	$b['texts'][] = ':poussin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':poussin' . '" />';

	$b['texts'][] = ':bourdon';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bee.gif' . '" alt="' . ':bourdon' . '" />';

	$b['texts'][] = ':coccinelle';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/ladybird.gif' . '" alt="' . ':coccinelle' . '" />';

	$b['texts'][] = ':araignée';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bigspider.gif' . '" alt="' . ':araignée' . '" />';

	$b['texts'][] = ':chat';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/cat.gif' . '" alt="' . ':chat' . '" />';

	$b['texts'][] = ':lapin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/bunny.gif' . '" alt="' . ':lapin' . '" />';

	$b['texts'][] = ':poussin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/chick.gif' . '" alt="' . ':poussin' . '" />';

	$b['texts'][] = ':vache';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/cow.gif' . '" alt="' . ':vache' . '" />';
    
	$b['texts'][] = ':crabe';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/crab.gif' . '" alt="' . ':crabe' . '" />';

	$b['texts'][] = ':dauphin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dolphin.gif' . '" alt="' . ':dauphin' . '" />';

	$b['texts'][] = ':libellule';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dragonfly.gif' . '" alt="' . ':libellule' . '" />';

	$b['texts'][] = ':grenouille';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/frog.gif' . '" alt="' . ':grenouille' . '" />';

	$b['texts'][] = ':singe';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/monkey.gif' . '" alt="' . ':singe' . '" />';

	$b['texts'][] = ':cheval';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/horse.gif' . '" alt="' . ':cheval' . '" />';
  
	$b['texts'][] = ':perroquet';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/parrot.gif' . '" alt="' . ':perroquet' . '" />';

	$b['texts'][] = ':escargot';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/snail.gif' . '" alt="' . ':escargot' . '" />';

	$b['texts'][] = ':mouton';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/sheep.gif' . '" alt="' . ':mouton' . '" />';

	$b['texts'][] = ':chien';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/dog.gif' . '" alt="' . ':chien' . '" />';

	$b['texts'][] = ':éléphant';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/elephant.gif' . '" alt="' . ':éléphant' . '" />';

	$b['texts'][] = ':poisson';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/fish.gif' . '" alt="' . ':poisson' . '" />';

	$b['texts'][] = ':girafe';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/giraffe.gif' . '" alt="' . ':girafe' . '" />';

	$b['texts'][] = ':cochon';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/animals/pig.gif' . '" alt="' . ':cochon' . '" />';



#Baby Smileys

	$b['texts'][] = ':bébé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/baby.gif' . '" alt="' . ':bébé' . '" />';

	$b['texts'][] = ':litbébé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/babycot.gif' . '" alt="' . ':litbébé' . '" />';
	

	$b['texts'][] = ':enceinte';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/pregnant.gif' . '" alt="' . ':enceinte' . '" />';

	$b['texts'][] = ':cigogne';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/babies/stork.gif' . '" alt="' . ':cigogne' . '" />';


#Confused Smileys	
	$b['texts'][] = ':paumé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/confused.gif' . '" alt="' . ':paumé' . '" />';
    
	$b['texts'][] = ':hausseépaules';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/shrug.gif' . '" alt="' . ':hausseépaules' . '" />';

	$b['texts'][] = ':stupide';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/stupid.gif' . '" alt="' . ':stupide' . '" />';

	$b['texts'][] = ':hébété';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/confused/dazed.gif' . '" alt="' . ':hébété' . '" />';


#Cool Smileys

	$b['texts'][] = ':afro';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/cool/affro.gif' . '" alt="' . ':afro' . '" />';

#Devil/Angel Smileys

	$b['texts'][] = ':ange';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/angel.gif' . '" alt="' . ':ange' . '" />';

	$b['texts'][] = ':chérubin';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/cherub.gif' . '" alt="' . ':chérubin' . '" />';

	$b['texts'][] = ':démonange';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/blondedevil.gif' . '" alt="' . ':démonange' . '" />';

	$b['texts'][] = ':diablechat';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/catdevil.gif' . '" alt="' . ':diablechat' . '" />';

	$b['texts'][] = ':démoniaque';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/devil.gif' . '" alt="' . ':démoniaque' . '" />';
	
	$b['texts'][] = ':bascule';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/daseesaw.gif' . '" alt="' . ':bascule' . '" />';

	$b['texts'][] = ':possédé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/turnevil.gif' . '" alt="' . ':possédé' . '" />';
	
	$b['texts'][] = ':tombe';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/devilangel/graveside.gif' . '" alt="' . ':tombe' . '" />';

#Unpleasent smileys.

	$b['texts'][] = ':toilettes';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/toilet.gif' . '" alt="' . ':toilettes' . '" />';

	$b['texts'][] = ':pèteaulit';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/fartinbed.gif' . '" alt="' . ':pèteaulit' . '" />';

	$b['texts'][] = ':pet';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/disgust/fartblush.gif' . '" alt="' . ':pet' . '" />';

#Drinks

	$b['texts'][] = ':thé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/drink/tea.gif' . '" alt="' . ':thé' . '" />';

	$b['texts'][] = ':salive';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/drool/drool.gif' . '" alt="' . ':salive' . '" />';

#Sad smileys

	$b['texts'][] = ':pleure';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/crying.png' . '" alt="' . ':pleure' . '" />';

	$b['texts'][] = ':prisonnier';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/prisoner.gif' . '" alt="' . ':prisonnier' . '" />';

	$b['texts'][] = ':soupir';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sad/sigh.gif' . '" alt="' . ':soupir' . '" />';

#Smoking - only one smiley in here, maybe it needs moving elsewhere?

	$b['texts'][] = ':fume';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/smoking/smoking.gif' . '" alt="' . ':fume' . '" />';

#Sport smileys

	$b['texts'][] = ':basket';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/basketball.gif' . '" alt="' . ':basket' . '" />';

	$b['texts'][] = ':vélo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/cycling.gif' . '" alt="' . ':vélo' . '" />';

	$b['texts'][] = ':fléchettes';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/darts.gif' . '" alt="' . ':fléchettes' . '" />';

	$b['texts'][] = ':escrime';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/fencing.gif' . '" alt="' . ':escrime' . '" />';

	$b['texts'][] = ':jonglage';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/juggling.gif' . '" alt="' . ':jonglage' . '" />';

	$b['texts'][] = ':sautàlacorde';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/skipping.gif' . '" alt="' . ':sautàlacorde' . '" />';

	$b['texts'][] = ':arc';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/archery.gif' . '" alt="' . ':arc' . '" />';

	$b['texts'][] = ':surf';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/surfing.gif' . '" alt="' . ':surf' . '" />';

	$b['texts'][] = ':billard';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/snooker.gif' . '" alt="' . ':billard' . '" />';
  
	$b['texts'][] = ':équitation';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/sport/horseriding.gif' . '" alt="' . ':équitation' . '" />';

#Love smileys

	$b['texts'][] = ':jetaime';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/iloveyou.gif' . '" alt="' . ':jetaime' . '" />';

	$b['texts'][] = ':amoureux';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/inlove.gif' . '" alt="' . ':amoureux' . '" />';

	$b['texts'][] = ':oursamour';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/lovebear.gif' . '" alt="' . ':oursamour' . '" />';

	$b['texts'][] = ':amourlit';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/lovebed.gif' . '" alt="' . ':amourlit' . '" />';

	$b['texts'][] = ':coeur';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/love/loveheart.gif' . '" alt="' . ':coeur' . '" />';

#Tired/Sleep smileys

	$b['texts'][] = ':comptemoutons';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/countsheep.gif' . '" alt="' . ':comptemoutons' . '" />';

	$b['texts'][] = ':hamac';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/hammock.gif' . '" alt="' . ':hamac' . '" />';

	$b['texts'][] = ':oreiller';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/pillow.gif' . '" alt="' . ':oreiller' . '" />';

	$b['texts'][] = ':bâille';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/tired/yawn.gif' . '" alt="' . ':bâille' . '" />';

#Fight/Flame/Violent smileys

	$b['texts'][] = ':2pistolets';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/2guns.gif' . '" alt="' . ':2pistolets' . '" />';

	$b['texts'][] = ':combatalien';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/alienfight.gif' . '" alt="' . ':combatalien' . '" />';

	$b['texts'][] = ':armée';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/army.gif' . '" alt="' . ':armée' . '" />';

	$b['texts'][] = ':flèche';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/arrowhead.gif' . '" alt="' . ':flèche' . '" />';

	$b['texts'][] = ':bfg';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/bfg.gif' . '" alt="' . ':bfg' . '" />';

	$b['texts'][] = ':archer';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/bowman.gif' . '" alt="' . ':archer' . '" />';

	$b['texts'][] = ':tronçonneuse';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/chainsaw.gif' . '" alt="' . ':tronçonneuse' . '" />';

	$b['texts'][] = ':arbalète';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/crossbow.gif' . '" alt="' . ':arbalète' . '" />';

	$b['texts'][] = ':croisé';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/crusader.gif' . '" alt="' . ':croisé' . '" />';

	$b['texts'][] = ':mort';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/dead.gif' . '" alt="' . ':mort' . '" />';

	$b['texts'][] = ':marteau';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/hammersplat.gif' . '" alt="' . ':marteau' . '" />';

	$b['texts'][] = ':pistoletlaser';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/lasergun.gif' . '" alt="' . ':pistoletlaser' . '" />';

	$b['texts'][] = ':mitrailleuse';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/machinegun.gif' . '" alt="' . ':mitrailleuse' . '" />';

	$b['texts'][] = ':acide';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fight/acid.gif' . '" alt="' . ':acide' . '" />';

#Fantasy smileys - monsters and dragons fantasy.  The other type of fantasy belongs in adult smileys

	$b['texts'][] = ':monstrealien';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/alienmonster.gif' . '" alt="' . ':monstrealien' . '" />';

	$b['texts'][] = ':barbare';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/barbarian.gif' . '" alt="' . ':barbare' . '" />';

	$b['texts'][] = ':dinosaure';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/dinosaur.gif' . '" alt="' . ':dinosaure' . '" />';

	$b['texts'][] = ':petitdragon';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/dragonwhelp.gif' . '" alt="' . ':petitdragon' . '" />';

	$b['texts'][] = ':fantôme';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/ghost.gif' . '" alt="' . ':fantôme' . '" />';

	$b['texts'][] = ':momie';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/fantasy/mummy.gif' . '" alt="' . ':momie' . '" />';

#Food smileys

	$b['texts'][] = ':pomme';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/apple.gif' . '" alt="' . ':pomme' . '" />';

	$b['texts'][] = ':brocoli';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/broccoli.gif' . '" alt="' . ':brocoli' . '" />';

	$b['texts'][] = ':gâteau';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/cake.gif' . '" alt="' . ':gâteau' . '" />';

	$b['texts'][] = ':carotte';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/carrot.gif' . '" alt="' . ':carotte' . '" />';

	$b['texts'][] = '~popcorn';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/popcorn.gif' . '" alt="' . '~popcorn' . '" />';

	$b['texts'][] = ':tomate';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/tomato.gif' . '" alt="' . ':tomate' . '" />';

	$b['texts'][] = ':banane';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/banana.gif' . '" alt="' . ':banane' . '" />';

	$b['texts'][] = ':cuisine';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/cooking.gif' . '" alt="' . ':cuisine' . '" />';

	$b['texts'][] = ':oeufauplat';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/food/fryegg.gif' . '" alt="' . ':oeufauplat' . '" />';

#Happy smileys

	$b['texts'][] = ':nuage';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/happy/cloud9.gif' . '" alt="' . ':nuage' . '" />';

	$b['texts'][] = ':larmesdejoie';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/happy/tearsofjoy.gif' . '" alt="' . ':larmesdejoie' . '" />';

#Repsect smileys

	$b['texts'][] = ':courbette';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/bow.gif' . '" alt="' . ':courbette' . '" />';

	$b['texts'][] = ':bravo';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/bravo.gif' . '" alt="' . ':bravo' . '" />';

	$b['texts'][] = ':viveleroi';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/hailking.gif' . '" alt="' . ':viveleroi' . '" />';

	$b['texts'][] = ':numéro1';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/respect/number1.gif' . '" alt="' . ':numéro1' . '" />';

#Laugh smileys

#Music smileys

	$b['texts'][] = ':batterie';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/drums.gif' . '" alt="' . ':batterie' . '" />';

	$b['texts'][] = ':guitare';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/guitar.gif' . '" alt="' . ':guitare' . '" />';

	$b['texts'][] = ':trompette';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/trumpet.gif' . '" alt="' . ':trompette' . '" />';

	$b['texts'][] = ':violon';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/music/violin.gif' . '" alt="' . ':violon' . '" />';

#Smileys that used to be in core

	$b['texts'][] = ':cognetête';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/headbang.gif' . '" alt="' . ':cognetête' . '" />';

		$b['texts'][] = ':barbu';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/beard.png' . '" alt="' . ':barbu' . '" />';

	$b['texts'][] = ':barbeblanche';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/whitebeard.png' . '" alt="' . ':barbeblanche' . '" />';

	$b['texts'][] = ':tête';
	$b['icons'][] = '<img src="' . DI::baseUrl()->get() . '/addon/smiley_pack/icons/oldcore/headbang.gif' . '" alt="' . ':tête' . '" />';

}
