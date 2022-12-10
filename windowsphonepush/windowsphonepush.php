<?php

/**
 * Name: WindowsPhonePush
 * Description: Enable push notification to send information to Friendica Mobile app on Windows phone (count of unread timeline entries, text of last posting - if wished by user)
 * Version: 2.0
 * Author: Gerhard Seeber <http://friendica.seeber.at/profile/admin>
 * Status: Unsupported
 *
 *
 * Pre-requisite: Windows Phone mobile device (at least WP 7.0)
 *                Friendica mobile app on Windows Phone
 *
 * When addon is installed, the system calls the addon
 * name_install() function, located in 'addon/name/name.php',
 * where 'name' is the name of the addon.
 * If the addon is removed from the configuration list, the
 * system will call the name_uninstall() function.
 *
 * Version history:
 * 1.1  : addon crashed on php versions >= 5.4 as of removed deprecated call-time
 *        pass-by-reference used in function calls within function windowsphonepush_content
 * 2.0  : adaption for supporting emphasizing new entries in app (count on tile cannot be read out,
 *        so we need to retrieve counter through show_settings secondly). Provide new function for
 *        calling from app to set the counter back after start (if user starts again before cronjob
 *        sets the counter back
 *        count only unseen elements which are not type=activity (likes and dislikes not seen as new elements)
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Content\Text\HTML;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\User;
use Friendica\Network\HTTPException\UnauthorizedException;

function windowsphonepush_install()
{
	/* Our addon will attach in three places.
	 * The first is within cron - so the push notifications will be
	 * sent every 10 minutes (or whatever is set in crontab).
	 */
	Hook::register('cron', 'addon/windowsphonepush/windowsphonepush.php', 'windowsphonepush_cron');

	/* Then we'll attach into the addon settings page, and also the
	 * settings post hook so that we can create and update
	 * user preferences. User shall be able to activate the addon and
	 * define whether he allows pushing first characters of item text
	 */
	Hook::register('addon_settings', 'addon/windowsphonepush/windowsphonepush.php', 'windowsphonepush_settings');
	Hook::register('addon_settings_post', 'addon/windowsphonepush/windowsphonepush.php', 'windowsphonepush_settings_post');

	Logger::notice("installed windowsphonepush");
}

/* declare the windowsphonepush function so that /windowsphonepush url requests will land here */
/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function windowsphonepush_module() {}

/* Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 */
function windowsphonepush_settings_post(App $a, $post)
{
	if (!local_user() || empty($_POST['windowsphonepush-submit'])) {
		return;
	}
	$enable = intval($_POST['windowsphonepush']);
	DI::pConfig()->set(local_user(), 'windowsphonepush', 'enable', $enable);

	if ($enable) {
		DI::pConfig()->set(local_user(), 'windowsphonepush', 'counterunseen', 0);
	}

	DI::pConfig()->set(local_user(), 'windowsphonepush', 'senditemtext', intval($_POST['windowsphonepush-senditemtext']));
}

/* Called from the Addon Setting form.
 * Add our own settings info to the page.
 */
function windowsphonepush_settings(App &$a, array &$data)
{
	if (!local_user()) {
		return;
	}

	$enabled = DI::pConfig()->get(local_user(), 'windowsphonepush', 'enable');
	$senditemtext = DI::pConfig()->get(local_user(), 'windowsphonepush', 'senditemtext');
	$device_url = DI::pConfig()->get(local_user(), 'windowsphonepush', 'device_url');

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/windowsphonepush/');
	$html = Renderer::replaceMacros($t, [
		'$enabled'   => ['windowsphonepush', DI::l10n()->t('Enable WindowsPhonePush Addon'), $enabled],
		'$senditemtext'   => ['windowsphonepush-senditemtext', DI::l10n()->t('Push text of new item'), $senditemtext],
		'$device_url'   => ['', DI::l10n()->t('Device URL'), $device_url, '', false, ' readonly'],
	]);

	$data = [
		'addon' => 'windowsphonepush',
		'title' => DI::l10n()->t('WindowsPhonePush Settings'),
		'html'  => $html,
	];
}

/* Cron function used to regularly check all users on the server with active windowsphonepushaddon and send
 * notifications to the Microsoft servers and consequently to the Windows Phone device
 */
