<?php
/*
 * Name: Unicode Smilies
 * Description: Smilies based on the unicode emojis - On Linux use https://github.com/eosrei/emojione-color-font to see them in color and http://www.unicode.org/emoji/charts/full-emoji-list.html
 * Version: 1.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 * Author: Matthias Ebers <https://loma.ml/profile/one>
 */
use Friendica\Content\Smilies;
use Friendica\Core\Hook;

function unicode_smilies_install() {
		Hook::register('smilie', 'addon/unicode_smilies/unicode_smilies.php', 'unicode_smilies_smilies');
}

function unicode_smilies_uninstall() {
		Hook::unregister('smilie', 'addon/unicode_smilies/unicode_smilies.php', 'unicode_smilies_smilies');
}

function unicode_smilies_smilies(&$a,&$b) {
		Smilies::add($b, ':-)', '&#x1F600;');
		Smilies::add($b, ':)', '&#x1F600;');
		Smilies::add($b, ':-(', '&#x1F641;');
		Smilies::add($b, ':(', '&#x1F641;');
		Smilies::add($b, ':-[', '&#x1F633;');
		Smilies::add($b, ':-D', '&#x1F601;');
		Smilies::add($b, ':D', '&#x1F601;');
		Smilies::add($b, ';-)', '&#x1F609;');
		// Smilies::add($b, ';)', '&#x1F609;'); // Deactivated since this leads to disturbed html entities
		Smilies::add($b, ':-P', '&#x1F61B;');
		Smilies::add($b, ':-p', '&#x1F61B;');
		Smilies::add($b, ':P', '&#x1F61B;');
		Smilies::add($b, ':p', '&#x1F61B;');
		Smilies::add($b, ':-O', '&#x1F62E;');
		Smilies::add($b, ':O', '&#x1F62E;');
		Smilies::add($b, ':-X', '&#x1F910;');
		Smilies::add($b, ':-x', '&#x1F910;');
		Smilies::add($b, ':X', '&#x1F910;');
		Smilies::add($b, ':x', '&#x1F910;');
		Smilies::add($b, '8-)', '&#x1F60E;');
		Smilies::add($b, ':-/', '&#x1F615;');
		Smilies::add($b, ':-"', '&#x1F48F;');
		Smilies::add($b, ':-&quot;', '&#x1F48F;');
		Smilies::add($b, ':-!', '&#x1F912;');
		Smilies::add($b, '&lt;3', '&#x2764;');
		Smilies::add($b, '&lt;/3', '&#x1F494;');
		Smilies::add($b, '&lt;\\3', '&#x1F494;');
		Smilies::add($b, '8-|', '&#x1F632;');
		Smilies::add($b, '8-O', '&#x1F632;');
		Smilies::add($b, '\\o/', '&#x1F44D;');
		Smilies::add($b, ":'(", '&#x1F622;');
		Smilies::add($b, ':coffee', '&#x2615;');
		Smilies::add($b, ':beer', '&#x1F37A;');
		Smilies::add($b, ':homebrew', '&#x1F37A;');
		Smilies::add($b, ':like', '&#x1F44D;');
		Smilies::add($b, ':dislike', '&#x1F44E;');
		Smilies::add($b, ':kaffee', '&#x2615;');
		Smilies::add($b, ':bier', '&#x1F37A;');
		Smilies::add($b, ':mögen', '&#x1F44D;');
		Smilies::add($b, ':nicht mögen', '&#x1F44E;');
//		Smilies::add($b, 'o.O', '&#x;');
//		Smilies::add($b, 'O.o', '&#x;');
//		Smilies::add($b, 'o_O', '&#x;');
//		Smilies::add($b, 'O_o', '&#x;');
//		Smilies::add($b, ':facepalm', '&#x1F926;'); // Bad client support

// Animal smileys.

//		Smilies::add($b, ':bunnyflowers', '&#x;');
		Smilies::add($b, ':chick', '&#x1F424;');
		Smilies::add($b, ':bumblebee', '&#x1F41D;');
		Smilies::add($b, ':ladybird', '&#x1F41E;');
		Smilies::add($b, ':bigspider', '&#x1F577;');
		Smilies::add($b, ':cat', '&#x1F408;');
		Smilies::add($b, ':bunny', '&#x1F430;');
		Smilies::add($b, ':cow', '&#x1F42E;');
		Smilies::add($b, ':crab', '&#x1F980;');
		Smilies::add($b, ':dolphin', '&#x1F42C;');
//		Smilies::add($b, ':dragonfly', '&#x;');
		Smilies::add($b, ':frog', '&#x1F438;');
		Smilies::add($b, ':hamster', '&#x1F439;');
		Smilies::add($b, ':monkey', '&#x1F412;');
		Smilies::add($b, ':horse', '&#x1F434;');
//		Smilies::add($b, ':parrot', '&#x;');
		Smilies::add($b, ':tux', '&#x1F427;');
		Smilies::add($b, ':snail', '&#x1F40C;');
		Smilies::add($b, ':sheep', '&#x1F411;');
		Smilies::add($b, ':dog', '&#x1F436;');
		Smilies::add($b, ':elephant', '&#x1F418;');
		Smilies::add($b, ':fish', '&#x1F41F;');
//		Smilies::add($b, ':giraffe', '&#x1F992;'); // Bad client support
		Smilies::add($b, ':pig', '&#x1F416;');

// Baby Smileys

		Smilies::add($b, ':baby', '&#x1F476;');
//		Smilies::add($b, ':babycot', '&#x;');
//		Smilies::add($b, ':pregnant', '&#x1F930;'); // Bad client support
//		Smilies::add($b, ':stork', '&#x;');

// Confused Smileys

		Smilies::add($b, ':confused', '&#x1F615;');
		Smilies::add($b, ':shrug', '&#x1F937;');
//		Smilies::add($b, ':stupid', '&#x;');
//		Smilies::add($b, ':dazed', '&#x;');

// Cool Smileys

//		Smilies::add($b, ':affro', '&#x;');

// Devil/Angel Smileys

		Smilies::add($b, ':angel', '&#x1F47C;');
		Smilies::add($b, ':cherub', '&#x1F47C;');
//		Smilies::add($b, ':devilangel', '&#x;');
//		Smilies::add($b, ':catdevil', '&#x;');
//		Smilies::add($b, ':devillish', '&#x;');
//		Smilies::add($b, ':daseesaw', '&#x;');
//		Smilies::add($b, ':turnevil', '&#x;');
//		Smilies::add($b, ':saint', '&#x;');
//		Smilies::add($b, ':graveside', '&#x;');

// Unpleasent smileys.

		Smilies::add($b, ':toilet', '&#x1F6BD;');
//		Smilies::add($b, ':fartinbed', '&#x;');
//		Smilies::add($b, ':fartblush', '&#x;');

// Sad smileys

		Smilies::add($b, ':crying', '&#x1F622;');
//		Smilies::add($b, ':prisoner', '&#x;');
//		Smilies::add($b, ':sigh', '&#x;');

// Sport smileys

		Smilies::add($b, ':basketball', '&#x1F3C0;');
		Smilies::add($b, ':bowling', '&#x1F3B3;');
		Smilies::add($b, ':cycling', '&#x1F6B4;');
		Smilies::add($b, ':darts', '&#x1F3AF;');
		Smilies::add($b, ':fencing', '&#x1F93A;');
		Smilies::add($b, ':juggling', '&#x1F939;');
//		Smilies::add($b, ':skipping', '&#x;');
//		Smilies::add($b, ':archery', '&#x;');
		Smilies::add($b, ':surfing', '&#x1F3C4;');
		Smilies::add($b, ':snooker', '&#x1F3B1;');
		Smilies::add($b, ':horseriding', '&#x1F3C7;');

// Love smileys

//		Smilies::add($b, ':iloveyou', '&#x;');
//		Smilies::add($b, ':inlove', '&#x;');
//		Smilies::add($b, '~love', '&#x;');
//		Smilies::add($b, ':lovebear', '&#x;');
//		Smilies::add($b, ':lovebed', '&#x;');
		Smilies::add($b, ':loveheart', '&#x1F496;');

// Tired/Sleep smileys

//		Smilies::add($b, ':countsheep', '&#x;');
//		Smilies::add($b, ':hammock', '&#x;');
//		Smilies::add($b, ':pillow', '&#x;');
//		Smilies::add($b, ':yawn', '&#x;');

// Fight/Flame/Violent smileys

//		Smilies::add($b, ':2guns', '&#x;');
//		Smilies::add($b, ':alienfight', '&#x;');
//		Smilies::add($b, ':army', '&#x;');
//		Smilies::add($b, ':arrowhead', '&#x;');
//		Smilies::add($b, ':bfg', '&#x;');
//		Smilies::add($b, ':bowman', '&#x;');
//		Smilies::add($b, ':chainsaw', '&#x;');
//		Smilies::add($b, ':crossbow', '&#x;');
//		Smilies::add($b, ':crusader', '&#x;');
//		Smilies::add($b, ':dead', '&#x;');
//		Smilies::add($b, ':hammersplat', '&#x;');
//		Smilies::add($b, ':lasergun', '&#x;');
//		Smilies::add($b, ':machinegun', '&#x;');
//		Smilies::add($b, ':acid', '&#x;');

// Fantasy smileys - monsters and dragons fantasy.  The other type of fantasy belongs in adult smileys

		Smilies::add($b, ':alienmonster', '&#x1F47E;');
//		Smilies::add($b, ':barbarian', '&#x;');
//		Smilies::add($b, ':dinosaur', '&#x;');
		Smilies::add($b, ':dragon', '&#x1F409;');
		Smilies::add($b, ':draco', '&#x1F409;');
		Smilies::add($b, ':ghost', '&#x1F47B;');
//		Smilies::add($b, ':mummy', '&#x;');

// Food smileys

		Smilies::add($b, ':apple', '&#x1F34E;');
//		Smilies::add($b, ':broccoli', '&#x;');
		Smilies::add($b, ':cake', '&#x1F370;');
//		Smilies::add($b, ':carrot', '&#x1F955;'); // Bad client support
		Smilies::add($b, ':popcorn', '&#x1F37F;');
		Smilies::add($b, ':tomato', '&#x1F345;');
		Smilies::add($b, ':banana', '&#x1F34C;');
		Smilies::add($b, ':cooking', '&#x1F373;');
		Smilies::add($b, ':fryegg', '&#x1F373;');
		Smilies::add($b, ':birthday cake', '&#x1F382;');

// Happy smileys

//		Smilies::add($b, ':cloud9', '&#x;');
		Smilies::add($b, ':tearsofjoy', '&#x1F602;');

// Respect smileys

		Smilies::add($b, ':bow', '&#x1F647;');
//		Smilies::add($b, ':bravo', '&#x;');
//		Smilies::add($b, ':hailking', '&#x;');
//		Smilies::add($b, ':number1', '&#x;');

// Laugh smileys

//		Smilies::add($b, ':hahaha', '&#x;');
//		Smilies::add($b, ':loltv', '&#x;');
//		Smilies::add($b, ':rofl', '&#x1F923;'); // Bad client support
// Music smileys

//		Smilies::add($b, ':drums', '&#x1F941;'); // Bad client support
		Smilies::add($b, ':guitar', '&#x1F3B8;');
		Smilies::add($b, ':trumpet', '&#x1F3BA;');

// Smileys that used to be in core

//		Smilies::add($b, ':headbang', '&#x;');
//		Smilies::add($b, ':beard', '&#x1F9D4;'); // Bad client support
//		Smilies::add($b, ':whitebeard', '&#x;');
//		Smilies::add($b, ':shaka', '&#x;');
//		Smilies::add($b, ':\\.../', '&#x;');
//		Smilies::add($b, ':\\ooo/', '&#x;');
//		Smilies::add($b, ':headdesk', '&#x;');

// These two are still in core, so oldcore isn't strictly right, but we don't want too many directories

//		Smilies::add($b, ':-d', '&#x;');
		Smilies::add($b, ':-o', '&#x1F62E;');

//  Regex killers - stick these at the bottom so they appear at the end of the English and
//  at the start of $OtherLanguage.

// Drinks

		Smilies::add($b, ':tea', '&#x2615;');
		Smilies::add($b, ':tee', '&#x2615;');
//		Smilies::add($b, ':drool', '&#x1F924;'); // Bad client support

		Smilies::add($b, ':cool', '&#x1F192;');
//		Smilies::add($b, ':vomit', '&#x1F92E;'); // Bad client support
		Smilies::add($b, ':golf', '&#x1F3CC;');
		Smilies::add($b, ':football', '&#x1F3C8;');
		Smilies::add($b, ':tennis', '&#x1F3BE;');
//		Smilies::add($b, ':alpha', '&#x;');
//		Smilies::add($b, ':marine', '&#x;');
		Smilies::add($b, ':sabre', '&#x1F5E1;');
//		Smilies::add($b, ':tank', '&#x;');
//		Smilies::add($b, ':viking', '&#x;');
//		Smilies::add($b, ':gangs', '&#x;');
//		Smilies::add($b, ':dj', '&#x;');
//		Smilies::add($b, ':elvis', '&#x;');
		Smilies::add($b, ':violin', '&#x1F3BB;');


// Neu hinzugefügte Unicode Emoji von Matthias Ebers
// face-smiling
		Smilies::add($b, ':grinning face', '&#x1F600');
		Smilies::add($b, ':grinning face with big eyes', '&#x1F603');
		Smilies::add($b, ':grinning face with smiling eyes', '&#x1F604');
		Smilies::add($b, ':beaming face with smiling eyes', '&#x1F601');
		Smilies::add($b, ':grinning squinting face', '&#x1F606');
		Smilies::add($b, ':laughing', '&#x1F606');
		Smilies::add($b, ':grinning face with sweat', '&#x1F605');
		Smilies::add($b, ':rolling on the floor laughing', '&#x1F923');
		Smilies::add($b, ':face with tears of joy', '&#x1F602');
		Smilies::add($b, ':slightly smiling face', '&#x1F642');
		Smilies::add($b, ':upside-down face', '&#x1F643');
		Smilies::add($b, ':winking face', '&#x1F609');
		Smilies::add($b, ':smiling face with smiling eyes', '&#x1F60A');
		Smilies::add($b, ':smiling face with halo', '&#x1F607');
    
// face-affection
		Smilies::add($b, ':smiling face with hearts', '&#x1F970');
		Smilies::add($b, ':smiling face with heart-eyes', '&#x1F60D');
		Smilies::add($b, ':star-struck', '&#x1F929');
		Smilies::add($b, ':face blowing a kiss', '&#x1F618');
		Smilies::add($b, ':kissing face', '&#x1F617');
		Smilies::add($b, ':smiling face', '&#x263A');
		Smilies::add($b, ':kissing face with closed eyes', '&#x1F61A');
		Smilies::add($b, ':kissing face with smiling eyes', '&#x1F619');
    
// face-tongue
		Smilies::add($b, ':face savoring food', '&#x1F60B');
		Smilies::add($b, ':face with tongue', '&#x1F61B');
		Smilies::add($b, ':winking face with tongue', '&#x1F61C');
		Smilies::add($b, ':zany face', '&#x1F92A');
		Smilies::add($b, ':squinting face with tongue', '&#x1F61D');
		Smilies::add($b, ':money-mouth face', '&#x1F911');
    
// face-hand
		Smilies::add($b, ':hugging face', '&#x1F917');
		Smilies::add($b, ':face with hand over mouth', '&#x1F92D');
		Smilies::add($b, ':shushing face', '&#x1F92B');
		Smilies::add($b, ':thinking face', '&#x1F914');

//   face-neutral-skeptical
		Smilies::add($b, ':zipper-mouth face', '&#x1F910');
		Smilies::add($b, ':face with raised eyebrow', '&#x1F928');
		Smilies::add($b, ':neutral face', '&#x1F610');
		Smilies::add($b, ':expressionless face', '&#x1F611');
		Smilies::add($b, ':face without mouth', '&#x1F636');
		Smilies::add($b, ':smirking face', '&#x1F60F');
		Smilies::add($b, ':unamused face', '&#x1F612');
		Smilies::add($b, ':face with rolling eyes', '&#x1F644');
		Smilies::add($b, ':grimacing face', '&#x1F62C');
		Smilies::add($b, ':lying face', '&#x1F925');
    
// face-sleepy
		Smilies::add($b, ':relieved face', '&#x1F60C');
		Smilies::add($b, ':pensive face', '&#x1F614');
		Smilies::add($b, ':sleepy face', '&#x1F62A');
		Smilies::add($b, ':drooling face', '&#x1F924');
		Smilies::add($b, ':sleeping face', '&#x1F634');

// face-unwell
		Smilies::add($b, ':face with medical mask', '&#x1F637');
		Smilies::add($b, ':face with thermometer', '&#x1F912');
		Smilies::add($b, ':face with head-bandage', '&#x1F915');
		Smilies::add($b, ':nauseated face', '&#x1F922');
		Smilies::add($b, ':face vomiting', '&#x1F92E');
		Smilies::add($b, ':sneezing face', '&#x1F927');
		Smilies::add($b, ':hot face', '&#x1F975');
		Smilies::add($b, ':cold face', '&#x1F976');
		Smilies::add($b, ':woozy face', '&#x1F974');
		Smilies::add($b, ':dizzy face', '&#x1F635');
		Smilies::add($b, ':exploding head', '&#x1F92F');
    
// face-hat
		Smilies::add($b, ':cowboy hat face', '&#x1F920');
		Smilies::add($b, ':partying face', '&#x1F973');
    
// face-glasses
		Smilies::add($b, ':smiling face with sunglasses', '&#x1F60E');
		Smilies::add($b, ':nerd face', '&#x1F913');
		Smilies::add($b, ':face with monocle', '&#x1F9D0');
    
// face-concerned
		Smilies::add($b, ':confused face', '&#x1F615');
		Smilies::add($b, ':worried face', '&#x1F61F');
		Smilies::add($b, ':slightly frowning face', '&#x1F641');
		Smilies::add($b, ':frowning face', '&#x2639');
		Smilies::add($b, ':face with open mouth', '&#x1F62E');
		Smilies::add($b, ':hushed face', '&#x1F62F');
		Smilies::add($b, ':astonished face', '&#x1F632');
		Smilies::add($b, ':flushed face', '&#x1F633');
		Smilies::add($b, ':pleading face', '&#x1F97A');
		Smilies::add($b, ':frowning face with open mouth', '&#x1F626');
		Smilies::add($b, ':anguished face', '&#x1F627');
		Smilies::add($b, ':fearful face', '&#x1F628');
		Smilies::add($b, ':anxious face with sweat', '&#x1F630');
		Smilies::add($b, ':sad but relieved face', '&#x1F625');
		Smilies::add($b, ':crying face', '&#x1F622');
		Smilies::add($b, ':loudly crying face', '&#x1F62D');
		Smilies::add($b, ':face screaming in fear', '&#x1F631');
		Smilies::add($b, ':confounded face', '&#x1F616');
		Smilies::add($b, ':persevering face', '&#x1F623');
		Smilies::add($b, ':disappointed face', '&#x1F61E');
    
// face-negative
		Smilies::add($b, ':face with steam from nose', '&#x1F624');
		Smilies::add($b, ':pouting face', '&#x1F621');
		Smilies::add($b, ':angry face', '&#x1F620');
		Smilies::add($b, ':face with symbols on mouth', '&#x1F92C');
		Smilies::add($b, ':smiling face with horns', '&#x1F608');
		Smilies::add($b, ':angry face with horns', '&#x1F47F');
		Smilies::add($b, ':skull', '&#x1F480');
		Smilies::add($b, ':skull and crossbones', '&#x2620');
    
// face-costume
		Smilies::add($b, ':pile of poo', '&#x1F4A9');
		Smilies::add($b, ':clown face', '&#x1F921');
		Smilies::add($b, ':ogre', '&#x1F479');
		Smilies::add($b, ':goblin', '&#x1F47A');
		Smilies::add($b, ':ghost', '&#x1F47B');
		Smilies::add($b, ':alien', '&#x1F47D');
		Smilies::add($b, ':alien monster', '&#x1F47E');
		Smilies::add($b, ':robot', '&#x1F916');

// cat-face
		Smilies::add($b, ':grinning cat', '&#x1F63A');
		Smilies::add($b, ':grinning cat with smiling eyes', '&#x1F638');
		Smilies::add($b, ':cat with tears of joy', '&#x1F639');
		Smilies::add($b, ':smiling cat with heart-eyes', '&#x1F63B');
		Smilies::add($b, ':cat with wry smile', '&#x1F63C');
		Smilies::add($b, ':kissing cat', '&#x1F63D');
		Smilies::add($b, ':weary cat', '&#x1F640');
		Smilies::add($b, ':crying cat', '&#x1F63F');
		Smilies::add($b, ':pouting cat', '&#x1F63E');
    
// monkey-face
		Smilies::add($b, ':see-no-evil monkey', '&#x1F648');
		Smilies::add($b, ':hear-no-evil monkey', '&#x1F649');
		Smilies::add($b, ':speak-no-evil monkey', '&#x1F64A');
    
//emotion
		Smilies::add($b, ':kiss mark', '&#x1F48B');
		Smilies::add($b, ':love letter', '&#x1F48C');
		Smilies::add($b, ':heart with arrow', '&#x1F498');
		Smilies::add($b, ':heart with ribbon', '&#x1F49D');
		Smilies::add($b, ':sparkling heart', '&#x1F496');
		Smilies::add($b, ':growing heart', '&#x1F497');
		Smilies::add($b, ':beating heart', '&#x1F493');
		Smilies::add($b, ':revolving hearts', '&#x1F49E');
		Smilies::add($b, ':two hearts', '&#x1F495');
		Smilies::add($b, ':heart decoration', '&#x1F49F');
		Smilies::add($b, ':heart exclamation', '&#x2763');
		Smilies::add($b, ':broken heart', '&#x1F494');
		Smilies::add($b, ':red heart', '&#x2764');
		Smilies::add($b, ':orange heart', '&#x1F9E1');
		Smilies::add($b, ':yellow heart', '&#x1F49B');
		Smilies::add($b, ':green heart', '&#x1F49A');
		Smilies::add($b, ':blue heart', '&#x1F499');
		Smilies::add($b, ':purple heart', '&#x1F49C');
//  		Smilies::add($b, ':brown heart', '&#x1F90E');
    		Smilies::add($b, ':black heart', '&#x1F5A4');
//    		Smilies::add($b, ':white heart', '&#x1F90D');
		Smilies::add($b, ':hundred points', '&#x1F4AF');
		Smilies::add($b, ':anger symbol', '&#x1F4A2');
		Smilies::add($b, ':collision', '&#x1F4A5');
		Smilies::add($b, ':dizzy', '&#x1F4AB');
		Smilies::add($b, ':sweat droplets', '&#x1F4A6');
		Smilies::add($b, ':dashing away', '&#x1F4A8');
		Smilies::add($b, ':hole', '&#x1F573');
		Smilies::add($b, ':bomb', '&#x1F4A3');
		Smilies::add($b, ':speech balloon', '&#x1F4AC');
		Smilies::add($b, ':left speech bubble', '&#x1F5E8');
		Smilies::add($b, ':right anger bubble', '&#x1F5EF');
		Smilies::add($b, ':thought balloon', '&#x1F4AD');
		Smilies::add($b, ':zzz', '&#x1F4A4');
    
// People & Body
// hand-fingers-open
		Smilies::add($b, ':waving hand', '&#x1F44B');
		Smilies::add($b, ':raised back of hand', '&#x1F91A');
		Smilies::add($b, ':hand with fingers splayed', '&#x1F590');
		Smilies::add($b, ':raised hand', '&#x270B');
		Smilies::add($b, ':vulcan salute', '&#x1F596');

// hand-fingers-partial
		Smilies::add($b, ':OK hand', '&#x1F44C');
//  	Smilies::add($b, ':pinching hand', '&#x1F90F');
		Smilies::add($b, ':victory hand', '&#x270C');
		Smilies::add($b, ':crossed fingers', '&#x1F91E');
		Smilies::add($b, ':love-you gesture', '&#x1F91F');
		Smilies::add($b, ':sign of the horns', '&#x1F918');
		Smilies::add($b, ':call me hand', '&#x1F919');

// hand-single-finger
		Smilies::add($b, ':backhand index pointing left', '&#x1F448');
		Smilies::add($b, ':backhand index pointing right', '&#x1F449');
		Smilies::add($b, ':backhand index pointing up', '&#x1F446');
		Smilies::add($b, ':middle finger', '&#x1F595');
		Smilies::add($b, ':backhand index pointing down', '&#x1F447');
		Smilies::add($b, ':index pointing up', '&#x261D');

// hand-fingers-closed
		Smilies::add($b, ':thumbs up', '&#x1F44D');
		Smilies::add($b, ':thumbs down', '&#x1F44E');
		Smilies::add($b, ':raised fist', '&#x270A');
		Smilies::add($b, ':oncoming fist', '&#x1F44A');
		Smilies::add($b, ':left-facing fist', '&#x1F91B');
		Smilies::add($b, ':right-facing fist', '&#x1F91C');

// hands
		Smilies::add($b, ':clapping hands', '&#x1F44F');
		Smilies::add($b, ':raising hands', '&#x1F64C');
		Smilies::add($b, ':open hands', '&#x1F450');
		Smilies::add($b, ':palms up together', '&#x1F932');
		Smilies::add($b, ':handshake', '&#x1F91D');
		Smilies::add($b, ':folded hands', '&#x1F64F');

// hand-prop
		Smilies::add($b, ':writing hand', '&#x270D');
		Smilies::add($b, ':nail polish', '&#x1F485');
		Smilies::add($b, ':selfie', '&#x1F933');

// body-parts
    		Smilies::add($b, ':flexed biceps', '&#x1F4AA');
//  		Smilies::add($b, ':mechanical arm', '&#x1F9BE');
//  		Smilies::add($b, ':mechanical leg', '&#x1F9BF');
		Smilies::add($b, ':leg', '&#x1F9B5');
		Smilies::add($b, ':foot', '&#x1F9B6');
		Smilies::add($b, ':ear', '&#x1F442');
//  		Smilies::add($b, ':ear with hearing aid', '&#x1F9BB');
		Smilies::add($b, ':nose', '&#x1F443');
		Smilies::add($b, ':brain', '&#x1F9E0');
		Smilies::add($b, ':tooth', '&#x1F9B7');
		Smilies::add($b, ':bone', '&#x1F9B4');
		Smilies::add($b, ':eyes', '&#x1F440');
		Smilies::add($b, ':eye', '&#x1F441');
		Smilies::add($b, ':tongue', '&#x1F445');
		Smilies::add($b, ':mouth', '&#x1F444');

// person
		Smilies::add($b, ':baby', '&#x1F476');
		Smilies::add($b, ':child', '&#x1F9D2');
		Smilies::add($b, ':boy', '&#x1F466');
		Smilies::add($b, ':girl', '&#x1F467');
		Smilies::add($b, ':person', '&#x1F9D1');
		Smilies::add($b, ':person: blond hair', '&#x1F471');
		Smilies::add($b, ':man', '&#x1F468');
		Smilies::add($b, ':man: beard', '&#x1F9D4');
		Smilies::add($b, ':man: red hair', '&#x1F468&#x200D&#x1F9B0');
		Smilies::add($b, ':man: curly hair', '&#x1F468&#x200D&#x1F9B1');
		Smilies::add($b, ':man: white hair', '&#x1F468&#x200D&#x1F9B3');
		Smilies::add($b, ':man: bald', '&#x1F468&#x200D&#x1F9B2');
		Smilies::add($b, ':woman', '&#x1F469');
		Smilies::add($b, ':woman: red hair', '&#x1F469&#x200D&#x1F9B0');
		Smilies::add($b, ':⊛ person: red hair', '&#x1F9D1&#x200D&#x1F9B0');
		Smilies::add($b, ':woman: curly hair', '&#x1F469&#x200D&#x1F9B1');
		Smilies::add($b, ':⊛ person: curly hair', '&#x1F9D1&#x200D&#x1F9B1');
		Smilies::add($b, ':woman: white hair', '&#x1F469&#x200D&#x1F9B3');
		Smilies::add($b, ':⊛ person: white hair', '&#x1F9D1&#x200D&#x1F9B3');
		Smilies::add($b, ':woman: bald', '&#x1F469&#x200D&#x1F9B2');
		Smilies::add($b, ':bald', '&#x1F9D1&#x200D&#x1F9B2');
		Smilies::add($b, ':woman: blond hair', '&#x1F471&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':man: blond hair', '&#x1F471&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':older person', '&#x1F9D3');
		Smilies::add($b, ':old man', '&#x1F474');
		Smilies::add($b, ':old woman', '&#x1F475');

// person-gesture
		Smilies::add($b, ':person frowning', '&#x1F64D');
		Smilies::add($b, ':man frowning', '&#x1F64D&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman frowning', '&#x1F64D&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person pouting', '&#x1F64E');
		Smilies::add($b, ':man pouting', '&#x1F64E&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman pouting', '&#x1F64E&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person gesturing NO', '&#x1F645');
		Smilies::add($b, ':man gesturing NO', '&#x1F645&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman gesturing NO', '&#x1F645&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person gesturing OK', '&#x1F646');
		Smilies::add($b, ':man gesturing OK', '&#x1F646&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman gesturing OK', '&#x1F646&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person tipping hand', '&#x1F481');
		Smilies::add($b, ':man tipping hand', '&#x1F481&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman tipping hand', '&#x1F481&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person raising hand', '&#x1F64B');
		Smilies::add($b, ':man raising hand', '&#x1F64B&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman raising hand', '&#x1F64B&#x200D&#x2640&#xFE0F');
//  		Smilies::add($b, ':deaf person', '&#x1F9CF');
//  		Smilies::add($b, ':deaf man', '&#x1F9CF&#x200D&#x2642&#xFE0F');
//  		Smilies::add($b, ':deaf woman', '&#x1F9CF&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person bowing', '&#x1F647');
		Smilies::add($b, ':man bowing', '&#x1F647&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman bowing', '&#x1F647&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person facepalming', '&#x1F926');
		Smilies::add($b, ':man facepalming', '&#x1F926&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman facepalming', '&#x1F926&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':person shrugging', '&#x1F937');
		Smilies::add($b, ':man shrugging', '&#x1F937&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman shrugging', '&#x1F937&#x200D&#x2640&#xFE0F');

// person-role

// person-fantasy
		Smilies::add($b, ':baby angel', '&#x1F47C');
		Smilies::add($b, ':Santa Claus', '&#x1F385');
		Smilies::add($b, ':Mrs. Claus', '&#x1F936');
		Smilies::add($b, ':superhero', '&#x1F9B8');
		Smilies::add($b, ':man superhero', '&#x1F9B8&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman superhero', '&#x1F9B8&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':supervillain', '&#x1F9B9');
		Smilies::add($b, ':man supervillain', '&#x1F9B9&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman supervillain', '&#x1F9B9&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':mage', '&#x1F9D9');
		Smilies::add($b, ':man mage', '&#x1F9D9&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman mage', '&#x1F9D9&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':fairy', '&#x1F9DA');
		Smilies::add($b, ':man fairy', '&#x1F9DA&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman fairy', '&#x1F9DA&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':vampire', '&#x1F9DB');
		Smilies::add($b, ':man vampire', '&#x1F9DB&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman vampire', '&#x1F9DB&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':merperson', '&#x1F9DC');
		Smilies::add($b, ':merman', '&#x1F9DC&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':mermaid', '&#x1F9DC&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':elf', '&#x1F9DD');
		Smilies::add($b, ':man elf', '&#x1F9DD&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman elf', '&#x1F9DD&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':genie', '&#x1F9DE');
		Smilies::add($b, ':man genie', '&#x1F9DE&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman genie', '&#x1F9DE&#x200D&#x2640&#xFE0F');
		Smilies::add($b, ':zombie', '&#x1F9DF');
		Smilies::add($b, ':man zombie', '&#x1F9DF&#x200D&#x2642&#xFE0F');
		Smilies::add($b, ':woman zombie', '&#x1F9DF&#x200D&#x2640&#xFE0F');

// person-activity

// person-sport

// person-resting

// family
		Smilies::add($b, ':people holding hands', '&#x1F9D1&#x200D&#x1F91D&#x200D&#x1F9D1');
		Smilies::add($b, ':women holding hands', '&#x1F46D');
		Smilies::add($b, ':woman and man holding hands', '&#x1F46B');
		Smilies::add($b, ':men holding hands', '&#x1F46C');
		Smilies::add($b, ':kiss', '&#x1F48F');
		Smilies::add($b, ':couple with heart', '&#x1F491');
		Smilies::add($b, ':family', '&#x1F46A');

// person-symbol
		Smilies::add($b, ':speaking head', '&#x1F5E3');
		Smilies::add($b, ':bust in silhouette', '&#x1F464');
		Smilies::add($b, ':busts in silhouette', '&#x1F465');
		Smilies::add($b, ':footprints', '&#x1F463');
    
// Component
// hair-style

// Animals & Nature
// animal-mammal
		Smilies::add($b, ':monkey face', '&#x1F435');
		Smilies::add($b, ':monkey', '&#x1F412');
		Smilies::add($b, ':gorilla', '&#x1F98D');
//  		Smilies::add($b, ':orangutan', '&#x1F9A7');
		Smilies::add($b, ':dog face', '&#x1F436');
		Smilies::add($b, ':dog', '&#x1F415');
//  		Smilies::add($b, ':guide dog', '&#x1F9AE');
		Smilies::add($b, ':poodle', '&#x1F429');
		Smilies::add($b, ':wolf', '&#x1F43A');
		Smilies::add($b, ':fox', '&#x1F98A');
		Smilies::add($b, ':raccoon', '&#x1F99D');
		Smilies::add($b, ':cat face', '&#x1F431');
		Smilies::add($b, ':cat', '&#x1F408');
		Smilies::add($b, ':lion', '&#x1F981');
		Smilies::add($b, ':tiger face', '&#x1F42F');
		Smilies::add($b, ':tiger', '&#x1F405');
		Smilies::add($b, ':leopard', '&#x1F406');
		Smilies::add($b, ':horse face', '&#x1F434');
		Smilies::add($b, ':horse', '&#x1F40E');
		Smilies::add($b, ':unicorn', '&#x1F984');
		Smilies::add($b, ':zebra', '&#x1F993');
		Smilies::add($b, ':deer', '&#x1F98C');
		Smilies::add($b, ':cow face', '&#x1F42E');
		Smilies::add($b, ':ox', '&#x1F402');
		Smilies::add($b, ':water buffalo', '&#x1F403');
		Smilies::add($b, ':cow', '&#x1F404');
		Smilies::add($b, ':pig face', '&#x1F437');
		Smilies::add($b, ':pig', '&#x1F416');
		Smilies::add($b, ':boar', '&#x1F417');
		Smilies::add($b, ':pig nose', '&#x1F43D');
		Smilies::add($b, ':ram', '&#x1F40F');
		Smilies::add($b, ':ewe', '&#x1F411');
		Smilies::add($b, ':goat', '&#x1F410');
		Smilies::add($b, ':camel', '&#x1F42A');
		Smilies::add($b, ':two-hump camel', '&#x1F42B');
		Smilies::add($b, ':llama', '&#x1F999');
		Smilies::add($b, ':giraffe', '&#x1F992');
		Smilies::add($b, ':elephant', '&#x1F418');
		Smilies::add($b, ':rhinoceros', '&#x1F98F');
		Smilies::add($b, ':hippopotamus', '&#x1F99B');
		Smilies::add($b, ':mouse face', '&#x1F42D');
		Smilies::add($b, ':mouse', '&#x1F401');
		Smilies::add($b, ':rat', '&#x1F400');
		Smilies::add($b, ':hamster', '&#x1F439');
		Smilies::add($b, ':rabbit face', '&#x1F430');
		Smilies::add($b, ':rabbit', '&#x1F407');
		Smilies::add($b, ':chipmunk', '&#x1F43F');
		Smilies::add($b, ':hedgehog', '&#x1F994');
		Smilies::add($b, ':bat', '&#x1F987');
		Smilies::add($b, ':bear', '&#x1F43B');
		Smilies::add($b, ':koala', '&#x1F428');
		Smilies::add($b, ':panda', '&#x1F43C');
//  		Smilies::add($b, ':sloth', '&#x1F9A5');
//  		Smilies::add($b, ':otter', '&#x1F9A6');
//  		Smilies::add($b, ':skunk', '&#x1F9A8');
//  		Smilies::add($b, ':kangaroo', '&#x1F998');
		Smilies::add($b, ':badger', '&#x1F9A1');
		Smilies::add($b, ':paw prints', '&#x1F43E');

// animal-bird
		Smilies::add($b, ':turkey', '&#x1F983');
		Smilies::add($b, ':chicken', '&#x1F414');
		Smilies::add($b, ':rooster', '&#x1F413');
		Smilies::add($b, ':hatching chick', '&#x1F423');
		Smilies::add($b, ':baby chick', '&#x1F424');
		Smilies::add($b, ':front-facing baby chick', '&#x1F425');
		Smilies::add($b, ':bird', '&#x1F426');
		Smilies::add($b, ':penguin', '&#x1F427');
		Smilies::add($b, ':dove', '&#x1F54A');
		Smilies::add($b, ':eagle', '&#x1F985');
		Smilies::add($b, ':duck', '&#x1F986');
		Smilies::add($b, ':swan', '&#x1F9A2');
		Smilies::add($b, ':owl', '&#x1F989');
//  		Smilies::add($b, ':flamingo', '&#x1F9A9');
		Smilies::add($b, ':peacock', '&#x1F99A');
		Smilies::add($b, ':parrot', '&#x1F99C');
    
// animal-amphibian
		Smilies::add($b, ':frog', '&#x1F438');

// animal-reptile
		Smilies::add($b, ':crocodile', '&#x1F40A');
		Smilies::add($b, ':turtle', '&#x1F422');
		Smilies::add($b, ':lizard', '&#x1F98E');
		Smilies::add($b, ':snake', '&#x1F40D');
		Smilies::add($b, ':dragon face', '&#x1F432');
		Smilies::add($b, ':dragon', '&#x1F409');
		Smilies::add($b, ':sauropod', '&#x1F995');
		Smilies::add($b, ':T-Rex', '&#x1F996');

// animal-marine
		Smilies::add($b, ':spouting whale', '&#x1F433');
		Smilies::add($b, ':whale', '&#x1F40B');
		Smilies::add($b, ':dolphin', '&#x1F42C');
		Smilies::add($b, ':fish', '&#x1F41F');
		Smilies::add($b, ':tropical fish', '&#x1F420');
		Smilies::add($b, ':blowfish', '&#x1F421');
		Smilies::add($b, ':shark', '&#x1F988');
		Smilies::add($b, ':octopus', '&#x1F419');
		Smilies::add($b, ':spiral shell', '&#x1F41A');

// animal-bug
		Smilies::add($b, ':snail', '&#x1F40C');
		Smilies::add($b, ':butterfly', '&#x1F98B');
		Smilies::add($b, ':bug', '&#x1F41B');
		Smilies::add($b, ':ant', '&#x1F41C');
		Smilies::add($b, ':honeybee', '&#x1F41D');
		Smilies::add($b, ':lady beetle', '&#x1F41E');
		Smilies::add($b, ':cricket', '&#x1F997');
		Smilies::add($b, ':spider', '&#x1F577');
		Smilies::add($b, ':spider web', '&#x1F578');
		Smilies::add($b, ':scorpion', '&#x1F982');
		Smilies::add($b, ':mosquito', '&#x1F99F');
		Smilies::add($b, ':microbe', '&#x1F9A0');

// plant-flower
		Smilies::add($b, ':bouquet', '&#x1F490');
		Smilies::add($b, ':cherry blossom', '&#x1F338');
		Smilies::add($b, ':white flower', '&#x1F4AE');
		Smilies::add($b, ':rosette', '&#x1F3F5');
		Smilies::add($b, ':rose', '&#x1F339');
		Smilies::add($b, ':wilted flower', '&#x1F940');
		Smilies::add($b, ':hibiscus', '&#x1F33A');
		Smilies::add($b, ':sunflower', '&#x1F33B');
		Smilies::add($b, ':blossom', '&#x1F33C');
		Smilies::add($b, ':tulip', '&#x1F337');

// plant-other
		Smilies::add($b, ':seedling', '&#x1F331');
		Smilies::add($b, ':evergreen tree', '&#x1F332');
		Smilies::add($b, ':deciduous tree', '&#x1F333');
		Smilies::add($b, ':palm tree', '&#x1F334');
		Smilies::add($b, ':cactus', '&#x1F335');
		Smilies::add($b, ':sheaf of rice', '&#x1F33E');
		Smilies::add($b, ':herb', '&#x1F33F');
		Smilies::add($b, ':shamrock', '&#x2618');
		Smilies::add($b, ':four leaf clover', '&#x1F340');
		Smilies::add($b, ':maple leaf', '&#x1F341');
		Smilies::add($b, ':fallen leaf', '&#x1F342');
		Smilies::add($b, ':leaf fluttering in wind', '&#x1F343');

// Food & Drink
// food-fruit
		Smilies::add($b, ':grapes', '&#x1F347');
		Smilies::add($b, ':melon', '&#x1F348');
		Smilies::add($b, ':watermelon', '&#x1F349');
		Smilies::add($b, ':tangerine', '&#x1F34A');
		Smilies::add($b, ':lemon', '&#x1F34B');
		Smilies::add($b, ':banana', '&#x1F34C');
		Smilies::add($b, ':pineapple', '&#x1F34D');
		Smilies::add($b, ':mango', '&#x1F96D');
		Smilies::add($b, ':red apple', '&#x1F34E');
		Smilies::add($b, ':green apple', '&#x1F34F');
		Smilies::add($b, ':pear', '&#x1F350');
		Smilies::add($b, ':peach', '&#x1F351');
		Smilies::add($b, ':cherries', '&#x1F352');
		Smilies::add($b, ':strawberry', '&#x1F353');
		Smilies::add($b, ':kiwi fruit', '&#x1F95D');
		Smilies::add($b, ':tomato', '&#x1F345');
		Smilies::add($b, ':coconut', '&#x1F965');

// food-vegetable
		Smilies::add($b, ':avocado', '&#x1F951');
		Smilies::add($b, ':eggplant', '&#x1F346');
		Smilies::add($b, ':potato', '&#x1F954');
		Smilies::add($b, ':carrot', '&#x1F955');
		Smilies::add($b, ':ear of corn', '&#x1F33D');
		Smilies::add($b, ':hot pepper', '&#x1F336');
		Smilies::add($b, ':cucumber', '&#x1F952');
		Smilies::add($b, ':leafy green', '&#x1F96C');
		Smilies::add($b, ':broccoli', '&#x1F966');
//  		Smilies::add($b, ':garlic', '&#x1F9C4');
//  		Smilies::add($b, ':onion', '&#x1F9C5');
		Smilies::add($b, ':mushroom', '&#x1F344');
		Smilies::add($b, ':peanuts', '&#x1F95C');
		Smilies::add($b, ':chestnut', '&#x1F330');
    
// food-prepared
		Smilies::add($b, ':bread', '&#x1F35E');
		Smilies::add($b, ':croissant', '&#x1F950');
		Smilies::add($b, ':baguette bread', '&#x1F956');
		Smilies::add($b, ':pretzel', '&#x1F968');
		Smilies::add($b, ':bagel', '&#x1F96F');
		Smilies::add($b, ':pancakes', '&#x1F95E');
//  		Smilies::add($b, ':waffle', '&#x1F9C7');
		Smilies::add($b, ':cheese wedge', '&#x1F9C0');
		Smilies::add($b, ':meat on bone', '&#x1F356');
		Smilies::add($b, ':poultry leg', '&#x1F357');
		Smilies::add($b, ':cut of meat', '&#x1F969');
		Smilies::add($b, ':bacon', '&#x1F953');
		Smilies::add($b, ':hamburger', '&#x1F354');
		Smilies::add($b, ':french fries', '&#x1F35F');
		Smilies::add($b, ':pizza', '&#x1F355');
		Smilies::add($b, ':hot dog', '&#x1F32D');
		Smilies::add($b, ':sandwich', '&#x1F96A');
		Smilies::add($b, ':taco', '&#x1F32E');
		Smilies::add($b, ':burrito', '&#x1F32F');
		Smilies::add($b, ':stuffed flatbread', '&#x1F959');
//  		Smilies::add($b, ':falafel', '&#x1F9C6');
		Smilies::add($b, ':egg', '&#x1F95A');
		Smilies::add($b, ':cooking', '&#x1F373');
		Smilies::add($b, ':shallow pan of food', '&#x1F958');
		Smilies::add($b, ':pot of food', '&#x1F372');
		Smilies::add($b, ':bowl with spoon', '&#x1F963');
		Smilies::add($b, ':green salad', '&#x1F957');
		Smilies::add($b, ':popcorn', '&#x1F37F');
//  		Smilies::add($b, ':butter', '&#x1F9C8');
		Smilies::add($b, ':salt', '&#x1F9C2');
		Smilies::add($b, ':canned food', '&#x1F96B');

// food-asian
		Smilies::add($b, ':bento box', '&#x1F371');
		Smilies::add($b, ':rice cracker', '&#x1F358');
		Smilies::add($b, ':rice ball', '&#x1F359');
		Smilies::add($b, ':cooked rice', '&#x1F35A');
		Smilies::add($b, ':curry rice', '&#x1F35B');
		Smilies::add($b, ':steaming bowl', '&#x1F35C');
		Smilies::add($b, ':spaghetti', '&#x1F35D');
		Smilies::add($b, ':roasted sweet potato', '&#x1F360');
		Smilies::add($b, ':oden', '&#x1F362');
		Smilies::add($b, ':sushi', '&#x1F363');
		Smilies::add($b, ':fried shrimp', '&#x1F364');
		Smilies::add($b, ':fish cake with swirl', '&#x1F365');
		Smilies::add($b, ':moon cake', '&#x1F96E');
		Smilies::add($b, ':dango', '&#x1F361');
		Smilies::add($b, ':dumpling', '&#x1F95F');
		Smilies::add($b, ':fortune cookie', '&#x1F960');
		Smilies::add($b, ':takeout box', '&#x1F961');

// food-marine
		Smilies::add($b, ':crab', '&#x1F980');
		Smilies::add($b, ':lobster', '&#x1F99E');
		Smilies::add($b, ':shrimp', '&#x1F990');
		Smilies::add($b, ':squid', '&#x1F991');
// 	 	Smilies::add($b, ':oyster', '&#x1F9AA');
    
// food-sweet
		Smilies::add($b, ':soft ice cream', '&#x1F366');
		Smilies::add($b, ':shaved ice', '&#x1F367');
		Smilies::add($b, ':ice cream', '&#x1F368');
		Smilies::add($b, ':doughnut', '&#x1F369');
		Smilies::add($b, ':cookie', '&#x1F36A');
		Smilies::add($b, ':birthday cake', '&#x1F382');
		Smilies::add($b, ':shortcake', '&#x1F370');
		Smilies::add($b, ':cupcake', '&#x1F9C1');
		Smilies::add($b, ':pie', '&#x1F967');
		Smilies::add($b, ':chocolate bar', '&#x1F36B');
		Smilies::add($b, ':candy', '&#x1F36C');
		Smilies::add($b, ':lollipop', '&#x1F36D');
		Smilies::add($b, ':custard', '&#x1F36E');
		Smilies::add($b, ':honey pot', '&#x1F36F');
    
// drink  
		Smilies::add($b, ':baby bottle', '&#x1F37C');
		Smilies::add($b, ':glass of milk', '&#x1F95B');
		Smilies::add($b, ':hot beverage', '&#x2615');
		Smilies::add($b, ':teacup without handle', '&#x1F375');
		Smilies::add($b, ':sake', '&#x1F376');
		Smilies::add($b, ':bottle with popping cork', '&#x1F37E');
		Smilies::add($b, ':wine glass', '&#x1F377');
		Smilies::add($b, ':cocktail glass', '&#x1F378');
		Smilies::add($b, ':tropical drink', '&#x1F379');
		Smilies::add($b, ':beer mug', '&#x1F37A');
		Smilies::add($b, ':clinking beer mugs', '&#x1F37B');
		Smilies::add($b, ':clinking glasses', '&#x1F942');
		Smilies::add($b, ':tumbler glass', '&#x1F943');
		Smilies::add($b, ':cup with straw', '&#x1F964');
//  		Smilies::add($b, ':beverage box', '&#x1F9C3');
//  		Smilies::add($b, ':mate', '&#x1F9C9');
//  		Smilies::add($b, ':ice', '&#x1F9CA');
    
// dishware
		Smilies::add($b, ':chopsticks', '&#x1F962');
		Smilies::add($b, ':fork and knife with plate', '&#x1F37D');
		Smilies::add($b, ':fork and knife', '&#x1F374');
		Smilies::add($b, ':spoon', '&#x1F944');
		Smilies::add($b, ':kitchen knife', '&#x1F52A');
		Smilies::add($b, ':amphora', '&#x1F3FA');

// Travel & Places
// place-map
		Smilies::add($b, ':globe showing Europe-Africa', '&#x1F30D');
		Smilies::add($b, ':globe showing Americas', '&#x1F30E');
		Smilies::add($b, ':globe showing Asia-Australia', '&#x1F30F');
		Smilies::add($b, ':globe with meridians', '&#x1F310');
		Smilies::add($b, ':world map', '&#x1F5FA');
		Smilies::add($b, ':map of Japan', '&#x1F5FE');
		Smilies::add($b, ':compass', '&#x1F9ED');
    
// place-geographic
		Smilies::add($b, ':snow-capped mountain', '&#x1F3D4');
		Smilies::add($b, ':mountain', '&#x26F0');
		Smilies::add($b, ':volcano', '&#x1F30B');
		Smilies::add($b, ':mount fuji', '&#x1F5FB');
		Smilies::add($b, ':camping', '&#x1F3D5');
		Smilies::add($b, ':beach with umbrella', '&#x1F3D6');
		Smilies::add($b, ':desert', '&#x1F3DC');
		Smilies::add($b, ':desert island', '&#x1F3DD');
		Smilies::add($b, ':national park', '&#x1F3DE');

// place-building
		Smilies::add($b, ':stadium', '&#x1F3DF');
		Smilies::add($b, ':classical building', '&#x1F3DB');
		Smilies::add($b, ':building construction', '&#x1F3D7');
		Smilies::add($b, ':brick', '&#x1F9F1');
		Smilies::add($b, ':houses', '&#x1F3D8');
		Smilies::add($b, ':derelict house', '&#x1F3DA');
		Smilies::add($b, ':house', '&#x1F3E0');
		Smilies::add($b, ':house with garden', '&#x1F3E1');
		Smilies::add($b, ':office building', '&#x1F3E2');
		Smilies::add($b, ':Japanese post office', '&#x1F3E3');
		Smilies::add($b, ':post office', '&#x1F3E4');
		Smilies::add($b, ':hospital', '&#x1F3E5');
		Smilies::add($b, ':bank', '&#x1F3E6');
		Smilies::add($b, ':hotel', '&#x1F3E8');
		Smilies::add($b, ':love hotel', '&#x1F3E9');
		Smilies::add($b, ':convenience store', '&#x1F3EA');
		Smilies::add($b, ':school', '&#x1F3EB');
		Smilies::add($b, ':department store', '&#x1F3EC');
		Smilies::add($b, ':factory', '&#x1F3ED');
		Smilies::add($b, ':Japanese castle', '&#x1F3EF');
		Smilies::add($b, ':castle', '&#x1F3F0');
		Smilies::add($b, ':wedding', '&#x1F492');
		Smilies::add($b, ':Tokyo tower', '&#x1F5FC');
		Smilies::add($b, ':Statue of Liberty', '&#x1F5FD');
    
// place-religious
		Smilies::add($b, ':church', '&#x26EA');
		Smilies::add($b, ':mosque', '&#x1F54C');
//  		Smilies::add($b, ':hindu temple', '&#x1F6D5');
		Smilies::add($b, ':synagogue', '&#x1F54D');
		Smilies::add($b, ':shinto shrine', '&#x26E9');
		Smilies::add($b, ':kaaba', '&#x1F54B');
    
// place-other
		Smilies::add($b, ':fountain', '&#x26F2');
		Smilies::add($b, ':tent', '&#x26FA');
		Smilies::add($b, ':foggy', '&#x1F301');
		Smilies::add($b, ':night with stars', '&#x1F303');
		Smilies::add($b, ':cityscape', '&#x1F3D9');
		Smilies::add($b, ':sunrise over mountains', '&#x1F304');
		Smilies::add($b, ':sunrise', '&#x1F305');
		Smilies::add($b, ':cityscape at dusk', '&#x1F306');
		Smilies::add($b, ':sunset', '&#x1F307');
		Smilies::add($b, ':bridge at night', '&#x1F309');
		Smilies::add($b, ':hot springs', '&#x2668');
		Smilies::add($b, ':carousel horse', '&#x1F3A0');
		Smilies::add($b, ':ferris wheel', '&#x1F3A1');
		Smilies::add($b, ':roller coaster', '&#x1F3A2');
		Smilies::add($b, ':barber pole', '&#x1F488');
		Smilies::add($b, ':circus tent', '&#x1F3AA');
    
// transport-ground
		Smilies::add($b, ':locomotive', '&#x1F682');
		Smilies::add($b, ':railway car', '&#x1F683');
		Smilies::add($b, ':high-speed train', '&#x1F684');
		Smilies::add($b, ':bullet train', '&#x1F685');
		Smilies::add($b, ':train', '&#x1F686');
		Smilies::add($b, ':metro', '&#x1F687');
		Smilies::add($b, ':light rail', '&#x1F688');
		Smilies::add($b, ':station', '&#x1F689');
		Smilies::add($b, ':tram', '&#x1F68A');
		Smilies::add($b, ':monorail', '&#x1F69D');
		Smilies::add($b, ':mountain railway', '&#x1F69E');
		Smilies::add($b, ':tram car', '&#x1F68B');
		Smilies::add($b, ':bus', '&#x1F68C');
		Smilies::add($b, ':oncoming bus', '&#x1F68D');
		Smilies::add($b, ':trolleybus', '&#x1F68E');
		Smilies::add($b, ':minibus', '&#x1F690');
		Smilies::add($b, ':ambulance', '&#x1F691');
		Smilies::add($b, ':fire engine', '&#x1F692');
		Smilies::add($b, ':police car', '&#x1F693');
		Smilies::add($b, ':oncoming police car', '&#x1F694');
		Smilies::add($b, ':taxi', '&#x1F695');
		Smilies::add($b, ':oncoming taxi', '&#x1F696');
		Smilies::add($b, ':automobile', '&#x1F697');
		Smilies::add($b, ':oncoming automobile', '&#x1F698');
		Smilies::add($b, ':sport utility vehicle', '&#x1F699');
		Smilies::add($b, ':delivery truck', '&#x1F69A');
		Smilies::add($b, ':articulated lorry', '&#x1F69B');
		Smilies::add($b, ':tractor', '&#x1F69C');
		Smilies::add($b, ':racing car', '&#x1F3CE');
		Smilies::add($b, ':motorcycle', '&#x1F3CD');
		Smilies::add($b, ':motor scooter', '&#x1F6F5');
//  		Smilies::add($b, ':manual wheelchair', '&#x1F9BD');
//  		Smilies::add($b, ':motorized wheelchair', '&#x1F9BC');
//  		Smilies::add($b, ':auto rickshaw', '&#x1F6FA');
		Smilies::add($b, ':bicycle', '&#x1F6B2');
		Smilies::add($b, ':kick scooter', '&#x1F6F4');
		Smilies::add($b, ':skateboard', '&#x1F6F9');
		Smilies::add($b, ':bus stop', '&#x1F68F');
		Smilies::add($b, ':motorway', '&#x1F6E3');
		Smilies::add($b, ':railway track', '&#x1F6E4');
		Smilies::add($b, ':oil drum', '&#x1F6E2');
		Smilies::add($b, ':fuel pump', '&#x26FD');
		Smilies::add($b, ':police car light', '&#x1F6A8');
		Smilies::add($b, ':horizontal traffic light', '&#x1F6A5');
		Smilies::add($b, ':vertical traffic light', '&#x1F6A6');
		Smilies::add($b, ':stop sign', '&#x1F6D1');
		Smilies::add($b, ':construction', '&#x1F6A7');

// transport-water
		Smilies::add($b, ':anchor', '&#x2693');
		Smilies::add($b, ':sailboat', '&#x26F5');
		Smilies::add($b, ':canoe', '&#x1F6F6');
		Smilies::add($b, ':speedboat', '&#x1F6A4');
		Smilies::add($b, ':passenger ship', '&#x1F6F3');
		Smilies::add($b, ':ferry', '&#x26F4');
		Smilies::add($b, ':motor boat', '&#x1F6E5');
		Smilies::add($b, ':ship', '&#x1F6A2');
    
// transport-air
		Smilies::add($b, ':airplane', '&#x2708');
		Smilies::add($b, ':small airplane', '&#x1F6E9');
		Smilies::add($b, ':airplane departure', '&#x1F6EB');
		Smilies::add($b, ':airplane arrival', '&#x1F6EC');
//  		Smilies::add($b, ':parachute', '&#x1FA82');
		Smilies::add($b, ':seat', '&#x1F4BA');
		Smilies::add($b, ':helicopter', '&#x1F681');
		Smilies::add($b, ':suspension railway', '&#x1F69F');
		Smilies::add($b, ':mountain cableway', '&#x1F6A0');
		Smilies::add($b, ':aerial tramway', '&#x1F6A1');
		Smilies::add($b, ':satellite', '&#x1F6F0');
		Smilies::add($b, ':rocket', '&#x1F680');
		Smilies::add($b, ':flying saucer', '&#x1F6F8');
    
// hotel
    		Smilies::add($b, ':bellhop bell', '&#x1F6CE');
    		Smilies::add($b, ':luggage', '&#x1F9F3');
    
// time
		Smilies::add($b, ':hourglass done', '&#x231B');
		Smilies::add($b, ':hourglass not done', '&#x23F3');
		Smilies::add($b, ':watch', '&#x231A');
		Smilies::add($b, ':alarm clock', '&#x23F0');
		Smilies::add($b, ':stopwatch', '&#x23F1');
		Smilies::add($b, ':timer clock', '&#x23F2');
		Smilies::add($b, ':mantelpiece clock', '&#x1F570');
		Smilies::add($b, ':twelve o’clock', '&#x1F55B');
		Smilies::add($b, ':twelve-thirty', '&#x1F567');
		Smilies::add($b, ':one o’clock', '&#x1F550');
		Smilies::add($b, ':one-thirty', '&#x1F55C');
		Smilies::add($b, ':two o’clock', '&#x1F551');
		Smilies::add($b, ':two-thirty', '&#x1F55D');
		Smilies::add($b, ':three o’clock', '&#x1F552');
		Smilies::add($b, ':three-thirty', '&#x1F55E');
		Smilies::add($b, ':four o’clock', '&#x1F553');
		Smilies::add($b, ':four-thirty', '&#x1F55F');
		Smilies::add($b, ':five o’clock', '&#x1F554');
		Smilies::add($b, ':five-thirty', '&#x1F560');
		Smilies::add($b, ':six o’clock', '&#x1F555');
		Smilies::add($b, ':six-thirty', '&#x1F561');
		Smilies::add($b, ':seven o’clock', '&#x1F556');
		Smilies::add($b, ':seven-thirty', '&#x1F562');
		Smilies::add($b, ':eight o’clock', '&#x1F557');
		Smilies::add($b, ':eight-thirty', '&#x1F563');
		Smilies::add($b, ':nine o’clock', '&#x1F558');
		Smilies::add($b, ':nine-thirty', '&#x1F564');
		Smilies::add($b, ':ten o’clock', '&#x1F559');
		Smilies::add($b, ':ten-thirty', '&#x1F565');
		Smilies::add($b, ':eleven o’clock', '&#x1F55A');
		Smilies::add($b, ':eleven-thirty', '&#x1F566');
    
// sky & weather
		Smilies::add($b, ':new moon', '&#x1F311');
		Smilies::add($b, ':waxing crescent moon', '&#x1F312');
		Smilies::add($b, ':first quarter moon', '&#x1F313');
		Smilies::add($b, ':waxing gibbous moon', '&#x1F314');
		Smilies::add($b, ':full moon', '&#x1F315');
		Smilies::add($b, ':waning gibbous moon', '&#x1F316');
		Smilies::add($b, ':last quarter moon', '&#x1F317');
		Smilies::add($b, ':waning crescent moon', '&#x1F318');
		Smilies::add($b, ':crescent moon', '&#x1F319');
		Smilies::add($b, ':new moon face', '&#x1F31A');
		Smilies::add($b, ':first quarter moon face', '&#x1F31B');
		Smilies::add($b, ':last quarter moon face', '&#x1F31C');
		Smilies::add($b, ':thermometer', '&#x1F321');
		Smilies::add($b, ':sun', '&#x2600');
		Smilies::add($b, ':full moon face', '&#x1F31D');
		Smilies::add($b, ':sun with face', '&#x1F31E');
//  		Smilies::add($b, ':ringed planet', '&#x1FA90');
		Smilies::add($b, ':star', '&#x2B50');
		Smilies::add($b, ':glowing star', '&#x1F31F');
		Smilies::add($b, ':shooting star', '&#x1F320');
		Smilies::add($b, ':milky way', '&#x1F30C');
		Smilies::add($b, ':cloud', '&#x2601');
		Smilies::add($b, ':sun behind cloud', '&#x26C5');
		Smilies::add($b, ':cloud with lightning and rain', '&#x26C8');
		Smilies::add($b, ':sun behind small cloud', '&#x1F324');
		Smilies::add($b, ':sun behind large cloud', '&#x1F325');
		Smilies::add($b, ':sun behind rain cloud', '&#x1F326');
		Smilies::add($b, ':cloud with rain', '&#x1F327');
		Smilies::add($b, ':cloud with snow', '&#x1F328');
		Smilies::add($b, ':cloud with lightning', '&#x1F329');
		Smilies::add($b, ':tornado', '&#x1F32A');
		Smilies::add($b, ':fog', '&#x1F32B');
		Smilies::add($b, ':wind face', '&#x1F32C');
		Smilies::add($b, ':cyclone', '&#x1F300');
		Smilies::add($b, ':rainbow', '&#x1F308');
		Smilies::add($b, ':closed umbrella', '&#x1F302');
		Smilies::add($b, ':umbrella', '&#x2602');
		Smilies::add($b, ':umbrella with rain drops', '&#x2614');
		Smilies::add($b, ':umbrella on ground', '&#x26F1');
		Smilies::add($b, ':high voltage', '&#x26A1');
		Smilies::add($b, ':snowflake', '&#x2744');
		Smilies::add($b, ':snowman', '&#x2603');
		Smilies::add($b, ':snowman without snow', '&#x26C4');
		Smilies::add($b, ':comet', '&#x2604');
		Smilies::add($b, ':fire', '&#x1F525');
		Smilies::add($b, ':droplet', '&#x1F4A7');
		Smilies::add($b, ':water wave', '&#x1F30A');

// Activities
// event
		Smilies::add($b, ':jack-o-lantern', '&#x1F383');
		Smilies::add($b, ':Christmas tree', '&#x1F384');
		Smilies::add($b, ':fireworks', '&#x1F386');
		Smilies::add($b, ':sparkler', '&#x1F387');
		Smilies::add($b, ':firecracker', '&#x1F9E8');
		Smilies::add($b, ':sparkles', '&#x2728');
		Smilies::add($b, ':balloon', '&#x1F388');
		Smilies::add($b, ':party popper', '&#x1F389');
		Smilies::add($b, ':confetti ball', '&#x1F38A');
		Smilies::add($b, ':tanabata tree', '&#x1F38B');
		Smilies::add($b, ':pine decoration', '&#x1F38D');
		Smilies::add($b, ':Japanese dolls', '&#x1F38E');
		Smilies::add($b, ':carp streamer', '&#x1F38F');
		Smilies::add($b, ':wind chime', '&#x1F390');
		Smilies::add($b, ':moon viewing ceremony', '&#x1F391');
		Smilies::add($b, ':red envelope', '&#x1F9E7');
		Smilies::add($b, ':ribbon', '&#x1F380');
		Smilies::add($b, ':wrapped gift', '&#x1F381');
		Smilies::add($b, ':reminder ribbon', '&#x1F397');
		Smilies::add($b, ':admission tickets', '&#x1F39F');
		Smilies::add($b, ':ticket', '&#x1F3AB');
    
// award-medal
		Smilies::add($b, ':military medal', '&#x1F396');
		Smilies::add($b, ':trophy', '&#x1F3C6');
		Smilies::add($b, ':sports medal', '&#x1F3C5');
		Smilies::add($b, ':1st place medal', '&#x1F947');
		Smilies::add($b, ':2nd place medal', '&#x1F948');
		Smilies::add($b, ':3rd place medal', '&#x1F949');
    
// sport
		Smilies::add($b, ':soccer ball', '&#x26BD');
		Smilies::add($b, ':baseball', '&#x26BE');
		Smilies::add($b, ':softball', '&#x1F94E');
		Smilies::add($b, ':basketball', '&#x1F3C0');
		Smilies::add($b, ':volleyball', '&#x1F3D0');
		Smilies::add($b, ':american football', '&#x1F3C8');
		Smilies::add($b, ':rugby football', '&#x1F3C9');
		Smilies::add($b, ':tennis', '&#x1F3BE');
		Smilies::add($b, ':flying disc', '&#x1F94F');
		Smilies::add($b, ':bowling', '&#x1F3B3');
		Smilies::add($b, ':cricket game', '&#x1F3CF');
		Smilies::add($b, ':field hockey', '&#x1F3D1');
		Smilies::add($b, ':ice hockey', '&#x1F3D2');
		Smilies::add($b, ':lacrosse', '&#x1F94D');
		Smilies::add($b, ':ping pong', '&#x1F3D3');
		Smilies::add($b, ':badminton', '&#x1F3F8');
		Smilies::add($b, ':boxing glove', '&#x1F94A');
		Smilies::add($b, ':martial arts uniform', '&#x1F94B');
		Smilies::add($b, ':goal net', '&#x1F945');
		Smilies::add($b, ':flag in hole', '&#x26F3');
		Smilies::add($b, ':ice skate', '&#x26F8');
		Smilies::add($b, ':fishing pole', '&#x1F3A3');
//  		Smilies::add($b, ':diving mask', '&#x1F93F');
		Smilies::add($b, ':running shirt', '&#x1F3BD');
		Smilies::add($b, ':skis', '&#x1F3BF');
		Smilies::add($b, ':sled', '&#x1F6F7');
		Smilies::add($b, ':curling stone', '&#x1F94C');
    
// game
    		Smilies::add($b, ':direct hit', '&#x1F3AF');
//  		Smilies::add($b, ':yo-yo', '&#x1FA80');
//  		Smilies::add($b, ':kite', '&#x1FA81');
		Smilies::add($b, ':pool 8 ball', '&#x1F3B1');
		Smilies::add($b, ':crystal ball', '&#x1F52E');
		Smilies::add($b, ':nazar amulet', '&#x1F9FF');
		Smilies::add($b, ':video game', '&#x1F3AE');
		Smilies::add($b, ':joystick', '&#x1F579');
		Smilies::add($b, ':slot machine', '&#x1F3B0');
		Smilies::add($b, ':game die', '&#x1F3B2');
		Smilies::add($b, ':puzzle piece', '&#x1F9E9');
		Smilies::add($b, ':teddy bear', '&#x1F9F8');
		Smilies::add($b, ':spade suit', '&#x2660');
		Smilies::add($b, ':heart suit', '&#x2665');
		Smilies::add($b, ':diamond suit', '&#x2666');
		Smilies::add($b, ':club suit', '&#x2663');
		Smilies::add($b, ':chess pawn', '&#x265F');
		Smilies::add($b, ':joker', '&#x1F0CF');
		Smilies::add($b, ':mahjong red dragon', '&#x1F004');
		Smilies::add($b, ':flower playing cards', '&#x1F3B4');
    
// arts & crafts
		Smilies::add($b, ':performing arts', '&#x1F3AD');
		Smilies::add($b, ':framed picture', '&#x1F5BC');
		Smilies::add($b, ':artist palette', '&#x1F3A8');
		Smilies::add($b, ':thread', '&#x1F9F5');
		Smilies::add($b, ':yarn', '&#x1F9F6');

// Objects
// clothing
		Smilies::add($b, ':glasses', '&#x1F453');
		Smilies::add($b, ':sunglasses', '&#x1F576');
		Smilies::add($b, ':goggles', '&#x1F97D');
		Smilies::add($b, ':lab coat', '&#x1F97C');
//  		Smilies::add($b, ':safety vest', '&#x1F9BA');
		Smilies::add($b, ':necktie', '&#x1F454');
		Smilies::add($b, ':t-shirt', '&#x1F455');
		Smilies::add($b, ':jeans', '&#x1F456');
		Smilies::add($b, ':scarf', '&#x1F9E3');
		Smilies::add($b, ':gloves', '&#x1F9E4');
		Smilies::add($b, ':coat', '&#x1F9E5');
		Smilies::add($b, ':socks', '&#x1F9E6');
		Smilies::add($b, ':dress', '&#x1F457');
		Smilies::add($b, ':kimono', '&#x1F458');
//  		Smilies::add($b, ':sari', '&#x1F97B');
//  		Smilies::add($b, ':one-piece swimsuit', '&#x1FA71');
//  		Smilies::add($b, ':briefs', '&#x1FA72');
//  		Smilies::add($b, ':shorts', '&#x1FA73');
		Smilies::add($b, ':bikini', '&#x1F459');
		Smilies::add($b, ':woman’s clothes', '&#x1F45A');
		Smilies::add($b, ':purse', '&#x1F45B');
		Smilies::add($b, ':handbag', '&#x1F45C');
		Smilies::add($b, ':clutch bag', '&#x1F45D');
		Smilies::add($b, ':shopping bags', '&#x1F6CD');
		Smilies::add($b, ':backpack', '&#x1F392');
		Smilies::add($b, ':man’s shoe', '&#x1F45E');
		Smilies::add($b, ':running shoe', '&#x1F45F');
		Smilies::add($b, ':hiking boot', '&#x1F97E');
		Smilies::add($b, ':flat shoe', '&#x1F97F');
		Smilies::add($b, ':high-heeled shoe', '&#x1F460');
		Smilies::add($b, ':woman’s sandal', '&#x1F461');
		Smilies::add($b, ':ballet shoes', '&#x1FA70');
		Smilies::add($b, ':woman’s boot', '&#x1F462');
		Smilies::add($b, ':crown', '&#x1F451');
		Smilies::add($b, ':woman’s hat', '&#x1F452');
		Smilies::add($b, ':top hat', '&#x1F3A9');
		Smilies::add($b, ':graduation cap', '&#x1F393');
		Smilies::add($b, ':billed cap', '&#x1F9E2');
		Smilies::add($b, ':rescue worker’s helmet', '&#x26D1');
		Smilies::add($b, ':prayer beads', '&#x1F4FF');
		Smilies::add($b, ':lipstick', '&#x1F484');
		Smilies::add($b, ':ring', '&#x1F48D');
		Smilies::add($b, ':gem stone', '&#x1F48E');
    
// sound
		Smilies::add($b, ':muted speaker', '&#x1F507');
		Smilies::add($b, ':speaker low volume', '&#x1F508');
		Smilies::add($b, ':speaker medium volume', '&#x1F509');
		Smilies::add($b, ':speaker high volume', '&#x1F50A');
		Smilies::add($b, ':loudspeaker', '&#x1F4E2');
		Smilies::add($b, ':megaphone', '&#x1F4E3');
		Smilies::add($b, ':postal horn', '&#x1F4EF');
		Smilies::add($b, ':bell', '&#x1F514');
		Smilies::add($b, ':bell with slash', '&#x1F515');
    
// musik
		Smilies::add($b, ':musical score', '&#x1F3BC');
		Smilies::add($b, ':musical note', '&#x1F3B5');
		Smilies::add($b, ':musical notes', '&#x1F3B6');
		Smilies::add($b, ':studio microphone', '&#x1F399');
		Smilies::add($b, ':level slider', '&#x1F39A');
		Smilies::add($b, ':control knobs', '&#x1F39B');
		Smilies::add($b, ':microphone', '&#x1F3A4');
		Smilies::add($b, ':headphone', '&#x1F3A7');
		Smilies::add($b, ':radio', '&#x1F4FB');    

// musical-instrument
		Smilies::add($b, ':saxophone', '&#x1F3B7');
		Smilies::add($b, ':guitar', '&#x1F3B8');
		Smilies::add($b, ':musical keyboard', '&#x1F3B9');
		Smilies::add($b, ':trumpet', '&#x1F3BA');
		Smilies::add($b, ':violin', '&#x1F3BB');
//  		Smilies::add($b, ':banjo', '&#x1FA95');
    		Smilies::add($b, ':drum', '&#x1F941');
    
// phone
		Smilies::add($b, ':mobile phone', '&#x1F4F1');
		Smilies::add($b, ':mobile phone with arrow', '&#x1F4F2');
		Smilies::add($b, ':telephone', '&#x260E');
		Smilies::add($b, ':telephone receiver', '&#x1F4DE');
		Smilies::add($b, ':pager', '&#x1F4DF');
		Smilies::add($b, ':fax machine', '&#x1F4E0');
    
// computer
		Smilies::add($b, ':battery', '&#x1F50B');
		Smilies::add($b, ':electric plug', '&#x1F50C');
		Smilies::add($b, ':laptop', '&#x1F4BB');
		Smilies::add($b, ':desktop computer', '&#x1F5A5');
		Smilies::add($b, ':printer', '&#x1F5A8');
		Smilies::add($b, ':keyboard', '&#x2328');
		Smilies::add($b, ':computer mouse', '&#x1F5B1');
		Smilies::add($b, ':trackball', '&#x1F5B2');
		Smilies::add($b, ':computer disk', '&#x1F4BD');
		Smilies::add($b, ':floppy disk', '&#x1F4BE');
		Smilies::add($b, ':optical disk', '&#x1F4BF');
		Smilies::add($b, ':dvd', '&#x1F4C0');
		Smilies::add($b, ':abacus', '&#x1F9EE');
    
// light & video
		Smilies::add($b, ':movie camera', '&#x1F3A5');
		Smilies::add($b, ':film frames', '&#x1F39E');
		Smilies::add($b, ':film projector', '&#x1F4FD');
		Smilies::add($b, ':clapper board', '&#x1F3AC');
		Smilies::add($b, ':television', '&#x1F4FA');
		Smilies::add($b, ':camera', '&#x1F4F7');
		Smilies::add($b, ':camera with flash', '&#x1F4F8');
		Smilies::add($b, ':video camera', '&#x1F4F9');
		Smilies::add($b, ':videocassette', '&#x1F4FC');
		Smilies::add($b, ':magnifying glass tilted left', '&#x1F50D');
		Smilies::add($b, ':magnifying glass tilted right', '&#x1F50E');
		Smilies::add($b, ':candle', '&#x1F56F');
		Smilies::add($b, ':light bulb', '&#x1F4A1');
		Smilies::add($b, ':flashlight', '&#x1F526');
		Smilies::add($b, ':red paper lantern', '&#x1F3EE');
//  		Smilies::add($b, ':diya lamp', '&#x1FA94');
    
// book-paper
		Smilies::add($b, ':notebook with decorative cover', '&#x1F4D4');
		Smilies::add($b, ':closed book', '&#x1F4D5');
		Smilies::add($b, ':open book', '&#x1F4D6');
		Smilies::add($b, ':green book', '&#x1F4D7');
		Smilies::add($b, ':blue book', '&#x1F4D8');
		Smilies::add($b, ':orange book', '&#x1F4D9');
		Smilies::add($b, ':books', '&#x1F4DA');
		Smilies::add($b, ':notebook', '&#x1F4D3');
		Smilies::add($b, ':ledger', '&#x1F4D2');
		Smilies::add($b, ':page with curl', '&#x1F4C3');
		Smilies::add($b, ':scroll', '&#x1F4DC');
		Smilies::add($b, ':page facing up', '&#x1F4C4');
		Smilies::add($b, ':newspaper', '&#x1F4F0');
		Smilies::add($b, ':rolled-up newspaper', '&#x1F5DE');
		Smilies::add($b, ':bookmark tabs', '&#x1F4D1');
		Smilies::add($b, ':bookmark', '&#x1F516');
		Smilies::add($b, ':label', '&#x1F3F7');
    
// money
		Smilies::add($b, ':money bag', '&#x1F4B0');
		Smilies::add($b, ':yen banknote', '&#x1F4B4');
		Smilies::add($b, ':dollar banknote', '&#x1F4B5');
		Smilies::add($b, ':euro banknote', '&#x1F4B6');
		Smilies::add($b, ':pound banknote', '&#x1F4B7');
		Smilies::add($b, ':money with wings', '&#x1F4B8');
		Smilies::add($b, ':credit card', '&#x1F4B3');
		Smilies::add($b, ':receipt', '&#x1F9FE');
		Smilies::add($b, ':chart increasing with yen', '&#x1F4B9');
    
// mail
		Smilies::add($b, ':envelope', '&#x2709');
		Smilies::add($b, ':e-mail', '&#x1F4E7');
		Smilies::add($b, ':incoming envelope', '&#x1F4E8');
		Smilies::add($b, ':envelope with arrow', '&#x1F4E9');
		Smilies::add($b, ':outbox tray', '&#x1F4E4');
		Smilies::add($b, ':inbox tray', '&#x1F4E5');
		Smilies::add($b, ':package', '&#x1F4E6');
		Smilies::add($b, ':closed mailbox with raised flag', '&#x1F4EB');
		Smilies::add($b, ':closed mailbox with lowered flag', '&#x1F4EA');
		Smilies::add($b, ':open mailbox with raised flag', '&#x1F4EC');
		Smilies::add($b, ':open mailbox with lowered flag', '&#x1F4ED');
		Smilies::add($b, ':postbox', '&#x1F4EE');
		Smilies::add($b, ':ballot box with ballot', '&#x1F5F3');
    
// writing
		Smilies::add($b, ':pencil', '&#x270F');
		Smilies::add($b, ':black nib', '&#x2712');
		Smilies::add($b, ':fountain pen', '&#x1F58B');
		Smilies::add($b, ':pen', '&#x1F58A');
		Smilies::add($b, ':paintbrush', '&#x1F58C');
		Smilies::add($b, ':crayon', '&#x1F58D');
		Smilies::add($b, ':memo', '&#x1F4DD');

// office
		Smilies::add($b, ':briefcase', '&#x1F4BC');
		Smilies::add($b, ':file folder', '&#x1F4C1');
		Smilies::add($b, ':open file folder', '&#x1F4C2');
		Smilies::add($b, ':card index dividers', '&#x1F5C2');
		Smilies::add($b, ':calendar', '&#x1F4C5');
		Smilies::add($b, ':tear-off calendar', '&#x1F4C6');
		Smilies::add($b, ':spiral notepad', '&#x1F5D2');
		Smilies::add($b, ':spiral calendar', '&#x1F5D3');
		Smilies::add($b, ':card index', '&#x1F4C7');
		Smilies::add($b, ':chart increasing', '&#x1F4C8');
		Smilies::add($b, ':chart decreasing', '&#x1F4C9');
		Smilies::add($b, ':bar chart', '&#x1F4CA');
		Smilies::add($b, ':clipboard', '&#x1F4CB');
		Smilies::add($b, ':pushpin', '&#x1F4CC');
		Smilies::add($b, ':round pushpin', '&#x1F4CD');
		Smilies::add($b, ':paperclip', '&#x1F4CE');
		Smilies::add($b, ':linked paperclips', '&#x1F587');
		Smilies::add($b, ':straight ruler', '&#x1F4CF');
		Smilies::add($b, ':triangular ruler', '&#x1F4D0');
		Smilies::add($b, ':scissors', '&#x2702');
		Smilies::add($b, ':card file box', '&#x1F5C3');
		Smilies::add($b, ':file cabinet', '&#x1F5C4');
		Smilies::add($b, ':wastebasket', '&#x1F5D1');
    
// lock
		Smilies::add($b, ':locked', '&#x1F512');
		Smilies::add($b, ':unlocked', '&#x1F513');
		Smilies::add($b, ':locked with pen', '&#x1F50F');
		Smilies::add($b, ':locked with key', '&#x1F510');
		Smilies::add($b, ':key', '&#x1F511');
		Smilies::add($b, ':old key', '&#x1F5DD');
    
// tool
    		Smilies::add($b, ':hammer', '&#x1F528');
//  		Smilies::add($b, ':axe', '&#x1FA93');
		Smilies::add($b, ':pick', '&#x26CF');
		Smilies::add($b, ':hammer and pick', '&#x2692');
		Smilies::add($b, ':hammer and wrench', '&#x1F6E0');
		Smilies::add($b, ':dagger', '&#x1F5E1');
		Smilies::add($b, ':crossed swords', '&#x2694');
		Smilies::add($b, ':pistol', '&#x1F52B');
		Smilies::add($b, ':bow and arrow', '&#x1F3F9');
		Smilies::add($b, ':shield', '&#x1F6E1');
		Smilies::add($b, ':wrench', '&#x1F527');
		Smilies::add($b, ':nut and bolt', '&#x1F529');
		Smilies::add($b, ':gear', '&#x2699');
		Smilies::add($b, ':clamp', '&#x1F5DC');
		Smilies::add($b, ':balance scale', '&#x2696');
//  		Smilies::add($b, ':white cane', '&#x1F9AF');
		Smilies::add($b, ':link', '&#x1F517');
		Smilies::add($b, ':chains', '&#x26D3');
		Smilies::add($b, ':toolbox', '&#x1F9F0');
		Smilies::add($b, ':magnet', '&#x1F9F2');
    
// science
		Smilies::add($b, ':alembic', '&#x2697');
		Smilies::add($b, ':test tube', '&#x1F9EA');
		Smilies::add($b, ':petri dish', '&#x1F9EB');
		Smilies::add($b, ':dna', '&#x1F9EC');
		Smilies::add($b, ':microscope', '&#x1F52C');
		Smilies::add($b, ':telescope', '&#x1F52D');
		Smilies::add($b, ':satellite antenna', '&#x1F4E1');

// medical
    		Smilies::add($b, ':syringe', '&#x1F489');
//    		Smilies::add($b, ':drop of blood', '&#x1FA78');
    		Smilies::add($b, ':pill', '&#x1F48A');
//  		Smilies::add($b, ':adhesive bandage', '&#x1FA79');
//  		Smilies::add($b, ':stethoscope', '&#x1FA7A');
    
// household
		Smilies::add($b, ':door', '&#x1F6AA');
		Smilies::add($b, ':bed', '&#x1F6CF');
		Smilies::add($b, ':couch and lamp', '&#x1F6CB');
//    		Smilies::add($b, ':chair', '&#x1FA91');
		Smilies::add($b, ':toilet', '&#x1F6BD');
		Smilies::add($b, ':shower', '&#x1F6BF');
//  		Smilies::add($b, ':bathtub', '&#x1F6C1');
		Smilies::add($b, ':razor', '&#x1FA92');
		Smilies::add($b, ':lotion bottle', '&#x1F9F4');
		Smilies::add($b, ':safety pin', '&#x1F9F7');
		Smilies::add($b, ':broom', '&#x1F9F9');
		Smilies::add($b, ':basket', '&#x1F9FA');
		Smilies::add($b, ':roll of paper', '&#x1F9FB');
		Smilies::add($b, ':soap', '&#x1F9FC');
		Smilies::add($b, ':sponge', '&#x1F9FD');
		Smilies::add($b, ':fire extinguisher', '&#x1F9EF');
		Smilies::add($b, ':shopping cart', '&#x1F6D2');

// other-object
		Smilies::add($b, ':cigarette', '&#x1F6AC');
		Smilies::add($b, ':coffin', '&#x26B0');
		Smilies::add($b, ':funeral urn', '&#x26B1');
		Smilies::add($b, ':moai', '&#x1F5FF');

// Symbols
// transport-sign
		Smilies::add($b, ':atm sign', '&#x1F3E7');
		Smilies::add($b, ':litter in bin sign', '&#x1F6AE');
		Smilies::add($b, ':potable water', '&#x1F6B0');
		Smilies::add($b, ':wheelchair symbol', '&#x267F');
		Smilies::add($b, ':men’s room', '&#x1F6B9');
		Smilies::add($b, ':women’s room', '&#x1F6BA');
		Smilies::add($b, ':restroom', '&#x1F6BB');
		Smilies::add($b, ':baby symbol', '&#x1F6BC');
		Smilies::add($b, ':water closet', '&#x1F6BE');
		Smilies::add($b, ':passport control', '&#x1F6C2');
		Smilies::add($b, ':customs', '&#x1F6C3');
		Smilies::add($b, ':baggage claim', '&#x1F6C4');
		Smilies::add($b, ':left luggage', '&#x1F6C5');

// warning
		Smilies::add($b, ':warning', '&#x26A0');
		Smilies::add($b, ':children crossing', '&#x1F6B8');
		Smilies::add($b, ':no entry', '&#x26D4');
		Smilies::add($b, ':prohibited', '&#x1F6AB');
		Smilies::add($b, ':no bicycles', '&#x1F6B3');
		Smilies::add($b, ':no smoking', '&#x1F6AD');
		Smilies::add($b, ':no littering', '&#x1F6AF');
		Smilies::add($b, ':non-potable water', '&#x1F6B1');
		Smilies::add($b, ':no pedestrians', '&#x1F6B7');
		Smilies::add($b, ':no mobile phones', '&#x1F4F5');
		Smilies::add($b, ':no one under eighteen', '&#x1F51E');
		Smilies::add($b, ':radioactive', '&#x2622');
		Smilies::add($b, ':biohazard', '&#x2623');
    
// arrow
		Smilies::add($b, ':up arrow', '&#x2B06');
		Smilies::add($b, ':up-right arrow', '&#x2197');
		Smilies::add($b, ':right arrow', '&#x27A1');
		Smilies::add($b, ':down-right arrow', '&#x2198');
		Smilies::add($b, ':down arrow', '&#x2B07');
		Smilies::add($b, ':down-left arrow', '&#x2199');
		Smilies::add($b, ':left arrow', '&#x2B05');
		Smilies::add($b, ':up-left arrow', '&#x2196');
		Smilies::add($b, ':up-down arrow', '&#x2195');
		Smilies::add($b, ':left-right arrow', '&#x2194');
		Smilies::add($b, ':right arrow curving left', '&#x21A9');
		Smilies::add($b, ':left arrow curving right', '&#x21AA');
		Smilies::add($b, ':right arrow curving up', '&#x2934');
		Smilies::add($b, ':right arrow curving down', '&#x2935');
		Smilies::add($b, ':clockwise vertical arrows', '&#x1F503');
		Smilies::add($b, ':counterclockwise arrows button', '&#x1F504');
		Smilies::add($b, ':BACK arrow', '&#x1F519');
		Smilies::add($b, ':END arrow', '&#x1F51A');
		Smilies::add($b, ':ON! arrow', '&#x1F51B');
		Smilies::add($b, ':SOON arrow', '&#x1F51C');
		Smilies::add($b, ':TOP arrow', '&#x1F51D');
    
// religion
		Smilies::add($b, ':place of worship', '&#x1F6D0');
		Smilies::add($b, ':atom symbol', '&#x269B');
		Smilies::add($b, ':om', '&#x1F549');
		Smilies::add($b, ':star of David', '&#x2721');
		Smilies::add($b, ':wheel of dharma', '&#x2638');
		Smilies::add($b, ':yin yang', '&#x262F');
		Smilies::add($b, ':latin cross', '&#x271D');
		Smilies::add($b, ':orthodox cross', '&#x2626');
		Smilies::add($b, ':star and crescent', '&#x262A');
		Smilies::add($b, ':peace symbol', '&#x262E');
		Smilies::add($b, ':menorah', '&#x1F54E');
		Smilies::add($b, ':dotted six-pointed star', '&#x1F52F');
    
// zodiac
		Smilies::add($b, ':Aries', '&#x2648');
		Smilies::add($b, ':Taurus', '&#x2649');
		Smilies::add($b, ':Gemini', '&#x264A');
		Smilies::add($b, ':Cancer', '&#x264B');
		Smilies::add($b, ':Leo', '&#x264C');
		Smilies::add($b, ':Virgo', '&#x264D');
		Smilies::add($b, ':Libra', '&#x264E');
		Smilies::add($b, ':Scorpio', '&#x264F');
		Smilies::add($b, ':Sagittarius', '&#x2650');
		Smilies::add($b, ':Capricorn', '&#x2651');
		Smilies::add($b, ':Aquarius', '&#x2652');
		Smilies::add($b, ':Pisces', '&#x2653');
		Smilies::add($b, ':Ophiuchus', '&#x26CE');
    
// av-symbol
		Smilies::add($b, ':shuffle tracks button', '&#x1F500');
		Smilies::add($b, ':repeat button', '&#x1F501');
		Smilies::add($b, ':repeat single button', '&#x1F502');
		Smilies::add($b, ':play button', '&#x25B6');
		Smilies::add($b, ':fast-forward button', '&#x23E9');
		Smilies::add($b, ':next track button', '&#x23ED');
		Smilies::add($b, ':play or pause button', '&#x23EF');
		Smilies::add($b, ':reverse button', '&#x25C0');
		Smilies::add($b, ':fast reverse button', '&#x23EA');
		Smilies::add($b, ':last track button', '&#x23EE');
		Smilies::add($b, ':upwards button', '&#x1F53C');
		Smilies::add($b, ':fast up button', '&#x23EB');
		Smilies::add($b, ':downwards button', '&#x1F53D');
		Smilies::add($b, ':fast down button', '&#x23EC');
		Smilies::add($b, ':pause button', '&#x23F8');
		Smilies::add($b, ':stop button', '&#x23F9');
		Smilies::add($b, ':record button', '&#x23FA');
		Smilies::add($b, ':eject button', '&#x23CF');
		Smilies::add($b, ':cinema', '&#x1F3A6');
		Smilies::add($b, ':dim button', '&#x1F505');
		Smilies::add($b, ':bright button', '&#x1F506');
		Smilies::add($b, ':antenna bars', '&#x1F4F6');
		Smilies::add($b, ':vibration mode', '&#x1F4F3');
		Smilies::add($b, ':mobile phone off', '&#x1F4F4');
    
// gender
		Smilies::add($b, ':female sign', '&#x2640');
		Smilies::add($b, ':male sign', '&#x2642');
    
// math
		Smilies::add($b, ':multiply', '&#x2716');
		Smilies::add($b, ':plus', '&#x2795');
		Smilies::add($b, ':minus', '&#x2796');
		Smilies::add($b, ':divide', '&#x2797');
		Smilies::add($b, ':infinity', '&#x267E');
		Smilies::add($b, ':kreisoperator', '&#x2218;');
		Smilies::add($b, ':leere menge', '&#x2205;');
		Smilies::add($b, ':rundung', '&#x2248;');
		Smilies::add($b, ':gleichung', '&#x003D;');
		Smilies::add($b, ':ungleichung', '&#x2260;');
		Smilies::add($b, ':kleiner als', '&#x003C;');
		Smilies::add($b, ':groesser als', '&#x003E;');
		Smilies::add($b, ':prozent', '&#x0025;');
    
// punctuation
		Smilies::add($b, ':double exclamation mark', '&#x203C');
		Smilies::add($b, ':exclamation question mark', '&#x2049');
		Smilies::add($b, ':question mark', '&#x2753');
		Smilies::add($b, ':white question mark', '&#x2754');
		Smilies::add($b, ':white exclamation mark', '&#x2755');
		Smilies::add($b, ':exclamation mark', '&#x2757');
		Smilies::add($b, ':wavy dash', '&#x3030');
    
// currency
		Smilies::add($b, ':currency exchange', '&#x1F4B1');
		Smilies::add($b, ':heavy dollar sign', '&#x1F4B2');
    
// other-symbol
		Smilies::add($b, ':medical symbol', '&#x2695');
		Smilies::add($b, ':recycling symbol', '&#x267B');
		Smilies::add($b, ':fleur-de-lis', '&#x269C');
		Smilies::add($b, ':trident emblem', '&#x1F531');
		Smilies::add($b, ':name badge', '&#x1F4DB');
		Smilies::add($b, ':Japanese symbol for beginner', '&#x1F530');
		Smilies::add($b, ':hollow red circle', '&#x2B55');
		Smilies::add($b, ':check mark button', '&#x2705');
		Smilies::add($b, ':check box with check', '&#x2611');
		Smilies::add($b, ':check mark', '&#x2714');
		Smilies::add($b, ':cross mark', '&#x274C');
		Smilies::add($b, ':cross mark button', '&#x274E');
		Smilies::add($b, ':curly loop', '&#x27B0');
		Smilies::add($b, ':double curly loop', '&#x27BF');
		Smilies::add($b, ':part alternation mark', '&#x303D');
		Smilies::add($b, ':eight-spoked asterisk', '&#x2733');
		Smilies::add($b, ':eight-pointed star', '&#x2734');
		Smilies::add($b, ':sparkle', '&#x2747');
		Smilies::add($b, ':copyright', '&#x00A9');
		Smilies::add($b, ':registered', '&#x00AE');
		Smilies::add($b, ':trade mark', '&#x2122');

// keycap
		Smilies::add($b, ':keycap: #', '&#x0023&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: *', '&#x002A&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 0', '&#x0030&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 1', '&#x0031&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 2', '&#x0032&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 3', '&#x0033&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 4', '&#x0034&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 5', '&#x0035&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 6', '&#x0036&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 7', '&#x0037&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 8', '&#x0038&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 9', '&#x0039&#xFE0F&#x20E3');
		Smilies::add($b, ':keycap: 10', '&#x1F51F');
    
// alphanum
		Smilies::add($b, ':input latin uppercase', '&#x1F520');
		Smilies::add($b, ':input latin lowercase', '&#x1F521');
		Smilies::add($b, ':input numbers', '&#x1F522');
		Smilies::add($b, ':input symbols', '&#x1F523');
		Smilies::add($b, ':input latin letters', '&#x1F524');
		Smilies::add($b, ':A button (blood type)', '&#x1F170');
		Smilies::add($b, ':AB button (blood type)', '&#x1F18E');
		Smilies::add($b, ':B button (blood type)', '&#x1F171');
		Smilies::add($b, ':CL button', '&#x1F191');
		Smilies::add($b, ':COOL button', '&#x1F192');
		Smilies::add($b, ':FREE button', '&#x1F193');
		Smilies::add($b, ':information', '&#x2139');
		Smilies::add($b, ':ID button', '&#x1F194');
		Smilies::add($b, ':circled M', '&#x24C2');
		Smilies::add($b, ':NEW button', '&#x1F195');
		Smilies::add($b, ':NG button', '&#x1F196');
		Smilies::add($b, ':O button (blood type)', '&#x1F17E');
		Smilies::add($b, ':OK button', '&#x1F197');
		Smilies::add($b, ':P button', '&#x1F17F');
		Smilies::add($b, ':SOS button', '&#x1F198');  
		Smilies::add($b, ':UP! button', '&#x1F199');
		Smilies::add($b, ':VS button', '&#x1F19A');
		Smilies::add($b, ':Japanese “here” button', '&#x1F201');
		Smilies::add($b, ':Japanese “service charge” button', '&#x1F202');
		Smilies::add($b, ':Japanese “monthly amount” button', '&#x1F237');
		Smilies::add($b, ':Japanese “not free of charge” button', '&#x1F236');
		Smilies::add($b, ':Japanese “reserved” button', '&#x1F22F');
		Smilies::add($b, ':Japanese “bargain” button', '&#x1F250');
		Smilies::add($b, ':Japanese “discount” button', '&#x1F239');
		Smilies::add($b, ':Japanese “free of charge” button', '&#x1F21A');
		Smilies::add($b, ':Japanese “prohibited” button', '&#x1F232');
		Smilies::add($b, ':Japanese “acceptable” button', '&#x1F251');
		Smilies::add($b, ':Japanese “application” button', '&#x1F238');
		Smilies::add($b, ':Japanese “passing grade” button', '&#x1F234');
		Smilies::add($b, ':Japanese “vacancy” button', '&#x1F233');
		Smilies::add($b, ':Japanese “congratulations” button', '&#x3297');
		Smilies::add($b, ':Japanese “secret” button', '&#x3299');
		Smilies::add($b, ':Japanese “open for business” button', '&#x1F23A');
		Smilies::add($b, ':Japanese “no vacancy” button', '&#x1F235');

// geometric
    		Smilies::add($b, ':red circle', '&#x1F534');
//  		Smilies::add($b, ':orange circle', '&#x1F7E0');
//  		Smilies::add($b, ':yellow circle', '&#x1F7E1');
//  		Smilies::add($b, ':green circle', '&#x1F7E2');
    		Smilies::add($b, ':blue circle', '&#x1F535');
//  		Smilies::add($b, ':purple circle', '&#x1F7E3');
//  		Smilies::add($b, ':brown circle', '&#x1F7E4');
		Smilies::add($b, ':black circle', '&#x26AB');
		Smilies::add($b, ':white circle', '&#x26AA');
//  		Smilies::add($b, ':red square', '&#x1F7E5');
//  		Smilies::add($b, ':orange square', '&#x1F7E7');
//  		Smilies::add($b, ':yellow square', '&#x1F7E8');
//  		Smilies::add($b, ':green square', '&#x1F7E9');
//  		Smilies::add($b, ':blue square', '&#x1F7E6');
//  		Smilies::add($b, ':purple square', '&#x1F7EA');
//  		Smilies::add($b, ':brown square', '&#x1F7EB');
		Smilies::add($b, ':black large square', '&#x2B1B');
		Smilies::add($b, ':white large square', '&#x2B1C');
		Smilies::add($b, ':black medium square', '&#x25FC');
		Smilies::add($b, ':white medium square', '&#x25FB');
		Smilies::add($b, ':black medium-small square', '&#x25FE');
		Smilies::add($b, ':white medium-small square', '&#x25FD');
		Smilies::add($b, ':black small square', '&#x25AA');
		Smilies::add($b, ':white small square', '&#x25AB');
		Smilies::add($b, ':large orange diamond', '&#x1F536');
		Smilies::add($b, ':large blue diamond', '&#x1F537');
		Smilies::add($b, ':small orange diamond', '&#x1F538');
		Smilies::add($b, ':small blue diamond', '&#x1F539');
		Smilies::add($b, ':red triangle pointed up', '&#x1F53A');
		Smilies::add($b, ':red triangle pointed down', '&#x1F53B');
		Smilies::add($b, ':diamond with a dot', '&#x1F4A0');
		Smilies::add($b, ':radio button', '&#x1F518');
		Smilies::add($b, ':white square button', '&#x1F533');
		Smilies::add($b, ':black square button', '&#x1F532');

// Flags
// flag
		Smilies::add($b, ':chequered flag', '&#x1F3C1');
		Smilies::add($b, ':triangular flag', '&#x1F6A9');
		Smilies::add($b, ':crossed flags', '&#x1F38C');
		Smilies::add($b, ':black flag', '&#x1F3F4');
		Smilies::add($b, ':white flag', '&#x1F3F3');
		Smilies::add($b, ':rainbow flag', '&#x1F3F3&#xFE0F&#x200D&#x1F308');
		Smilies::add($b, ':pirate flag', '&#x1F3F4&#x200D&#x2620&#xFE0F');
    
// country-flag
		Smilies::add($b, ':ascension island', '&#x1F1E6&#x1F1E8');
		Smilies::add($b, ':andorra', '&#x1F1E6&#x1F1E9');
		Smilies::add($b, ':united arab emirates', '&#x1F1E6&#x1F1EA');
		Smilies::add($b, ':afghanistan', '&#x1F1E6&#x1F1EB');
		Smilies::add($b, ':antigua & barbuda', '&#x1F1E6&#x1F1EC');
		Smilies::add($b, ':anguilla', '&#x1F1E6&#x1F1EE');
		Smilies::add($b, ':albania', '&#x1F1E6&#x1F1F1');
		Smilies::add($b, ':armenia', '&#x1F1E6&#x1F1F2');
		Smilies::add($b, ':angola', '&#x1F1E6&#x1F1F4');
		Smilies::add($b, ':antarctica', '&#x1F1E6&#x1F1F6');
		Smilies::add($b, ':argentina', '&#x1F1E6&#x1F1F7');
		Smilies::add($b, ':americansamoa', '&#x1F1E6&#x1F1F8');
		Smilies::add($b, ':austria', '&#x1F1E6&#x1F1F9');
		Smilies::add($b, ':australia', '&#x1F1E6&#x1F1FA');
		Smilies::add($b, ':aruba', '&#x1F1E6&#x1F1FC');
		Smilies::add($b, ':ålandislands', '&#x1F1E6&#x1F1FD');
		Smilies::add($b, ':azerbaijan', '&#x1F1E6&#x1F1FF');
		Smilies::add($b, ':bosnia&herzegovina', '&#x1F1E7&#x1F1E6');
		Smilies::add($b, ':barbados', '&#x1F1E7&#x1F1E7');
		Smilies::add($b, ':bangladesh', '&#x1F1E7&#x1F1E9');
		Smilies::add($b, ':belgium', '&#x1F1E7&#x1F1EA');
		Smilies::add($b, ':burkinafaso', '&#x1F1E7&#x1F1EB');
		Smilies::add($b, ':bulgaria', '&#x1F1E7&#x1F1EC');
		Smilies::add($b, ':bahrain', '&#x1F1E7&#x1F1ED');
		Smilies::add($b, ':burundi', '&#x1F1E7&#x1F1EE');
		Smilies::add($b, ':benin', '&#x1F1E7&#x1F1EF');
		Smilies::add($b, ':st.barthélemy', '&#x1F1E7&#x1F1F1');
		Smilies::add($b, ':bermuda', '&#x1F1E7&#x1F1F2');
		Smilies::add($b, ':brunei', '&#x1F1E7&#x1F1F3');
		Smilies::add($b, ':bolivia', '&#x1F1E7&#x1F1F4');
		Smilies::add($b, ':caribbeannetherlands', '&#x1F1E7&#x1F1F6');
		Smilies::add($b, ':brazil', '&#x1F1E7&#x1F1F7');
		Smilies::add($b, ':bahamas', '&#x1F1E7&#x1F1F8');
		Smilies::add($b, ':bhutan', '&#x1F1E7&#x1F1F9');
		Smilies::add($b, ':bouvetisland', '&#x1F1E7&#x1F1FB');
		Smilies::add($b, ':botswana', '&#x1F1E7&#x1F1FC');
		Smilies::add($b, ':belarus', '&#x1F1E7&#x1F1FE');
		Smilies::add($b, ':belize', '&#x1F1E7&#x1F1FF');
		Smilies::add($b, ':canada', '&#x1F1E8&#x1F1E6');
		Smilies::add($b, ':cocos(keeling)islands', '&#x1F1E8&#x1F1E8');
		Smilies::add($b, ':congo-kinshasa', '&#x1F1E8&#x1F1E9');
		Smilies::add($b, ':centralafricanrepublic', '&#x1F1E8&#x1F1EB');
		Smilies::add($b, ':congo-brazzaville', '&#x1F1E8&#x1F1EC');
		Smilies::add($b, ':switzerland', '&#x1F1E8&#x1F1ED');
		Smilies::add($b, ':côted’ivoire', '&#x1F1E8&#x1F1EE');
		Smilies::add($b, ':cookislands', '&#x1F1E8&#x1F1F0');
		Smilies::add($b, ':chile', '&#x1F1E8&#x1F1F1');
		Smilies::add($b, ':cameroon', '&#x1F1E8&#x1F1F2');
		Smilies::add($b, ':china', '&#x1F1E8&#x1F1F3');
		Smilies::add($b, ':colombia', '&#x1F1E8&#x1F1F4');
		Smilies::add($b, ':clippertonisland', '&#x1F1E8&#x1F1F5');
		Smilies::add($b, ':costarica', '&#x1F1E8&#x1F1F7');
		Smilies::add($b, ':cuba', '&#x1F1E8&#x1F1FA');
		Smilies::add($b, ':capeverde', '&#x1F1E8&#x1F1FB');
		Smilies::add($b, ':curaçao', '&#x1F1E8&#x1F1FC');
		Smilies::add($b, ':christmasisland', '&#x1F1E8&#x1F1FD');
		Smilies::add($b, ':cyprus', '&#x1F1E8&#x1F1FE');
		Smilies::add($b, ':czechia', '&#x1F1E8&#x1F1FF');
		Smilies::add($b, ':germany', '&#x1F1E9&#x1F1EA');
		Smilies::add($b, ':diegogarcia', '&#x1F1E9&#x1F1EC');
		Smilies::add($b, ':djibouti', '&#x1F1E9&#x1F1EF');
		Smilies::add($b, ':denmark', '&#x1F1E9&#x1F1F0');
		Smilies::add($b, ':dominica', '&#x1F1E9&#x1F1F2');
		Smilies::add($b, ':dominicanrepublic', '&#x1F1E9&#x1F1F4');
		Smilies::add($b, ':algeria', '&#x1F1E9&#x1F1FF');
		Smilies::add($b, ':ceuta&melilla', '&#x1F1EA&#x1F1E6');
		Smilies::add($b, ':ecuador', '&#x1F1EA&#x1F1E8');
		Smilies::add($b, ':estonia', '&#x1F1EA&#x1F1EA');
		Smilies::add($b, ':egypt', '&#x1F1EA&#x1F1EC');
		Smilies::add($b, ':westernsahara', '&#x1F1EA&#x1F1ED');
		Smilies::add($b, ':eritrea', '&#x1F1EA&#x1F1F7');
		Smilies::add($b, ':spain', '&#x1F1EA&#x1F1F8');
		Smilies::add($b, ':ethiopia', '&#x1F1EA&#x1F1F9');
		Smilies::add($b, ':europeanunion', '&#x1F1EA&#x1F1FA');
		Smilies::add($b, ':finland', '&#x1F1EB&#x1F1EE');
		Smilies::add($b, ':fiji', '&#x1F1EB&#x1F1EF');
		Smilies::add($b, ':falklandislands', '&#x1F1EB&#x1F1F0');
		Smilies::add($b, ':micronesia', '&#x1F1EB&#x1F1F2');
		Smilies::add($b, ':faroeislands', '&#x1F1EB&#x1F1F4');
		Smilies::add($b, ':france', '&#x1F1EB&#x1F1F7');
		Smilies::add($b, ':gabon', '&#x1F1EC&#x1F1E6');
		Smilies::add($b, ':unitedkingdom', '&#x1F1EC&#x1F1E7');
		Smilies::add($b, ':grenada', '&#x1F1EC&#x1F1E9');
		Smilies::add($b, ':georgia', '&#x1F1EC&#x1F1EA');
		Smilies::add($b, ':frenchguiana', '&#x1F1EC&#x1F1EB');
		Smilies::add($b, ':guernsey', '&#x1F1EC&#x1F1EC');
		Smilies::add($b, ':ghana', '&#x1F1EC&#x1F1ED');
		Smilies::add($b, ':gibraltar', '&#x1F1EC&#x1F1EE');
		Smilies::add($b, ':greenland', '&#x1F1EC&#x1F1F1');
		Smilies::add($b, ':gambia', '&#x1F1EC&#x1F1F2');
		Smilies::add($b, ':guinea', '&#x1F1EC&#x1F1F3');
		Smilies::add($b, ':guadeloupe', '&#x1F1EC&#x1F1F5');
		Smilies::add($b, ':equatorialguinea', '&#x1F1EC&#x1F1F6');
		Smilies::add($b, ':greece', '&#x1F1EC&#x1F1F7');
		Smilies::add($b, ':southgeorgia&southsandwichislands', '&#x1F1EC&#x1F1F8');
		Smilies::add($b, ':guatemala', '&#x1F1EC&#x1F1F9');
		Smilies::add($b, ':guam', '&#x1F1EC&#x1F1FA');
		Smilies::add($b, ':guinea-bissau', '&#x1F1EC&#x1F1FC');
		Smilies::add($b, ':guyana', '&#x1F1EC&#x1F1FE');
		Smilies::add($b, ':hongkongsarchina', '&#x1F1ED&#x1F1F0');
		Smilies::add($b, ':heard&mcdonaldislands', '&#x1F1ED&#x1F1F2');
		Smilies::add($b, ':honduras', '&#x1F1ED&#x1F1F3');
		Smilies::add($b, ':croatia', '&#x1F1ED&#x1F1F7');
		Smilies::add($b, ':haiti', '&#x1F1ED&#x1F1F9');
		Smilies::add($b, ':hungary', '&#x1F1ED&#x1F1FA');
		Smilies::add($b, ':canaryislands', '&#x1F1EE&#x1F1E8');
		Smilies::add($b, ':indonesia', '&#x1F1EE&#x1F1E9');
		Smilies::add($b, ':ireland', '&#x1F1EE&#x1F1EA');
		Smilies::add($b, ':israel', '&#x1F1EE&#x1F1F1');
		Smilies::add($b, ':isleofman', '&#x1F1EE&#x1F1F2');
		Smilies::add($b, ':india', '&#x1F1EE&#x1F1F3');
		Smilies::add($b, ':britishindianoceanterritory', '&#x1F1EE&#x1F1F4');
		Smilies::add($b, ':iraq', '&#x1F1EE&#x1F1F6');
		Smilies::add($b, ':iran', '&#x1F1EE&#x1F1F7');
		Smilies::add($b, ':iceland', '&#x1F1EE&#x1F1F8');
		Smilies::add($b, ':italy', '&#x1F1EE&#x1F1F9');
		Smilies::add($b, ':jersey', '&#x1F1EF&#x1F1EA');
		Smilies::add($b, ':jamaica', '&#x1F1EF&#x1F1F2');
		Smilies::add($b, ':jordan', '&#x1F1EF&#x1F1F4');
		Smilies::add($b, ':japan', '&#x1F1EF&#x1F1F5');
		Smilies::add($b, ':kenya', '&#x1F1F0&#x1F1EA');
		Smilies::add($b, ':kyrgyzstan', '&#x1F1F0&#x1F1EC');
		Smilies::add($b, ':cambodia', '&#x1F1F0&#x1F1ED');
		Smilies::add($b, ':kiribati', '&#x1F1F0&#x1F1EE');
		Smilies::add($b, ':comoros', '&#x1F1F0&#x1F1F2');
		Smilies::add($b, ':st.kitts&nevis', '&#x1F1F0&#x1F1F3');
		Smilies::add($b, ':northkorea', '&#x1F1F0&#x1F1F5');
		Smilies::add($b, ':southkorea', '&#x1F1F0&#x1F1F7');
		Smilies::add($b, ':kuwait', '&#x1F1F0&#x1F1FC');
		Smilies::add($b, ':caymanislands', '&#x1F1F0&#x1F1FE');
		Smilies::add($b, ':kazakhstan', '&#x1F1F0&#x1F1FF');
		Smilies::add($b, ':laos', '&#x1F1F1&#x1F1E6');
		Smilies::add($b, ':lebanon', '&#x1F1F1&#x1F1E7');
		Smilies::add($b, ':st.lucia', '&#x1F1F1&#x1F1E8');
		Smilies::add($b, ':liechtenstein', '&#x1F1F1&#x1F1EE');
		Smilies::add($b, ':srilanka', '&#x1F1F1&#x1F1F0');
		Smilies::add($b, ':liberia', '&#x1F1F1&#x1F1F7');
		Smilies::add($b, ':lesotho', '&#x1F1F1&#x1F1F8');
		Smilies::add($b, ':lithuania', '&#x1F1F1&#x1F1F9');
		Smilies::add($b, ':luxembourg', '&#x1F1F1&#x1F1FA');
		Smilies::add($b, ':latvia', '&#x1F1F1&#x1F1FB');
		Smilies::add($b, ':libya', '&#x1F1F1&#x1F1FE');
		Smilies::add($b, ':morocco', '&#x1F1F2&#x1F1E6');
		Smilies::add($b, ':monaco', '&#x1F1F2&#x1F1E8');
		Smilies::add($b, ':moldova', '&#x1F1F2&#x1F1E9');
		Smilies::add($b, ':montenegro', '&#x1F1F2&#x1F1EA');
		Smilies::add($b, ':st.martin', '&#x1F1F2&#x1F1EB');
		Smilies::add($b, ':madagascar', '&#x1F1F2&#x1F1EC');
		Smilies::add($b, ':marshallislands', '&#x1F1F2&#x1F1ED');
		Smilies::add($b, ':northmacedonia', '&#x1F1F2&#x1F1F0');
		Smilies::add($b, ':mali', '&#x1F1F2&#x1F1F1');
		Smilies::add($b, ':myanmar(burma)', '&#x1F1F2&#x1F1F2');
		Smilies::add($b, ':mongolia', '&#x1F1F2&#x1F1F3');
		Smilies::add($b, ':macaosarchina', '&#x1F1F2&#x1F1F4');
		Smilies::add($b, ':northernmarianaislands', '&#x1F1F2&#x1F1F5');
		Smilies::add($b, ':martinique', '&#x1F1F2&#x1F1F6');
		Smilies::add($b, ':mauritania', '&#x1F1F2&#x1F1F7');
		Smilies::add($b, ':montserrat', '&#x1F1F2&#x1F1F8');
		Smilies::add($b, ':malta', '&#x1F1F2&#x1F1F9');
//  		Smilies::add($b, ':mauritius', '&#x1F1F2 1F1FA');
		Smilies::add($b, ':maldives', '&#x1F1F2&#x1F1FB');
		Smilies::add($b, ':malawi', '&#x1F1F2&#x1F1FC');
		Smilies::add($b, ':mexico', '&#x1F1F2&#x1F1FD');
		Smilies::add($b, ':malaysia', '&#x1F1F2&#x1F1FE');
		Smilies::add($b, ':mozambique', '&#x1F1F2&#x1F1FF');
		Smilies::add($b, ':namibia', '&#x1F1F3&#x1F1E6');
		Smilies::add($b, ':newcaledonia', '&#x1F1F3&#x1F1E8');
		Smilies::add($b, ':niger', '&#x1F1F3&#x1F1EA');
		Smilies::add($b, ':norfolkisland', '&#x1F1F3&#x1F1EB');
		Smilies::add($b, ':nigeria', '&#x1F1F3&#x1F1EC');
		Smilies::add($b, ':nicaragua', '&#x1F1F3&#x1F1EE');
		Smilies::add($b, ':netherlands', '&#x1F1F3&#x1F1F1');
		Smilies::add($b, ':norway', '&#x1F1F3&#x1F1F4');
		Smilies::add($b, ':nepal', '&#x1F1F3&#x1F1F5');
		Smilies::add($b, ':nauru', '&#x1F1F3&#x1F1F7');
		Smilies::add($b, ':niue', '&#x1F1F3&#x1F1FA');
		Smilies::add($b, ':newzealand', '&#x1F1F3&#x1F1FF');
		Smilies::add($b, ':oman', '&#x1F1F4&#x1F1F2');
		Smilies::add($b, ':panama', '&#x1F1F5&#x1F1E6');
		Smilies::add($b, ':peru', '&#x1F1F5&#x1F1EA');
		Smilies::add($b, ':frenchpolynesia', '&#x1F1F5&#x1F1EB');
		Smilies::add($b, ':papuanewguinea', '&#x1F1F5&#x1F1EC');
		Smilies::add($b, ':philippines', '&#x1F1F5&#x1F1ED');
		Smilies::add($b, ':pakistan', '&#x1F1F5&#x1F1F0');
		Smilies::add($b, ':poland', '&#x1F1F5&#x1F1F1');
		Smilies::add($b, ':st.pierre&miquelon', '&#x1F1F5&#x1F1F2');
		Smilies::add($b, ':pitcairnislands', '&#x1F1F5&#x1F1F3');
		Smilies::add($b, ':puertorico', '&#x1F1F5&#x1F1F7');
		Smilies::add($b, ':palestinianterritories', '&#x1F1F5&#x1F1F8');
		Smilies::add($b, ':portugal', '&#x1F1F5&#x1F1F9');
		Smilies::add($b, ':palau', '&#x1F1F5&#x1F1FC');
		Smilies::add($b, ':paraguay', '&#x1F1F5&#x1F1FE');
		Smilies::add($b, ':qatar', '&#x1F1F6&#x1F1E6');
		Smilies::add($b, ':réunion', '&#x1F1F7&#x1F1EA');
		Smilies::add($b, ':romania', '&#x1F1F7&#x1F1F4');
		Smilies::add($b, ':serbia', '&#x1F1F7&#x1F1F8');
		Smilies::add($b, ':russia', '&#x1F1F7&#x1F1FA');
		Smilies::add($b, ':rwanda', '&#x1F1F7&#x1F1FC');
		Smilies::add($b, ':saudiarabia', '&#x1F1F8&#x1F1E6');
		Smilies::add($b, ':solomonislands', '&#x1F1F8&#x1F1E7');
		Smilies::add($b, ':seychelles', '&#x1F1F8&#x1F1E8');
		Smilies::add($b, ':sudan', '&#x1F1F8&#x1F1E9');
		Smilies::add($b, ':sweden', '&#x1F1F8&#x1F1EA');
		Smilies::add($b, ':singapore', '&#x1F1F8&#x1F1EC');
		Smilies::add($b, ':st.helena', '&#x1F1F8&#x1F1ED');
		Smilies::add($b, ':slovenia', '&#x1F1F8&#x1F1EE');
		Smilies::add($b, ':svalbard&janmayen', '&#x1F1F8&#x1F1EF');
		Smilies::add($b, ':slovakia', '&#x1F1F8&#x1F1F0');
		Smilies::add($b, ':sierraleone', '&#x1F1F8&#x1F1F1');
		Smilies::add($b, ':sanmarino', '&#x1F1F8&#x1F1F2');
		Smilies::add($b, ':senegal', '&#x1F1F8&#x1F1F3');
		Smilies::add($b, ':somalia', '&#x1F1F8&#x1F1F4');
		Smilies::add($b, ':suriname', '&#x1F1F8&#x1F1F7');
		Smilies::add($b, ':southsudan', '&#x1F1F8&#x1F1F8');
		Smilies::add($b, ':sãotomé&príncipe', '&#x1F1F8&#x1F1F9');
		Smilies::add($b, ':elsalvador', '&#x1F1F8&#x1F1FB');
		Smilies::add($b, ':sintmaarten', '&#x1F1F8&#x1F1FD');
		Smilies::add($b, ':syria', '&#x1F1F8&#x1F1FE');
		Smilies::add($b, ':eswatini', '&#x1F1F8&#x1F1FF');
		Smilies::add($b, ':tristandacunha', '&#x1F1F9&#x1F1E6');
		Smilies::add($b, ':turks&caicosislands', '&#x1F1F9&#x1F1E8');
		Smilies::add($b, ':chad', '&#x1F1F9&#x1F1E9');
		Smilies::add($b, ':frenchsouthernterritories', '&#x1F1F9&#x1F1EB');
		Smilies::add($b, ':togo', '&#x1F1F9&#x1F1EC');
		Smilies::add($b, ':thailand', '&#x1F1F9&#x1F1ED');
		Smilies::add($b, ':tajikistan', '&#x1F1F9&#x1F1EF');
		Smilies::add($b, ':tokelau', '&#x1F1F9&#x1F1F0');
		Smilies::add($b, ':timor-leste', '&#x1F1F9&#x1F1F1');
		Smilies::add($b, ':turkmenistan', '&#x1F1F9&#x1F1F2');
		Smilies::add($b, ':tunisia', '&#x1F1F9&#x1F1F3');
		Smilies::add($b, ':tonga', '&#x1F1F9&#x1F1F4');
		Smilies::add($b, ':turkey', '&#x1F1F9&#x1F1F7');
		Smilies::add($b, ':trinidad&tobago', '&#x1F1F9&#x1F1F9');
		Smilies::add($b, ':tuvalu', '&#x1F1F9&#x1F1FB');
		Smilies::add($b, ':taiwan', '&#x1F1F9&#x1F1FC');
		Smilies::add($b, ':tanzania', '&#x1F1F9&#x1F1FF');
		Smilies::add($b, ':ukraine', '&#x1F1FA&#x1F1E6');
		Smilies::add($b, ':uganda', '&#x1F1FA&#x1F1EC');
		Smilies::add($b, ':u.s.outlyingislands', '&#x1F1FA&#x1F1F2');
		Smilies::add($b, ':unitednations', '&#x1F1FA&#x1F1F3');
		Smilies::add($b, ':unitedstates', '&#x1F1FA&#x1F1F8');
		Smilies::add($b, ':uruguay', '&#x1F1FA&#x1F1FE');
		Smilies::add($b, ':uzbekistan', '&#x1F1FA&#x1F1FF');
		Smilies::add($b, ':vaticancity', '&#x1F1FB&#x1F1E6');
		Smilies::add($b, ':st.vincent&grenadines', '&#x1F1FB&#x1F1E8');
		Smilies::add($b, ':venezuela', '&#x1F1FB&#x1F1EA');
		Smilies::add($b, ':britishvirginislands', '&#x1F1FB&#x1F1EC');
		Smilies::add($b, ':u.s.virginislands', '&#x1F1FB&#x1F1EE');
		Smilies::add($b, ':vietnam', '&#x1F1FB&#x1F1F3');
		Smilies::add($b, ':vanuatu', '&#x1F1FB&#x1F1FA');
		Smilies::add($b, ':wallis&futuna', '&#x1F1FC&#x1F1EB');
		Smilies::add($b, ':samoa', '&#x1F1FC&#x1F1F8');
		Smilies::add($b, ':kosovo', '&#x1F1FD&#x1F1F0');
		Smilies::add($b, ':yemen', '&#x1F1FE&#x1F1EA');
		Smilies::add($b, ':mayotte', '&#x1F1FE&#x1F1F9');
		Smilies::add($b, ':southafrica', '&#x1F1FF&#x1F1E6');
		Smilies::add($b, ':zambia', '&#x1F1FF&#x1F1F2');
		Smilies::add($b, ':zimbabwe', '&#x1F1FF&#x1F1FC');
    
// subdivision-flag
//  		Smilies::add($b, 'scotland', '&#x1F3F4&#xE0067&#xE0062&#xE0073&#xE0063&#xE0074&#xE007F');
    		Smilies::add($b, 'wales', '&#x1F3F4&#xE0067&#xE0077&#xE006C&#xE0073&#xE007F');
    		Smilies::add($b, 'wales', '&#x1F3F4&#xE0067&#xE0062&#xE0077&#xE006C&#xE0073&#xE007F');

}
