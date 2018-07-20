<?php
/*
 * Name: Unicode Smilies
 * Description: Smilies based on the unicode emojis - On Linux use https://github.com/eosrei/emojione-color-font to see them in color
 * Version: 1.0
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Content\Smilies;

function unicode_smilies_install() {
	Addon::registerHook('smilie', 'addon/unicode_smilies/unicode_smilies.php', 'unicode_smilies_smilies');
}

function unicode_smilies_uninstall() {
	Addon::unregisterHook('smilie', 'addon/unicode_smilies/unicode_smilies.php', 'unicode_smilies_smilies');
}

function unicode_smilies_smilies(App $a, array &$b) {
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
	Smilies::add($b, ':-O', '&#x1F62E;');
	Smilies::add($b, ':-X', '&#x1F910;');
	Smilies::add($b, ':-x', '&#x1F910;');
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
//	Smilies::add($b, 'o.O', '&#x;');
//	Smilies::add($b, 'O.o', '&#x;');
//	Smilies::add($b, 'o_O', '&#x;');
//	Smilies::add($b, 'O_o', '&#x;');
//	Smilies::add($b, ':facepalm', '&#x1F926;'); // Bad client support

// Animal smileys.

//	Smilies::add($b, ':bunnyflowers', '&#x;');
	Smilies::add($b, ':chick', '&#x1F424;');
	Smilies::add($b, ':bumblebee', '&#x1F41D;');
	Smilies::add($b, ':ladybird', '&#x1F41E;');
	Smilies::add($b, ':bigspider', '&#x1F577;');
	Smilies::add($b, ':cat', '&#x1F408;');
	Smilies::add($b, ':bunny', '&#x1F430;');
	Smilies::add($b, ':cow', '&#x1F42E;');
	Smilies::add($b, ':crab', '&#x1F980;');
	Smilies::add($b, ':dolphin', '&#x1F42C;');
//	Smilies::add($b, ':dragonfly', '&#x;');
	Smilies::add($b, ':frog', '&#x1F438;');
	Smilies::add($b, ':hamster', '&#x1F439;');
	Smilies::add($b, ':monkey', '&#x1F412;');
	Smilies::add($b, ':horse', '&#x1F434;');
//	Smilies::add($b, ':parrot', '&#x;');
	Smilies::add($b, ':tux', '&#x1F427;');
	Smilies::add($b, ':snail', '&#x1F40C;');
	Smilies::add($b, ':sheep', '&#x1F411;');
	Smilies::add($b, ':dog', '&#x1F436;');
	Smilies::add($b, ':elephant', '&#x1F418;');
	Smilies::add($b, ':fish', '&#x1F41F;');
//	Smilies::add($b, ':giraffe', '&#x1F992;'); // Bad client support
	Smilies::add($b, ':pig', '&#x1F416;');

// Baby Smileys

	Smilies::add($b, ':baby', '&#x1F476;');
//	Smilies::add($b, ':babycot', '&#x;');
//	Smilies::add($b, ':pregnant', '&#x1F930;'); // Bad client support
//	Smilies::add($b, ':stork', '&#x;');

// Confused Smileys

	Smilies::add($b, ':confused', '&#x1F615;');
	Smilies::add($b, ':shrug', '&#x1F937;');
//	Smilies::add($b, ':stupid', '&#x;');
//	Smilies::add($b, ':dazed', '&#x;');

// Cool Smileys

//	Smilies::add($b, ':affro', '&#x;');

// Devil/Angel Smileys

	Smilies::add($b, ':angel', '&#x1F47C;');
	Smilies::add($b, ':cherub', '&#x1F47C;');
//	Smilies::add($b, ':devilangel', '&#x;');
//	Smilies::add($b, ':catdevil', '&#x;');
//	Smilies::add($b, ':devillish', '&#x;');
//	Smilies::add($b, ':daseesaw', '&#x;');
//	Smilies::add($b, ':turnevil', '&#x;');
//	Smilies::add($b, ':saint', '&#x;');
//	Smilies::add($b, ':graveside', '&#x;');

// Unpleasent smileys.

	Smilies::add($b, ':toilet', '&#x1F6BD;');
//	Smilies::add($b, ':fartinbed', '&#x;');
//	Smilies::add($b, ':fartblush', '&#x;');

// Sad smileys

	Smilies::add($b, ':crying', '&#x1F622;');
//	Smilies::add($b, ':prisoner', '&#x;');
//	Smilies::add($b, ':sigh', '&#x;');

// Smoking - only one smiley in here, maybe it needs moving elsewhere?

	Smilies::add($b, ':smoking', '&#x1F6AC;');

// Sport smileys

	Smilies::add($b, ':basketball', '&#x1F3C0;');
	Smilies::add($b, '~bowling', '&#x1F3B3;');
	Smilies::add($b, ':cycling', '&#x1F6B4;');
	Smilies::add($b, ':darts', '&#x1F3AF;');
	Smilies::add($b, ':fencing', '&#x1F93A;');
	Smilies::add($b, ':juggling', '&#x1F939;');
//	Smilies::add($b, ':skipping', '&#x;');
//	Smilies::add($b, ':archery', '&#x;');
	Smilies::add($b, ':surfing', '&#x1F3C4;');
	Smilies::add($b, ':snooker', '&#x1F3B1;');
	Smilies::add($b, ':horseriding', '&#x1F3C7;');

// Love smileys

//	Smilies::add($b, ':iloveyou', '&#x;');
//	Smilies::add($b, ':inlove', '&#x;');
//	Smilies::add($b, '~love', '&#x;');
//	Smilies::add($b, ':lovebear', '&#x;');
//	Smilies::add($b, ':lovebed', '&#x;');
	Smilies::add($b, ':loveheart', '&#x1F496;');

// Tired/Sleep smileys

//	Smilies::add($b, ':countsheep', '&#x;');
//	Smilies::add($b, ':hammock', '&#x;');
//	Smilies::add($b, ':pillow', '&#x;');
//	Smilies::add($b, ':yawn', '&#x;');

// Fight/Flame/Violent smileys

//	Smilies::add($b, ':2guns', '&#x;');
//	Smilies::add($b, ':alienfight', '&#x;');
//	Smilies::add($b, ':army', '&#x;');
//	Smilies::add($b, ':arrowhead', '&#x;');
//	Smilies::add($b, ':bfg', '&#x;');
//	Smilies::add($b, ':bowman', '&#x;');
//	Smilies::add($b, ':chainsaw', '&#x;');
//	Smilies::add($b, ':crossbow', '&#x;');
//	Smilies::add($b, ':crusader', '&#x;');
//	Smilies::add($b, ':dead', '&#x;');
//	Smilies::add($b, ':hammersplat', '&#x;');
//	Smilies::add($b, ':lasergun', '&#x;');
//	Smilies::add($b, ':machinegun', '&#x;');
//	Smilies::add($b, ':acid', '&#x;');

// Fantasy smileys - monsters and dragons fantasy.  The other type of fantasy belongs in adult smileys

	Smilies::add($b, ':alienmonster', '&#x1F47E;');
//	Smilies::add($b, ':barbarian', '&#x;');
//	Smilies::add($b, ':dinosaur', '&#x;');
	Smilies::add($b, ':dragon', '&#x1F409;');
	Smilies::add($b, ':draco', '&#x1F409;');
	Smilies::add($b, ':ghost', '&#x1F47B;');
//	Smilies::add($b, ':mummy', '&#x;');

// Food smileys

	Smilies::add($b, ':apple', '&#x1F34E;');
//	Smilies::add($b, ':broccoli', '&#x;');
	Smilies::add($b, ':cake', '&#x1F370;');
//	Smilies::add($b, ':carrot', '&#x1F955;'); // Bad client support
	Smilies::add($b, ':popcorn', '&#x1F37F;');
	Smilies::add($b, ':tomato', '&#x1F345;');
	Smilies::add($b, ':banana', '&#x1F34C;');
	Smilies::add($b, ':cooking', '&#x1F373;');
	Smilies::add($b, ':fryegg', '&#x1F373;');
	Smilies::add($b, ':birthdaycake', '&#x1F382;');

// Happy smileys

//	Smilies::add($b, ':cloud9', '&#x;');
	Smilies::add($b, ':tearsofjoy', '&#x1F602;');

// Respect smileys

	Smilies::add($b, ':bow', '&#x1F647;');
//	Smilies::add($b, ':bravo', '&#x;');
//	Smilies::add($b, ':hailking', '&#x;');
//	Smilies::add($b, ':number1', '&#x;');

// Laugh smileys

//	Smilies::add($b, ':hahaha', '&#x;');
//	Smilies::add($b, ':loltv', '&#x;');
//	Smilies::add($b, ':rofl', '&#x1F923;'); // Bad client support
// Music smileys

//	Smilies::add($b, ':drums', '&#x1F941;'); // Bad client support
	Smilies::add($b, ':guitar', '&#x1F3B8;');
	Smilies::add($b, ':trumpet', '&#x1F3BA;');

// Smileys that used to be in core

//	Smilies::add($b, ':headbang', '&#x;');
//	Smilies::add($b, ':beard', '&#x1F9D4;'); // Bad client support
//	Smilies::add($b, ':whitebeard', '&#x;');
//	Smilies::add($b, ':shaka', '&#x;');
//	Smilies::add($b, ':\\.../', '&#x;');
//	Smilies::add($b, ':\\ooo/', '&#x;');
//	Smilies::add($b, ':headdesk', '&#x;');

// These two are still in core, so oldcore isn't strictly right, but we don't want too many directories

//	Smilies::add($b, ':-d', '&#x;');
	Smilies::add($b, ':-o', '&#x1F62E;');

//  Regex killers - stick these at the bottom so they appear at the end of the English and
//  at the start of $OtherLanguage.

// Drinks

	Smilies::add($b, ':tea', '&#x2615;');
//	Smilies::add($b, ':drool', '&#x1F924;'); // Bad client support

	Smilies::add($b, ':cool', '&#x1F192;');
//	Smilies::add($b, ':vomit', '&#x1F92E;'); // Bad client support
	Smilies::add($b, ':golf', '&#x1F3CC;');
	Smilies::add($b, ':football', '&#x1F3C8;');
	Smilies::add($b, ':tennis', '&#x1F3BE;');
//	Smilies::add($b, ':alpha', '&#x;');
//	Smilies::add($b, ':marine', '&#x;');
	Smilies::add($b, ':sabre', '&#x1F5E1;');
//	Smilies::add($b, ':tank', '&#x;');
//	Smilies::add($b, ':viking', '&#x;');
//	Smilies::add($b, ':gangs', '&#x;');
//	Smilies::add($b, ':dj', '&#x;');
//	Smilies::add($b, ':elvis', '&#x;');
	Smilies::add($b, ':violin', '&#x1F3BB;');
}