function windowsphonepush_cron()
{
	// retrieve all UID's for which the addon windowsphonepush is enabled and loop through every user
	$pconfigs = DBA::selectToArray('pconfig', ['uid'], ['cat' => 'windowsphonepush', 'k' => 'enable', 'v' => true]);
	foreach ($pconfigs as $rr) {
		// load stored information for the user-id of the current loop
		$device_url = DI::pConfig()->get($rr['uid'], 'windowsphonepush', 'device_url');
		$lastpushid = DI::pConfig()->get($rr['uid'], 'windowsphonepush', 'lastpushid');

		// pushing only possible if device_url (the URI on Microsoft server) is available or not "NA" (which will be sent
		// by app if user has switched the server setting in app - sending blank not possible as this would return an update error)
		if (( $device_url == "" ) || ( $device_url == "NA" )) {
			// no Device-URL for the user availabe, but addon is enabled --> write info to Logger
			Logger::notice("WARN: windowsphonepush is enable for user " . $rr['uid'] . ", but no Device-URL is specified for the user.");
		} else {
			// retrieve the number of unseen items and the id of the latest one (if there are more than
			// one new entries since last poller run, only the latest one will be pushed)
			$count = DBA::fetchFirst("SELECT count(`id`) AS count, max(`id`) AS max FROM `post-view` WHERE `unseen` AND `type` != ? AND `uid` = ?", 'activity', $rr['uid']);

			// send number of unseen items to the device (the number will be displayed on Start screen until
			// App will be started by user) - this update will be sent every 10 minutes to update the number to 0 if
			// user has loaded the timeline through app or website
			$res_tile = send_tile_update($device_url, "", $count['count'], "");
			switch (trim($res_tile)) {
				case "Received":
					// ok, count has been pushed, let's save it in personal settings
					DI::pConfig()->set($rr['uid'], 'windowsphonepush', 'counterunseen', $count['count']);
					break;
				case "QueueFull":
					// maximum of 30 messages reached, server rejects any further push notification until device reconnects
					Logger::notice("INFO: Device-URL '" . $device_url . "' returns a QueueFull.");
					break;
				case "Suppressed":
					// notification received and dropped as something in app was not enabled
					Logger::notice("WARN. Device-URL '" . $device_url . "' returns a Suppressed. Unexpected error in Mobile App?");
					break;
				case "Dropped":
					// mostly combines with Expired, in that case Device-URL will be deleted from pconfig (function send_push)
					break;
				default:
					// error, mostly called by "" which means that the url (not "" which has been checked)
					// didn't not received Microsoft Notification Server -> wrong url
					Logger::notice("ERROR: specified Device-URL '" . $device_url . "' didn't produced any response.");
			}

			// additionally user receives the text of the newest item (function checks against last successfully pushed item)
			if (intval($count['max']) > intval($lastpushid)) {
				// user can define if he wants to see the text of the item in the push notification
				// this has been implemented as the device_url is not a https uri (not so secure)
				$senditemtext = DI::pConfig()->get($rr['uid'], 'windowsphonepush', 'senditemtext');
				if ($senditemtext == 1) {
					// load item with the max id
					$item = Post::selectFirst(['author-name', 'body', 'uri-id'], ['id' => $count['max']]);

					// as user allows to send the item, we want to show the sender of the item in the toast
					// toasts are limited to one line, therefore place is limited - author shall be in
					// max. 15 chars (incl. dots); author is displayed in bold font
					$author = $item['author-name'];
					$author = ((strlen($author) > 12) ? substr($author, 0, 12) . "..." : $author);

					// normally we show the body of the item, however if it is an url or an image we cannot
					// show this in the toast (only test), therefore changing to an alternate text
					// Otherwise BBcode-Tags will be eliminated and plain text cutted to 140 chars (incl. dots)
					// BTW: information only possible in English
					$body = $item['body'];
					if (substr($body, 0, 4) == "[url") {
						$body = "URL/Image ...";
					} else {
						$body = BBCode::convertForUriId($item['uri-id'], $body, BBCode::API);
						$body = HTML::toPlaintext($body, 0);
						$body = ((strlen($body) > 137) ? substr($body, 0, 137) . "..." : $body);
					}
				} else {
					// if user wishes higher privacy, we only display "Friendica - New timeline entry arrived"
					$author = "Friendica";
					$body = "New timeline entry arrived ...";
				}
				// only if toast push notification returns the Notification status "Received" we will update th settings with the
				// new indicator max-id is checked against (QueueFull, Suppressed, N/A, Dropped shall qualify to resend
				// the push notification some minutes later (BTW: if resulting in Expired for subscription status the
				// device_url will be deleted (no further try on this url, see send_push)
				// further log information done on count pushing with send_tile (see above)
				$res_toast = send_toast($device_url, $author, $body);
				if (trim($res_toast) === 'Received') {
					DI::pConfig()->set($rr['uid'], 'windowsphonepush', 'lastpushid', $count['max']);
				}
			}
		}
	}
}

