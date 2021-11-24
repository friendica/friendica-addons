<?php
/**
 * Name: Bird Avatar Generator
 * Description: Generate a default avatar based on David Revoy's bird-avatar-generator https://www.peppercarrot.com/extras/html/2019_bird-generator/index.php
 * Version: 1.0
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

define("BIRDAVATAR_SIZE", 256);

/**
 * Installs the addon hook
 */
function birdavatar_install()
{
	Hook::register('avatar_lookup', 'addon/birdavatar/birdavatar.php', 'birdavatar_lookup');
	Hook::register('addon_settings', 'addon/birdavatar/birdavatar.php', 'birdavatar_addon_settings');
	Hook::register('addon_settings_post', 'addon/birdavatar/birdavatar.php', 'birdavatar_addon_settings_post');

	Logger::log('registered birdavatar');
}

/**
 * Bird avatar user settings page
 */
function birdavatar_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/birdavatar/');
	$s .= Renderer::replaceMacros($t, [
		'$postpost'     => !empty($_POST['birdavatar-morebird']) || !empty($_POST['birdavatar-emailbird']),
		'$uncache'      => time(),
		'$uid'          => local_user(),
		'$usebird'      => DI::l10n()->t('Use Bird as Avatar'),
		'$morebird'     => DI::l10n()->t('More Random Bird!'),
		'$emailbird'    => DI::l10n()->t('Reset to email Bird'),
		'$seed'         => DI::pConfig()->get(local_user(), 'birdavatar', 'seed', false),
		'$header'       => DI::l10n()->t('Bird Avatar Settings'),
		'$setrandomize' => DI::l10n()->t('Set default profile avatar or randomize the bird.'),
	]);
}

/**
 * Bird avatar user settings POST handle
 */
function birdavatar_addon_settings_post(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['birdavatar-usebird'])) {
		$url = DI::baseUrl()->get() . '/birdavatar/' . local_user() . '?ts=' . time();

		$self = DBA::selectFirst('contact', ['id'], ['uid' => local_user(), 'self' => true]);
		if (!DBA::isResult($self)) {
			notice(DI::l10n()->t("The bird has not found itself."));
			return;
		}

		Photo::importProfilePhoto($url, local_user(), $self['id']);

		$condition = ['uid' => local_user(), 'contact-id' => $self['id']];
		$photo     = DBA::selectFirst('photo', ['resource-id'], $condition);
		if (!DBA::isResult($photo)) {
			notice(DI::l10n()->t('There was an error, the bird flew away.'));
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

	if (!empty($_POST['birdavatar-morebird'])) {
		DI::pConfig()->set(local_user(), 'birdavatar', 'seed', time());
	}

	if (!empty($_POST['birdavatar-emailbird'])) {
		DI::pConfig()->delete(local_user(), 'birdavatar', 'seed');
	}
}

/**
 * Returns the URL to the bird avatar
 *
 * @param $a array
 * @param &$b array
 */
function birdavatar_lookup(App $a, &$b)
{
	$user = DBA::selectFirst('user', ['uid'], ['email' => $b['email']]);
	if (DBA::isResult($user)) {
		$url = DI::baseUrl()->get() . '/birdavatar/' . $user['uid'];
	} else {
		$url = DI::baseUrl()->get() . '/birdavatar/' . md5(trim(strtolower($b['email'])));
	}

	switch ($b['size']) {
		case 300: $url .= "/4"; break;
		case 80: $url .= "/5"; break;
		case 48: $url .= "/6"; break;
	}

	$b['url']     = $url;
	$b['success'] = true;
}

function birdavatar_module()
{
}

/**
 * Returns image for user id
 *
 * @throws NotFoundException
 *
 */
function birdavatar_content(App $a)
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

		$seed = DI::pConfig()->get($uid, "birdavatar", "seed", md5(trim(strtolower($user['email']))));
	} elseif (!empty(DI::args()->getArgv()[1])) {
		$seed = DI::args()->getArgv()[1];
	} else {
		throw new NotFoundException();
	}

	$size = 0;
	if (DI::args()->getArgc() == 3) {
		$size = intval(DI::args()->getArgv()[2]);
	}

	// start generation
	ob_start();

	// render the picture:
	build_bird($seed, $size);

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

function build_bird($seed = '', $size = 0)
{
	// init random seed
	if ($seed) {
		srand(hexdec(substr(md5($seed), 0, 6)));
	}

	// throw the dice for body parts
	$parts = [
		'tail'       => rand(1,9),
		'hoop'       => rand(1,10),
		'body'       => rand(1,9),
		'wing'       => rand(1,9),
		'eyes'       => rand(1,9),
		'bec'        => rand(1,9),
		'accessorie' => rand(1,20)
	];

	// create backgound
	$bird = @imagecreatetruecolor(BIRDAVATAR_SIZE, BIRDAVATAR_SIZE)
		or die("GD image create failed");
	$white = imagecolorallocate($bird, 255, 255, 255);
	imagefill($bird, 0, 0, $white);

	// add parts
	foreach ($parts as $part => $num) {
		$file = dirname(__FILE__) . '/avatars/' . $part . '_' . $num . '.png';

		$im = @imagecreatefrompng($file);
		if (!$im) {
			die('Failed to load ' . $file);
		}
		imageSaveAlpha($im, true);
		imagecopy($bird, $im, 0, 0, 0, 0, BIRDAVATAR_SIZE, BIRDAVATAR_SIZE);
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

		$dest = imagecreatetruecolor($size, $size) or die("GD image create failed");
		imagealphablending($dest, false);
		imagesavealpha($dest, true);
		imagecopyresampled($dest, $bird, 0, 0, 0, 0, $size, $size, BIRDAVATAR_SIZE, BIRDAVATAR_SIZE);
		imagedestroy($bird);
		$bird = $dest;
	}

	// restore random seed
	if ($seed) {
		srand();
	}

	header('Pragma: public');
	header('Cache-Control: max-age=86400');
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
	header('Content-Type: image/jpg');
	imagejpeg($bird, null, 90);
	imagedestroy($bird);
}
