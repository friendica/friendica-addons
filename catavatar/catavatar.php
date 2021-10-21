<?php
/**
 * Name: Cat Avatar Generator
 * Description: Generate a default avatar based on David Revoy's cat-avatar-generator https://framagit.org/Deevad/cat-avatar-generator
 * Version: 1.1
 * Author: Fabio <https://kirgroup.com/profile/fabrixxm>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Contact;
use Friendica\Model\Photo;
use Friendica\Model\Profile;
use Friendica\Network\HTTPException\NotFoundException;

define("CATAVATAR_SIZE", 256);

/**
 * Installs the addon hook
 */
function catavatar_install()
{
	Hook::register('avatar_lookup', 'addon/catavatar/catavatar.php', 'catavatar_lookup');
	Hook::register('addon_settings', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings');
	Hook::register('addon_settings_post', 'addon/catavatar/catavatar.php', 'catavatar_addon_settings_post');

	Logger::notice('registered catavatar');
}

/**
 * Cat avatar user settings page
 */
function catavatar_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/catavatar/');
	$s .= Renderer::replaceMacros($t, [
		'$postpost' => !empty($_POST['catavatar-morecat']) || !empty($_POST['catavatar-emailcat']),
		'$uncache' => time(),
		'$uid' => local_user(),
		'$usecat' => DI::l10n()->t('Use Cat as Avatar'),
		'$morecat' => DI::l10n()->t('More Random Cat!'),
		'$emailcat' => DI::l10n()->t('Reset to email Cat'),
		'$seed' => DI::pConfig()->get(local_user(), 'catavatar', 'seed', false),
		'$header' => DI::l10n()->t('Cat Avatar Settings'),
		'$setrandomize' => DI::l10n()->t('Set default profile avatar or randomize the cat.'),
	]);
}

/**
 * Cat avatar user settings POST handle
 */
function catavatar_addon_settings_post(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['catavatar-usecat'])) {
		$url = DI::baseUrl()->get() . '/catavatar/' . local_user() . '?ts=' . time();

		$self = DBA::selectFirst('contact', ['id'], ['uid' => local_user(), 'self' => true]);
		if (!DBA::isResult($self)) {
			notice(DI::l10n()->t("The cat hadn't found itself."));
			return;
		}

		Photo::importProfilePhoto($url, local_user(), $self['id']);

		$condition = ['uid' => local_user(), 'contact-id' => $self['id']];
		$photo = DBA::selectFirst('photo', ['resource-id'], $condition);
		if (!DBA::isResult($photo)) {
			notice(DI::l10n()->t('There was an error, the cat ran away.'));
			return;
		}

		DBA::update('photo', ['profile' => false], ['profile' => true, 'uid' => local_user()]);

		$fields = ['profile' => true, 'album' => DI::l10n()->t('Profile Photos'), 'contact-id' => 0];
		DBA::update('photo', $fields, ['uid' => local_user(), 'resource-id' => $photo['resource-id']]);

		Photo::importProfilePhoto($url, local_user(), $self['id']);

		Contact::updateSelfFromUserID(local_user(), true);

		// Update global directory in background
		Profile::publishUpdate(local_user());

		info(DI::l10n()->t('Meow!'));
		return;
	}

	if (!empty($_POST['catavatar-morecat'])) {
		DI::pConfig()->set(local_user(), 'catavatar', 'seed', time());
	}

	if (!empty($_POST['catavatar-emailcat'])) {
		DI::pConfig()->delete(local_user(), 'catavatar', 'seed');
	}
}

/**
 * Returns the URL to the cat avatar
 *
 * @param $a array
 * @param &$b array
 */
function catavatar_lookup(App $a, &$b)
{
	$user = DBA::selectFirst('user', ['uid'], ['email' => $b['email']]);
	if (DBA::isResult($user)) {
		$url = DI::baseUrl()->get() . '/catavatar/' . $user['uid'];
	} else {
		$url = DI::baseUrl()->get() . '/catavatar/' . md5(trim(strtolower($b['email'])));
	}

	switch($b['size']) {
		case 300: $url .= "/4"; break;
		case 80: $url .= "/5"; break;
		case 48: $url .= "/6"; break;
	}

	$b['url'] = $url;
	$b['success'] = true;
}

function catavatar_module() {}

/**
 * Returns image for user id
 *
 * @throws NotFoundException
 *
 */
function catavatar_content(App $a)
{
	if (DI::args()->getArgc() < 2 || DI::args()->getArgc() > 3) {
		throw new NotFoundException(); // this should be catched on index and show default "not found" page.
	}

	if (is_numeric(DI::args()->getArgv()[1])) {
		$uid = intval(DI::args()->getArgv()[1]);
		$condition = ['uid' => $uid,
				'account_expired' => false, 'account_removed' => false];
		$user = DBA::selectFirst('user', ['email'], $condition);

		if ($user === false) {
			throw new NotFoundException();
		}

		$seed = DI::pConfig()->get($uid, "catavatar", "seed", md5(trim(strtolower($user['email']))));
	} elseif (!empty(DI::args()->getArgv()[1])) {
		$seed = DI::args()->getArgv()[1];
	} else {
		throw new NotFoundException();
	}

	$size = 0;
	if (DI::args()->getArgc() == 3) {
		$size = intval(DI::args()->getArgv()[2]);
	}

	// ...Or start generation
	ob_start();

	// render the picture:
	build_cat($seed, $size);

	ob_end_flush();

	exit();
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

function build_cat($seed = '', $size = 0)
{
	// init random seed
	if ($seed) {
		srand(hexdec(substr(md5($seed), 0, 6)));
	}

	// throw the dice for body parts
	$parts = array(
		'body' => rand(1, 15),
		'fur' => rand(1, 10),
		'eyes' => rand(1, 15),
		'mouth' => rand(1, 10),
		'accessorie' => rand(1, 20)
	);

	// create backgound
	$cat = @imagecreatetruecolor(CATAVATAR_SIZE, CATAVATAR_SIZE)
		or die("GD image create failed");
	$white = imagecolorallocate($cat, 255, 255, 255);
	imagefill($cat, 0, 0, $white);

	// add parts
	foreach ($parts as $part => $num) {
		$file = dirname(__FILE__) . '/avatars/' . $part . '_' . $num . '.png';

		$im = @imagecreatefrompng($file);
		if (!$im) {
			die('Failed to load ' . $file);
		}
		imageSaveAlpha($im, true);
		imagecopy($cat, $im, 0, 0, 0, 0, CATAVATAR_SIZE, CATAVATAR_SIZE);
		imagedestroy($im);
	}

	// scale image
	if ($size > 3 && $size < 7) {
		switch ($size) {
			case 4:
				$size = 300;
				break;
			case 5:
				$size = 80;
				break;
			case 6:
				$size = 48;
				break;
		}

		$dest = imagecreatetruecolor($size, $size);
		imagealphablending($dest, false);
		imagesavealpha($dest, true);
		imagecopyresampled($dest, $cat, 0, 0, 0, 0, $size, $size, CATAVATAR_SIZE, CATAVATAR_SIZE);
		imagedestroy($cat);
		$cat = $dest;
	}

	// restore random seed
	if ($seed) {
		srand();
	}

	header('Pragma: public');
	header('Cache-Control: max-age=86400');
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
	header('Content-Type: image/jpg');
	imagejpeg($cat, NULL, 90);
	imagedestroy($cat);
}