/* Tile push notification change the number in the icon of the App in Start Screen of
 * a Windows Phone Device, Image could be changed, not used for App "Friendica Mobile"
 */
function send_tile_update($device_url, $image_url, $count, $title, $priority = 1)
{
	$msg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
		"<wp:Notification xmlns:wp=\"WPNotification\">" .
		"<wp:Tile>" .
		"<wp:BackgroundImage>" . $image_url . "</wp:BackgroundImage>" .
		"<wp:Count>" . $count . "</wp:Count>" .
		"<wp:Title>" . $title . "</wp:Title>" .
		"</wp:Tile> " .
		"</wp:Notification>";

	$result = send_push($device_url, [
		'X-WindowsPhone-Target: token',
		'X-NotificationClass: ' . $priority,
		], $msg);
	return $result;
}

/* Toast push notification send information to the top of the display
 * if the user is not currently using the Friendica Mobile App, however
 * there is only one line for displaying the information
 */
function send_toast($device_url, $title, $message, $priority = 2)
{
	$msg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
		"<wp:Notification xmlns:wp=\"WPNotification\">" .
		"<wp:Toast>" .
		"<wp:Text1>" . $title . "</wp:Text1>" .
		"<wp:Text2>" . $message . "</wp:Text2>" .
		"<wp:Param></wp:Param>" .
		"</wp:Toast>" .
		"</wp:Notification>";

	$result = send_push($device_url, [
		'X-WindowsPhone-Target: toast',
		'X-NotificationClass: ' . $priority,
		], $msg);
	return $result;
}

// General function to send the push notification via cURL
function send_push($device_url, $headers, $msg)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $device_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers + [
		'Content-Type: text/xml',
		'charset=utf-8',
		'Accept: application/*',
		]
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);

	$output = curl_exec($ch);
	curl_close($ch);

	// if we received "Expired" from Microsoft server we will delete the obsolete device-URL
	// and log this fact
	$subscriptionStatus = get_header_value($output, 'X-SubscriptionStatus');
	if ($subscriptionStatus == "Expired") {
		DI::pConfig()->set(local_user(), 'windowsphonepush', 'device_url', "");
		Logger::notice("ERROR: the stored Device-URL " . $device_url . "returned an 'Expired' error, it has been deleted now.");
	}

	// the notification status shall be returned to windowsphonepush_cron (will
	// update settings if 'Received' otherwise keep old value in settings (on QueuedFull. Suppressed, N/A, Dropped)
	$notificationStatus = get_header_value($output, 'X-NotificationStatus');
	return $notificationStatus;
}

// helper function to receive statuses from webresponse of Microsoft server
function get_header_value($content, $header)
{
	return preg_match_all("/$header: (.*)/i", $content, $match) ? $match[1][0] : "";
}

/* reading information from url and deciding which function to start
 * show_settings = delivering settings to check
 * update_settings = set the device_url
 * update_counterunseen = set counter for unseen elements to zero
 */
function windowsphonepush_content(App $a)
{
	// Login with the specified Network credentials (like in api.php)
	windowsphonepush_login($a);

	$path = DI::args()->getArgv()[0];
	$path2 = DI::args()->getArgv()[1];
	if ($path == "windowsphonepush") {
		switch ($path2) {
			case "show_settings":
				windowsphonepush_showsettings($a);
				exit();
				break;
			case "update_settings":
				$ret = windowsphonepush_updatesettings($a);
				header("Content-Type: application/json; charset=utf-8");
				echo json_encode(['status' => $ret]);
				exit();
				break;
			case "update_counterunseen":
				$ret = windowsphonepush_updatecounterunseen();
				header("Content-Type: application/json; charset=utf-8");
				echo json_encode(['status' => $ret]);
				exit();
				break;
			default:
				echo "Fehler";
		}
	}
}

