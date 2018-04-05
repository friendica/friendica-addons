<?php
/**
 * Name: Cat Avatar Generator
 * Description: Generate a default avatar based on David Revoy's cat-avatar-generator https://framagit.org/Deevad/cat-avatar-generator
 * Version: 1.1
 * Author: Fabio <https://kirgroup.com/profile/fabrixxm>
 */
use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\L10n;
use Friendica\Core\Worker;
use Friendica\Core\PConfig;
use Friendica\Util\DateTimeFormat;
use Friendica\Network\HTTPException\NotFoundException;

define("CATAVATAR_SIZE", 256);

/**
 * Installs the addon hook
 */
function catavatar_install() {
	Addon::registerHook('avatar_lookup', 'addon/catavatar/catavatar.php', 'catavatar_lookup');
	Addon::registerHook('addon_settings', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings_post');

	logger("registered catavatar");
}

/**
 * Removes the addon hook
 */
function catavatar_uninstall() {
	Addon::unregisterHook('avatar_lookup', 'addon/catavatar/catavatar.php', 'catavatar_lookup');
	Addon::unregisterHook('addon_settings', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings_post');
	
	logger("unregistered catavatar");
}


function catavatar_addon_settings(&$a, &$s) {
	if(! local_user())
		return;

	$t = get_markup_template("settings.tpl", "addon/catavatar/" );
	$s = replace_macros ($t, [
		'$postpost' => x($_POST,"catavatar-morecat") || x($_POST,"catavatar-emailcat"),
		'$uncache' => time(),
		'$uid' => local_user(),
		'$usecat' => L10n::t('Use Cat as Avatar'),
		'$morecat' => L10n::t('More Random Cat!'),
		'$emailcat' => L10n::t('Reset to email Cat'),
		'$seed' => PConfig::get(local_user(), "catavatar", "seed", false),
		'$header' => L10n::t('Cat Avatar').' '.L10n::t('Settings'),
	]);
	return;
}

function catavatar_addon_settings_post(&$a, &$s) {
	if(! local_user())
		return;
		
	// delete the current cached cat avatar
	$user = dba::selectFirst('user', ['email'],
		[
			'uid' => $uid,
			'blocked' => 0,
			'account_expired' => 0,
			'account_removed' => 0,
		]
	);
	$seed = PConfig::get(local_user(), "catavatar", "seed", md5(trim(strtolower($user['email']))));
	$imageurl = preg_replace('/[^A-Za-z0-9\._-]/', '', $seed); 
	$imageurl = substr($imageurl,0,35).'';
	$cachefile = get_cachefile($imageurl);
	if ($cachefile != "" && file_exists($cachefile)) {
		unlink($cachefile);
	}
		
		
	if (x($_POST,"catavatar-usecat")) {
		$url = $a->get_baseurl()."/catavatar/".local_user();
		
		// set the catavatar url as avatar url in contact and default profile
		// and set profile to 0 to current photo
		// I'm not sure it's the correct way to do this...
		$r = dba::update('contact', 
			['photo'=>$url."/4", 'thumb'=>$url."/5", 'micro'=>$url."/6", 'avatar-date'=>DateTimeFormat::utcNow()], 
			['uid'=>local_user(), 'self'=>1]
		);
		if ($r===false) {
			notice(L10n::t('There was an error, the cat ran away.'));
			return;
		}

		$r = dba::update('profile', 
			['photo'=>$url."/4", 'thumb'=>$url."/5"], 
			['uid'=>local_user(), 'is-default'=>1]
		);
		if ($r===false) {
			notice(L10n::t('There was an error, the cat ran away.'));
			return;
		}

		$r = dba::update('photo', 
			['profile'=>0], 
			['uid'=>local_user(), 'profile'=>1]
		);
		if ($r===false) {
			notice(L10n::t('There was an error, the cat ran away.'));
			return;
		}


		// Update global directory in background
		$url = $a->get_baseurl() . '/profile/' . $a->user['nickname'];
		if ($url && strlen(Config::get('system','directory'))) {
			Worker::add(PRIORITY_LOW, "Directory", $url);
		}

		Worker::add(PRIORITY_LOW, 'ProfileUpdate', local_user());
		
		info(L10n::t("Meow!"));
		return;
	}
	


	if (x($_POST,"catavatar-morecat")) {
		PConfig::set(local_user(), "catavatar", "seed", time());
	}

	if (x($_POST,"catavatar-emailcat")) {
		PConfig::delete(local_user(), "catavatar", "seed");
	}
}


/**
 * Returns the URL to the cat avatar
 *
 * @param $a array
 * @param &$b array
 */
function catavatar_lookup($a, &$b) {
	$user = dba::selectFirst('user', ['uid'],['email'=>$b['email']]);
	
	$url = $a->get_baseurl().'/catavatar/'.$user['uid'];

	switch($b['size']) {
		case 175: $url.="/4"; break;
		case 80: $url.="/5"; break;
		case 47: $url.="/6"; break;
	}

	$b['url'] = $url;
	$b['success'] = true;
}


function catavatar_module(){}


/**
 * Returns image for user id
 *
 * @throws NotFoundException
 *
 * @TODO: support sizes
 */
function catavatar_content($a) {
	if ($a->argc < 2 || $a->argc > 3)
		throw new NotFoundException(); // this should be catched on index and show default "not found" page.

	$uid = intval($a->argv[1]);
	
	$size = 0;
	if ($a->argc == 3) {
		$size = intval($a->argv[2]);
	}
	
	$user = dba::selectFirst('user', ['email'],
		[
			'uid' => $uid,
			'blocked' => 0,
			'account_expired' => 0,
			'account_removed' => 0,
		]
	);	
	
	if ($user === False)
		throw new NotFoundException();
	
	$seed = PConfig::get(local_user(), "catavatar", "seed", md5(trim(strtolower($user['email']))));
	//echo "<pre>"; var_dump($hash); killme();

	
	// from cat-avatar-generator.php

	$imageurl = $seed."-".$size;
	$imageurl = preg_replace('/[^A-Za-z0-9\._-]/', '', $imageurl); 
	$imageurl = substr($imageurl,0,35).'';
	$cachefile = get_cachefile($imageurl);
	$cachetime = 604800; # 1 week (1 day = 86400)

	// Serve from the cache if it is younger than $cachetime
	if ($cachefile != "" && file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
		header('Pragma: public');
		header('Cache-Control: max-age=86400');
		header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
		header('Content-Type: image/jpg');
		readfile($cachefile);
		exit;
	}

	// ...Or start generation
	ob_start(); 

	// render the picture:
	build_cat($seed, $size);

	// Save/cache the output to a file
	if ($cachefile!=""){
		$savedfile = fopen($cachefile, 'w+'); # w+ to be at start of the file, write mode, and attempt to create if not existing.
		fwrite($savedfile, ob_get_contents());
		fclose($savedfile);
		chmod($cachefile, 0755);
	}
	ob_end_flush();

	killme();
}



/**
 * ====================
 * CAT-AVATAR-GENERATOR
 * ====================
 * 
 * @authors: Andreas Gohr, David Revoy
 * 
 * This PHP is licensed under the short and simple permissive:
 * [MIT License](https://en.wikipedia.org/wiki/MIT_License)
 * 
**/

function build_cat($seed='', $size=0){

	// init random seed
	if($seed) srand( hexdec(substr(md5($seed),0,6)) );

	// throw the dice for body parts
	$parts = array(
		'body' => rand(1,15),
		'fur' => rand(1,10),
		'eyes' => rand(1,15),
		'mouth' => rand(1,10),
		'accessorie' => rand(1,20)
	);

	// create backgound
	$cat = @imagecreatetruecolor(CATAVATAR_SIZE, CATAVATAR_SIZE)
		or die("GD image create failed");
	$white = imagecolorallocate($cat, 255, 255, 255);
	imagefill($cat,0,0,$white);

	// add parts
	foreach($parts as $part => $num){
		$file = dirname(__FILE__).'/avatars/'.$part.'_'.$num.'.png';

		$im = @imagecreatefrompng($file);
		if(!$im) die('Failed to load '.$file);
		imageSaveAlpha($im, true);
		imagecopy($cat,$im,0,0,0,0,CATAVATAR_SIZE,CATAVATAR_SIZE);
		imagedestroy($im);
	}

	// scale image
	if ($size > 3 && $size < 7) {
		switch($size) {
			case 4: $size = 175; break;
			case 5: $size = 80; break;
			case 6: $size = 48; break;
		}
	
		$dest = imagecreatetruecolor($size, $size);
		imagealphablending($dest, false);
		imagesavealpha($dest, true);
		imagecopyresampled($dest, $cat, 0, 0, 0, 0, $size, $size, CATAVATAR_SIZE, CATAVATAR_SIZE);
		imagedestroy($cat);
		$cat = $dest;
	}
	
	// restore random seed
	if($seed) srand();

	header('Pragma: public');
	header('Cache-Control: max-age=86400');
	header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
	header('Content-Type: image/jpg');
	imagejpeg($cat, NULL, 90);
	imagedestroy($cat);
}