// return settings for windowsphonepush addon to be able to check them in WP app
function windowsphonepush_showsettings()
{
	if (!local_user()) {
		return;
	}

	$enable = DI::pConfig()->get(local_user(), 'windowsphonepush', 'enable');
	$device_url = DI::pConfig()->get(local_user(), 'windowsphonepush', 'device_url');
	$senditemtext = DI::pConfig()->get(local_user(), 'windowsphonepush', 'senditemtext');
	$lastpushid = DI::pConfig()->get(local_user(), 'windowsphonepush', 'lastpushid');
	$counterunseen = DI::pConfig()->get(local_user(), 'windowsphonepush', 'counterunseen');
	$addonversion = "2.0";

	if (!$device_url) {
		$device_url = "";
	}

	if (!$lastpushid) {
		$lastpushid = 0;
	}

	header("Content-Type: application/json");
	echo json_encode(['uid' => local_user(),
		'enable' => $enable,
		'device_url' => $device_url,
		'senditemtext' => $senditemtext,
		'lastpushid' => $lastpushid,
		'counterunseen' => $counterunseen,
		'addonversion' => $addonversion]);
}

/* update_settings is used to transfer the device_url from WP device to the Friendica server
 * return the status of the operation to the server
 */
function windowsphonepush_updatesettings()
{
	if (!local_user()) {
		return "Not Authenticated";
	}

	// no updating if user hasn't enabled the addon
	$enable = DI::pConfig()->get(local_user(), 'windowsphonepush', 'enable');
	if (!$enable) {
		return "Plug-in not enabled";
	}

	// check if sent url is empty - don't save and send return code to app
	$device_url = $_POST['deviceurl'];
	if ($device_url == "") {
		Logger::notice("ERROR: no valid Device-URL specified - client transferred '" . $device_url . "'");
		return "No valid Device-URL specified";
	}

	// check if sent url is already stored in database for another user, we assume that there was a change of
	// the user on the Windows Phone device and that device url is no longer true for the other user, so we
	// et the device_url for the OTHER user blank (should normally not occur as App should include User/server
	// in url request to Microsoft Push Notification server)
	$pconfigs = DBA::selectToArray('pconfig', ['uid'], ["`uid` != ? AND `cat` = ? AND `k` = ? AND `v` = ?", local_user(), 'windowsphonepush', 'device_url', $device_url]);
	foreach ($pconfigs as $rr) {
		DI::pConfig()->set($rr['uid'], 'windowsphonepush', 'device_url', '');
		Logger::notice("WARN: the sent URL was already registered with user '" . $rr['uid'] . "'. Deleted for this user as we expect to be correct now for user '" . local_user() . "'.");
	}

	DI::pConfig()->set(local_user(), 'windowsphonepush', 'device_url', $device_url);
	// output the successfull update of the device URL to the logger for error analysis if necessary
	Logger::notice("INFO: Device-URL for user '" . local_user() . "' has been updated with '" . $device_url . "'");
	return "Device-URL updated successfully!";
}

// update_counterunseen is used to reset the counter to zero from Windows Phone app
function windowsphonepush_updatecounterunseen()
{
	if (!local_user()) {
		return "Not Authenticated";
	}

	// no updating if user hasn't enabled the addon
	$enable = DI::pConfig()->get(local_user(), 'windowsphonepush', 'enable');
	if (!$enable) {
		return "Plug-in not enabled";
	}

	DI::pConfig()->set(local_user(), 'windowsphonepush', 'counterunseen', 0);
	return "Counter set to zero";
}

/* helper function to login to the server with the specified Network credentials
 * (mainly copied from api.php)
 */
function windowsphonepush_login(App $a)
{
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		Logger::info('API_login: ' . print_r($_SERVER, true));
		header('WWW-Authenticate: Basic realm="Friendica"');
		throw new UnauthorizedException('This api requires login');
	}

	try {
		$user_id = User::getIdFromPasswordAuthentication($_SERVER['PHP_AUTH_USER'], trim($_SERVER['PHP_AUTH_PW']));
		$record = DBA::selectFirst('user', [], ['uid' => $user_id]);
		DI::auth()->setForUser($a, $record);
		DI::session()->set('allow_api', true);
		Hook::callAll('logged_in', $record);
	} catch (Exception $ex) {
		Logger::info('API_login failure: ' . print_r($_SERVER, true));
		header('WWW-Authenticate: Basic realm="Friendica"');
		throw new UnauthorizedException('This api requires login');
	}
}
