<?php
  /**
   * Name: Retriever
   * Description: Follow the permalink of RSS/Atom feed items and replace the summary with the full content.
   * Version: 1.0
   * Author: Matthew Exon <http://mat.exon.name>
   */

use Friendica\Core\Addon;
use Friendica\Core\Config;
use Friendica\Core\PConfig;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;
use Friendica\Content\Text\HTML;
use Friendica\Content\Text\BBCode;
use Friendica\Model\Photo;
use Friendica\Object\Image;
use Friendica\Util\Network;
use Friendica\Core\L10n;
use Friendica\Database\DBA;
use Friendica\Model\ItemURI;
use Friendica\Model\Item;
use Friendica\Util\DateTimeFormat;

/**
 * @brief Installation hook for retriever plugin
 */
function retriever_install() {
	Addon::registerHook('addon_settings', 'addon/retriever/retriever.php', 'retriever_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/retriever/retriever.php', 'retriever_addon_settings_post');
	Addon::registerHook('post_remote', 'addon/retriever/retriever.php', 'retriever_post_remote_hook');
	Addon::registerHook('contact_photo_menu', 'addon/retriever/retriever.php', 'retriever_contact_photo_menu');
	Addon::registerHook('cron', 'addon/retriever/retriever.php', 'retriever_cron');

	if (Config::get('retriever', 'dbversion') != '0.14') {
		$schema = file_get_contents(dirname(__file__).'/database.sql');
		$tables = explode(';', $schema);
		foreach ($tables as $table) {
			if (!DBA::e($table)) {
				Logger::warning('Unable to create database table: ' . DBA::errorMessage());
				return;
			}
		}
		Config::set('retriever', 'downloads_per_cron', '100');
		Config::set('retriever', 'dbversion', '0.14');
	}
}

/**
 * @brief Uninstallation hook for retriever plugin
 */
function retriever_uninstall() {
	Addon::unregisterHook('addon_settings', 'addon/retriever/retriever.php', 'retriever_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/retriever/retriever.php', 'retriever_addon_settings_post');
	Addon::unregisterHook('post_remote', 'addon/retriever/retriever.php', 'retriever_post_remote_hook');
	Addon::unregisterHook('addon_settings', 'addon/retriever/retriever.php', 'retriever_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/retriever/retriever.php', 'retriever_addon_settings_post');
	Addon::unregisterHook('contact_photo_menu', 'addon/retriever/retriever.php', 'retriever_contact_photo_menu');
	Addon::unregisterHook('cron', 'addon/retriever/retriever.php', 'retriever_cron');
}

/**
 * @brief Module hook for retriever plugin
 *
 * TODO: figure out what this should be used for
 */
function retriever_module() {}

/**
 * @brief Admin page hook for retriever plugin
 *
 * @param App $a App object (by ref)
 * @param string $o HTML to append content to (by ref)
 */
function retriever_addon_admin(&$a, &$o) {
	$template = Renderer::getMarkupTemplate('admin.tpl', 'addon/retriever/');

	$downloads_per_cron = Config::get('retriever', 'downloads_per_cron');
	$downloads_per_cron_config = ['downloads_per_cron',
				      L10n::t('Downloads per Cron'),
				      $downloads_per_cron,
				      L10n::t('Maximum number of downloads to attempt during each run of the cron job.')];

	$allow_images = Config::get('retriever', 'allow_images');
	$allow_images_config = ['allow_images',
				      L10n::t('Allow Retrieving Images'),
				      $allow_images,
				      L10n::t('Allow users to request images be downloaded as well as text.<br><b>Warning: the images are not automatically deleted and may fill up your database.</b>')];

	$o .= Renderer::replaceMacros($template, [
					      '$downloads_per_cron' => $downloads_per_cron_config,
					      '$allow_images' => $allow_images_config,
					      '$submit' => L10n::t('Save Settings')]);
}

/**
 * @brief Admin page post hook for retriever plugin
 */
function retriever_addon_admin_post () {
	if (!empty($_POST['downloads_per_cron'])) {
		Config::set('retriever', 'downloads_per_cron', $_POST['downloads_per_cron']);
	}
	Config::set('retriever', 'allow_images', $_POST['allow_images']);
}

/**
 * @brief Cron jobs for retriever plugin
 */
function retriever_cron() {
	$downloads_per_cron = Config::get('retriever', 'downloads_per_cron');

	// Do this first, otherwise it can interfere with retriever_retrieve_items
	retriever_clean_up_completed_resources($downloads_per_cron);

	retriever_retrieve_items($downloads_per_cron);
	retriever_tidy();
}

// This global variable is used to track the number of items that have been retrieved during the course of this process
$retriever_item_count = 0;

/**
 * @brief Searches for items in the retriever_items table that should be retrieved and attempts to retrieve them
 *
 * @param int $max_items Maximum number of items to retrieve in this call
 */
function retriever_retrieve_items($max_items) {
	global $retriever_item_count;

	$retriever_schedule = array(array(1,'minute'),
				    array(10,'minute'),
				    array(1,'hour'),
				    array(1,'day'),
				    array(2,'day'),
				    array(1,'week'),
				    array(1,'month'));

	$schedule_clauses = array();
	for ($i = 0; $i < count($retriever_schedule); $i++) {
		$num = $retriever_schedule[$i][0];
		$unit = $retriever_schedule[$i][1];
		array_push($schedule_clauses,
			   '(`num-tries` = ' . $i . ' AND TIMESTAMPADD(' . DBA::escape($unit) .
			   ', ' . intval($num) . ', `last-try`) < now())');
	}

	$retrieve_items = $max_items - $retriever_item_count;
	do {
		Logger::debug('retriever_retrieve_items: asked for maximum ' . $max_items . ', already retrieved ' . intval($retriever_item_count) . ', retrieve ' . $retrieve_items);
		$retriever_resources = DBA::selectToArray('retriever_resource', [], ['`completed` IS NULL AND (`last-try` IS NULL OR ' . implode($schedule_clauses, ' OR ') . ')'], ['order' => ['last-try' => 0], 'limit' => $retrieve_items]);
		if (!is_array($retriever_resources)) {
			break;
		}
		if (count($retriever_resources) == 0) {
			break;
		}
		Logger::debug('retriever_retrieve_items: found ' . count($retriever_resources) . ' waiting resources in database');
		foreach ($retriever_resources as $retriever_resource) {
			retrieve_resource($retriever_resource);
			$retriever_item_count++;
		}
		$retrieve_items = $max_items - $retriever_item_count;
	}
	while ($retrieve_items > 0);
	Logger::debug('retriever_retrieve_items: finished retrieving items');
}

/**
 * @brief Looks for items that are waiting even though the resource has completed.  This shouldn't happen, but is worth cleaning up if it does.
 *
 * @param int $max_items Maximum number of items to retrieve in this call
 */
function retriever_clean_up_completed_resources($max_items) {
	// TODO: figure out how to do this with DBA module
	$r = q('SELECT retriever_resource.`id` as resource, retriever_item.`id` as item FROM retriever_resource, retriever_item, retriever_rule WHERE retriever_item.`finished` = 0 AND retriever_item.`resource` = retriever_resource.`id` AND retriever_resource.`completed` IS NOT NULL AND retriever_item.`contact-id` = retriever_rule.`contact-id` AND retriever_item.`item-uid` = retriever_rule.`uid` LIMIT %d',
	       intval($max_items));
	if (!$r) {
		$r = array();
	}
	Logger::debug('retriever_clean_up_completed_resources: items waiting even though resource has completed: ' . count($r));
	foreach ($r as $rr) {
		$retriever_item = DBA::selectFirst('retriever_item', [], ['id' => intval($rr['item'])]);
		if (!DBA::isResult($retriever_item)) {
			Logger::warning('retriever_clean_up_completed_resources: no retriever item with id ' . $rr['item']);
			continue;
		}
		$item = retriever_get_item($retriever_item);
		if (!$item) {
			Logger::warning('retriever_clean_up_completed_resources: no item ' . $retriever_item['item-uri']);
			continue;
		}
		$retriever_rule = get_retriever_rule($retriever_item['contact-id'], $item['uid'], false);
		if (!$retriever_rule) {
			Logger::warning('retriever_clean_up_completed_resources: no retriever for uri ' . $retriever_item['item-uri'] . ' uid ' . $retriever_item['uid'] . ' ' . $retriever_item['contact-id']);
			continue;
		}
		$resource = DBA::selectFirst('retriever_resource', [], ['id' => intval($rr['resource'])]);
		retriever_apply_completed_resource_to_item($retriever_rule, $item, $resource);
		// TODO: I don't really get how the $old_fields argument to DBA::update works
		DBA::update('retriever_item', ['finished' => 1], ['id' => intval($retriever_item['id'])], ['finished' => 0]);
		retriever_check_item_completed($item);
	}
}

/**
 * @brief Deletes old rows from the retriever_item and retriever_resource table that are unlikely to be needed
 */
function retriever_tidy() {
	DBA::delete('retriever_resource', ['completed IS NOT NULL AND completed < DATE_SUB(now(), INTERVAL 1 WEEK)']);
	DBA::delete('retriever_resource', ['completed IS NULL AND created < DATE_SUB(now(), INTERVAL 3 MONTH)']);

	$r = q("SELECT retriever_item.id FROM retriever_item LEFT OUTER JOIN retriever_resource ON (retriever_item.resource = retriever_resource.id) WHERE retriever_resource.id is null");
	Logger::info('retriever_tidy: found ' . count($r) . ' retriever_items with no retriever_resource');
	foreach ($r as $rr) {
		q('DELETE FROM retriever_item WHERE id = %d', intval($rr['id']));
	}
}

/**
 * @brief Special case of retrieving a resource: if the URL is a data URL, do not use cURL, decode the URL directly
 *
 * @param array $resource The row from the retriever_resource table
 */
function retrieve_dataurl_resource($resource) {
	if (!preg_match("/date:(.*);base64,(.*)/", $resource['url'], $matches)) {
		Logger::warning('retrieve_dataurl_resource: resource ' . $resource['id'] . ' does not match pattern');
	} else {
		$resource['type'] = $matches[1];
		$resource['data'] = base64url_decode($matches[2]);
	}

	// Succeed or fail, there's no point retrying
	DBA::update('retriever_resource', ['id' => intval($resource['id'])], ['last-try' => DateTimeFormat::utcNow(), 'num-tries' => intval($resource['num-tries']) + 1, 'completed' => DateTimeFormat::utcNow(), 'data' => $resource['data'], 'type' => $resource['type']], ['last-try' => false]);
	retriever_resource_completed($resource);
}

/**
 * @brief Makes an attempt to retrieve the supplied resource, and updates the row in the table with the results
 *
 * @param array $resource The row from the retriever_resource table
 */
function retrieve_resource($resource) {
	$components = parse_url($resource['url']);
	if ($components['scheme'] == "data") {
		return retrieve_dataurl_resource($resource);
	}
	if (($components['scheme'] != "http") && ($components['scheme'] != "https")) {
		Logger::warning('retrieve_resource: URL scheme not supported for ' . $resource['url']);
		DBA::update('retriever_resource', ['completed' => DateTimeFormat::utcNow()], ['id' => intval($resource['id'])], ['completed' => false]);
		retriever_resource_completed($resource);
		return;
	}

	$retriever_rule = get_retriever_rule($resource['contact-id'], $resource['item-uid'], false);
	if (!$retriever_rule) {
		Logger::warning('retrieve_resource: no rule found for resource id ' . $resource['id'] . ' contact ' . $resource['contact-id'] . ' item ' . $resource['item-uid']);
		DBA::update('retriever_resource', ['completed' => DateTimeFormat::utcNow()], ['id' => intval($resource['id'])], ['completed' => false]);
		retriever_resource_completed($resource);
		return;
	}
	$rule_data = $retriever_rule['data'];
	if (!$rule_data) {
		Logger::warning('retrieve_resource: no rule data found for resource id ' . $resource['id'] . ' contact ' . $resource['contact-id'] . ' item ' . $resource['item-uid']);
		DBA::update('retriever_resource', ['completed' => DateTimeFormat::utcNow()], ['id' => intval($resource['id'])], ['completed' => false]);
		retriever_resource_completed($resource);
		return;
	}

	try {
		Logger::debug('retrieve_resource: ' . ($resource['num-tries'] + 1) . ' attempt at resource ' . $resource['id'] . ' ' . $resource['url']);
		$redirects = 0;
		$cookiejar = '';
		if (array_key_exists('storecookies', $rule_data) && $rule_data['storecookies']) {
			$cookiejar = tempnam(get_temppath(), 'cookiejar-retriever-');
			file_put_contents($cookiejar, $rule_data['cookiedata']);
		}
		$fetch_result = Network::fetchUrlFull($resource['url'], $resource['binary'], $redirects, '', $cookiejar);
		if (array_key_exists('storecookies', $rule_data) && $rule_data['storecookies']) {
			$retriever_rule['data']['cookiedata'] = file_get_contents($cookiejar);
			DBA::update('retriever_rule', ['data' => json_encode($retriever_rule['data'])], ['id' => intval($retriever_rule["id"])], $retriever_rule);
			unlink($cookiejar);
		}
		$resource['data'] = $fetch_result->getBody();
		$resource['http-code'] = $fetch_result->getReturnCode();
		$resource['type'] = $fetch_result->getContentType();
		$resource['redirect-url'] = $fetch_result->getRedirectUrl();
		Logger::debug('retrieve_resource: got code ' . $resource['http-code'] . ' retrieving resource ' . $resource['id'] . ' final url ' . $resource['redirect-url']);
	} catch (Exception $e) {
		Logger::info('retrieve_resource: unable to retrieve ' . $resource['url'] . ' - ' . $e->getMessage());
	}
	DBA::update('retriever_resource', ['last-try' => DateTimeFormat::utcNow(), 'num-tries' => intval($resource['num-tries']) + 1, 'http-code' => intval($resource['http-code']), 'redirect-url' => $resource['redirect-url']], ['id' => intval($resource['id'])], ['last-try' => false]);
	if ($resource['data']) {
		DBA::update('retriever_resource', ['completed' => DateTimeFormat::utcNow(), 'data' => $resource['data'], 'type' => $resource['type']], ['id' => intval($resource['id'])], ['completed' => false]);
		retriever_resource_completed($resource);
	}
}

/**
 * @brief Gets the retriever configuration for a particular contact.  Optionally, will create a blank configuration.
 *
 * @param int $contact_id The Contact ID of the retriever configuration
 * @param int $uid The User ID of the retriever configuration
 * @param boolean $create Whether to create a new configuration if none exists already
 * @return array The row from the retriever_rule database for this configuration
 */
function get_retriever_rule($contact_id, $uid, $create) {
	$retriever_rule = DBA::selectFirst('retriever_rule', [], ['contact-id' => intval($contact_id), 'uid' => intval($uid)]);
	if ($retriever_rule) {
		$retriever_rule['data'] = json_decode($retriever_rule['data'], true);
		return $retriever_rule;
	}
	if ($create) {
		DBA::insert('retriever_rule', ['uid' => intval($uid), 'contact-id' => intval($contact_id)]);
		$retriever_rule = DBA::selectFirst('retriever_rule', [], ['contact-id' => intval($contact_id), 'uid' => intval($uid)]);
		return $retriever_rule;
	}
}

/**
 * @brief Looks up the item from the database that corresponds to the retriever_item
 *
 * @param array $retriever_item Row from the retriever_item table
 * @return array Item that was found, or undef if no item could be found
 */
function retriever_get_item($retriever_item) {
	$item = Item::selectFirst([], ['uri' => $retriever_item['item-uri'], 'uid' => intval($retriever_item['item-uid']), 'contact-id' => intval($retriever_item['contact-id'])]);
	if (!DBA::isResult($item)) {
		Logger::warning('retriever_get_item: no item found for uri ' . $retriever_item['item-uri']);
		return;
	}
	return $item;
}

/**
 * @brief This function should be called when a resource is completed to trigger all next steps, based on the corresponding retriever item
 *
 * @param int $retriever_item_id ID of the retriever item corresponding to this resource
 * @param array $resource The full details of the completed resource
 */
function retriever_item_completed($retriever_item_id, $resource) {
	Logger::debug('retriever_item_completed: id ' . $retriever_item_id . ' url ' . $resource['url']);

	$retriever_item = DBA::selectFirst('retriever_item', [], ['id' => intval($retriever_item_id)]);
	if (!DBA::isResult($retriever_item)) {
		Logger::info('retriever_item_completed: no retriever item with id ' . $retriever_item_id);
		return;
	}
	$item = retriever_get_item($retriever_item);
	if (!$item) {
		Logger::warning('retriever_item_completed: no item ' . $retriever_item['item-uri']);
		return;
	}
	// Note: the retriever might be null.  Doesn't matter.
	$retriever_rule = get_retriever_rule($retriever_item['contact-id'], $retriever_item['item-uid'], false);

	retriever_apply_completed_resource_to_item($retriever_rule, $item, $resource);

	DBA::update('retriever_item', ['finished' => 1], ['id' => intval($retriever_item['id'])], ['finished' => 0]);
	retriever_check_item_completed($item);
}

/**
 * @brief This function should be called when a resource is completed to trigger all next steps
 *
 * @param array $resource The full details of the completed resource
 */
function retriever_resource_completed($resource) {
	Logger::debug('retriever_resource_completed: id ' . $resource['id'] . ' url ' . $resource['url']);
	foreach (DBA::selectToArray('retriever_item', ['id'], ['resource' => intval($resource['id'])]) as $retriever_item) {
		retriever_item_completed($retriever_item['id'], $resource);
	}
}

/**
 * @brief For a retriever config for a particular contact, remove existing artifacts for a number of completed items and queue them to be tried again.  Will make the items invisible until they are again completed.  The items chosen will be the most recently received.
 *
 * @param array $retriever The row from the retriever_rule table for the contact
 * @param int $num The number of existing items to queue for retrieval
 */
function apply_retrospective($retriever, $num) {
	foreach (Item::selectToArray([], ['contact-id' => intval($retriever['contact-id'])], ['order' => ['received' => true], 'limit' => $num]) as $item) {
		Item::update(['visible' => 0], ['id' => intval($item['id'])]);
		foreach (DBA::selectToArray('retriever_item', [], ['item-uri' => $item['uri'], 'item-uid' => $item['uid'], 'contact-id' => $item['contact-id']]) as $retriever_item) {
			DBA::delete('retriever_resource', ['id' => $retriever_item['resource']]);
			DBA::delete('retriever_item', ['id' => $retriever_item['id']]);
		}
		retriever_on_item_insert($retriever, $item);
	}
}

/**
 * @brief Queues an item for retrieval.  It does not actually perform the retrieval.
 *
 * @param array $retriever Retriever rule configuration for this contact
 * @param array $item Item that should be retrieved.  This may or may not have been already stored in the database.
 *
 * TODO: This queries then inserts.  It should use some kind of lock to avoid requesting the same resource twice.
 */
function retriever_on_item_insert($retriever, &$item) {
	if (!$retriever || !$retriever['id']) {
		Logger::info('retriever_on_item_insert: No retriever supplied');
		return;
	}
	if (!array_key_exists('enable', $retriever['data']) || !$retriever['data']['enable'] == "on") {
		return;
	}
	if (array_key_exists('plink', $item) && strlen($item['plink'])) {
		$url = $item['plink'];
	}
	else {
		if (!array_key_exists('uri-id', $item)) {
			Logger::warning('retriever_on_item_insert: item ' . $item['id'] . ' has no plink and no uri-id');
			return;
		}
		$content = DBA::selectFirst('item-content', [], ['uri-id' => $item['uri-id']]);
		$url = $content['plink'];
	}

	if (array_key_exists('modurl', $retriever['data']) && $retriever['data']['modurl']) {
		$orig_url = $url;
		$url = preg_replace('/' . $retriever['data']['pattern'] . '/', $retriever['data']['replace'], $orig_url);
		Logger::debug('retriever_on_item_insert: Changed ' . $orig_url . ' to ' . $url);
	}

	$resource = add_retriever_resource($url, $item['uid'], $item['contact-id']);
	$retriever_item_id = add_retriever_item($item, $resource);
}

/**
 * @brief Creates a new resource to be downloaded from the supplied URL.  Unique resources are created for each URL, UID and contact ID, because different contact IDs may have different rules for how to retrieve them.  If the URL is actually a data URL, the resource is completed immediately.
 *
 * @param string $url URL of the resource to be downloaded
 * @param int $uid User ID that this resource is being downloaded fore
 * @param int $cid Contact ID of the item that triggered the downloading of this resource
 * @param boolean $binary Specifies if this download should be done in binary mode
 * @return array The created resource
 */
function add_retriever_resource($url, $uid, $cid, $binary = false) {
	Logger::debug('add_retriever_resource: url ' . $url . ' uid ' . $uid . ' contact-id ' . $cid);

	$scheme = parse_url($url, PHP_URL_SCHEME);
	if ($scheme == 'data') {
		$fp = fopen($url, 'r');
		$meta = stream_get_meta_data($fp);
		$type = $meta['mediatype'];
		$data = stream_get_contents($fp);
		fclose($fp);

		$url = 'md5://' . hash('md5', $url);
		$resource = DBA::selectFirst('retriever_resource', [], ['url' => $url, 'item-uid' => intval($uid), 'contact-id' => intval($cid)]);
		if ($resource) {
			Logger::debug('add_retriever_resource: Resource ' . $url . ' already requested');
			return $resource;
		}

		DBA::insert('retriever_resource', ['item-uid' => intval($uid), 'contact-id' => intval($cid), 'type' => $type, 'binary' => ($binary ? 1 : 0), 'url' => $url, 'completed' => DateTimeFormat::utcNow(), 'data' => $data]);
		$resource = DBA::selectFirst('retriever_resource', [], ['url' => $url, 'item-uid' => intval($uid), 'contact-id' => intval($cid)]);
		if ($resource) {
			retriever_resource_completed($resource);
		}
		return $resource;
	}

	// 800 characters is the size of this field in the database
	if (strlen($url) > 800) {
		Logger::warning('add_retriever_resource: URL is longer than 800 characters');
	}

	$resource = DBA::selectFirst('retriever_resource', [], ['url' => $url, 'item-uid' => intval($uid), 'contact-id' => intval($cid)]);
	if ($resource) {
		Logger::debug('add_retriever_resource: Resource ' . $url . ' uid ' . $uid . ' cid ' . $cid . ' already requested');
		return $resource;
	}

	DBA::insert('retriever_resource', ['item-uid' => intval($uid), 'contact-id' => intval($cid), 'binary' => ($binary ? 1 : 0), 'url' => $url]);
	return DBA::selectFirst('retriever_resource', [], ['url' => $url, 'item-uid' => intval($uid), 'contact-id' => intval($cid)]);
}

/**
 * @brief Adds a retriever item for the supplied resource and item, to mark that this item should wait for the resource to be completed.  Does not create a retriever item if a matching one already exists.
 *
 * @param array $item Item that is waiting for the resource.  This may or may not have been already stored in the database.
 * @param array $resource Resource that the item needs to wait for.  This must have already been stored in the database.
 * @return int ID of the retriever item that was created, or the existing one if present
 */
function add_retriever_item($item, $resource) {
	Logger::debug('add_retriever_item: ' . $resource['url'] . ' for ' . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);

	if (!array_key_exists('id', $resource) || !$resource['id']) {
		Logger::warning('add_retriever_item: resource is empty');
		return;
	}
	if (DBA::selectFirst('retriever_item', [], ['item-uri' => $item['uri'], 'item-uid' => intval($item['uid']), 'resource' => intval($resource['id'])])) {
		Logger::info("add_retriever_item: retriever item already present for " . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);
		return;
	}
	DBA::insert('retriever_item', ['item-uri' => $item['uri'], 'item-uid' => intval($item['uid']), 'contact-id' => intval($item['contact-id']), 'resource' => intval($resource['id'])]);
	$retriever_item = DBA::selectFirst('retriever_item', ['id'], ['item-uri' => $item['uri'], 'item-uid' => intval($item['uid']), 'resource' => intval($resource['id'])]);
	if (!$retriever_item) {
		Logger::info("add_retriever_item: couldn't create retriever item for " . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);
		return;
	}
	Logger::debug('add_retriever_item: created retriever_item ' . $retriever_item['id'] . ' for item ' . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);
	return $retriever_item['id'];
}

/**
 * @brief Analyse a completed text resource (such as HTML) for the character encoding used
 *
 * @param array $resource The completed resource
 * @return string Character encoding, e.g. "utf-8" or "iso-8859-1"
 */
function retriever_get_encoding($resource) {
	$matches = array();
	if (preg_match('/charset=(.*)/', $resource['type'], $matches)) {
		return trim(array_pop($matches));
	}
	return 'utf-8';
}

/**
 * @brief Apply the XSLT template to the DOM document
 *
 * @param string $xslt_text Text of the XSLT template
 * @param DOMDocument $doc Input to the XSLT template
 * @return DOMDocument Result of applying the template
 */
function retriever_apply_xslt_text($xslt_text, $doc) {
	if (!$xslt_text) {
		Logger::info('retriever_apply_xslt_text: empty XSLT text');
		return $doc;
	}
	$xslt_doc = new DOMDocument();
	if (!$xslt_doc->loadXML($xslt_text)) {
		Logger::info('retriever_apply_xslt_text: could not load XML');
		return $doc;
	}
	$xp = new XsltProcessor();
	$xp->importStylesheet($xslt_doc);
	$result = $xp->transformToDoc($doc);
	return $result;
}

/**
 * @brief Applies the retriever rules to the downloaded resource, and stores the results as the new body text of the item
 *
 * @param array $retriever Retriever rules as stored in the database, with the "data" element already decoded from JSON
 * @param array &$item Item to be in which to store the new body (by ref).  This may or may not be already stored in the database.
 * @param array $resource Newly completed resource, which should be text (HTML or XML)
 */
function retriever_apply_dom_filter($retriever, &$item, $resource) {
	Logger::debug('retriever_apply_dom_filter: applying XSLT to uri ' . $item['uri'] . ' uid ' . $item['uid'] . ' contact ' . $item['contact-id']);

	if (!array_key_exists('include', $retriever['data']) && !array_key_exists('customxslt', $retriever['data'])) {
		Logger::info('retriever_apply_dom_filter: no include and no customxslt');
		return;
	}
	if (!$resource['data']) {
		Logger::info('retriever_apply_dom_filter: no text to work with');
		return;
	}

	$doc = retriever_load_into_dom($resource);

	$doc = retriever_extract($doc, $retriever);
	if (!$doc) {
		Logger::info('retriever_apply_dom_filter: failed to apply extract XSLT template');
		return;
	}

	$doc = retriever_globalise_urls($doc, $resource);
	if (!$doc) {
		Logger::info('retriever_apply_dom_filter: failed to apply fix urls XSLT template');
		return;
	}

	$body = HTML::toBBCode($doc->saveHTML());
	if (!strlen($body)) {
		Logger::info('retriever_apply_dom_filter retriever ' . $retriever['id'] . ' item ' . $item['id'] . ': output was empty');
		return;
	}
	$body .= "\n\n" . L10n::t('Retrieved') . ' ' . date("Y-m-d") . ': [url=';
	$body .=  $item['plink'];
	$body .= ']' . $item['plink'] . '[/url]';

	Logger::debug('retriever_apply_dom_filter: XSLT result \"' . $body . '\"');
	retriever_set_body($item, $body);
}

/**
 * @brief Converts the completed resource, which must be HTML or XML, into a DOM document
 *
 * @param array $resource The resource containing the text content
 */
function retriever_load_into_dom($resource) {
	$encoding = retriever_get_encoding($resource);
	$content = mb_convert_encoding($resource['data'], 'HTML-ENTITIES', $encoding);
	$doc = new DOMDocument('1.0', 'UTF-8');
	if (strpos($resource['type'], 'html') !== false) {
		@$doc->loadHTML($content);
	}
	else {
		$doc->loadXML($content);
	}
	return $doc;
}

/**
 * @brief Applies the retriever rules, including configuration for included and excluded portions, to the DOM document
 *
 * @param DOMDocument $doc The original DOM document downloaded from the link
 * @param array $retriever The retriever configuration for this contact
 * @return DOMDocument New DOM document containing only the desired content
 */
function retriever_extract($doc, $retriever) {
	$params = array('$spec' => $retriever['data']);
	$extract_template = Renderer::getMarkupTemplate('extract.tpl', 'addon/retriever/');
	$extract_xslt = Renderer::replaceMacros($extract_template, $params);
	if ($retriever['data']['include']) {
		Logger::debug('retriever_apply_dom_filter: applying include/exclude template \"' . $extract_xslt . '\"');
		$doc = retriever_apply_xslt_text($extract_xslt, $doc);
	}
	if (array_key_exists('customxslt', $retriever['data']) && $retriever['data']['customxslt']) {
		Logger::debug('retriever_extract: applying custom XSLT \"' . $retriever['data']['customxslt'] . '\"');
		$doc = retriever_apply_xslt_text($retriever['data']['customxslt'], $doc);
	}
	return $doc;
}

/**
 * @brief Converts local URLs in the DOM document to global URLs
 *
 * @param DOMDocument $doc DOM document potentially containing links
 * @param array $resource Completed resource which contains the text in the DOM document
 * @return DOMDocument New DOM document with global URLs
 */
function retriever_globalise_urls($doc, $resource) {
	$components = parse_url($resource['redirect-url']);
	$rooturl = $components['scheme'] . "://" . $components['host'];
	$dirurl = $rooturl . dirname($components['path']) . "/";
	$params = array('$dirurl' => $dirurl, '$rooturl' => $rooturl);
	$fix_urls_template = Renderer::getMarkupTemplate('fix-urls.tpl', 'addon/retriever/');
	$fix_urls_xslt = Renderer::replaceMacros($fix_urls_template, $params);
	$doc = retriever_apply_xslt_text($fix_urls_xslt, $doc);
	return $doc;
}

/**
 * @brief Returns the body text for the supplied item.  If the item has already been stored in the database, this will fetch the content from the database rather than from the supplied array.
 *
 * @param array $item Row from the item table
 */
function retriever_get_body($item) {
	if (!array_key_exists('uri-id', $item) || !$item['uri-id']) {
		// item has not yet been stored in database
		return $item['body'];
	}

	// item has been stored in database, body is stored in the item-content table
	$content = DBA::selectFirst('item-content', ['body'], ['uri-id' => $item['uri-id']]);
	if (!$content) {
		Logger::warning('retriever_get_body: item-content uri-id ' . $item['uri-id'] . ' has no content');
		return $item['body'];
	}
	if (!$content['body']) {
		Logger::warning('retriever_get_body: item-content uri-id ' . $item['uri-id'] . ' has no body');
		return $item['body'];
	}
	if ($content['body'] != $item['body']) {
		Logger::warning('@@@ this is probably bad @@@ content: ' . $content['body'] . ' @@@ item: ' . $item['body']);
	}
	return $content['body'];
}

/**
 * @brief Updates the item with the supplied body text.  If the item has already been stored in the database, this will update the database too.
 *
 * @param array &$item Item in which to set the body (by ref).  This may or may not be already stored in the database.
 * @param string $body New body content
 */
function retriever_set_body(&$item, $body) {
	$item['body'] = $body;
	if (!array_key_exists('id', $item) || !$item['id']) {
		// item has not yet been stored in database
		return;
	}
	Item::update(['body' => $body], ['id' => intval($item['id'])]);
}

/**
 * @brief Searches for images in the item and adds corresponding retriever_items.  If the images have already been downloaded, updates the body in the supplied item array.
 *
 * @param array &$item Item to be searched for images and updated (by ref).  This may or may not be already stored in the database.
 */
function retrieve_images(&$item) {
	if (!Config::get('retriever', 'allow_images')) {
		return;
	}

	$body = retriever_get_body($item);
	if (!strlen($body)) {
		Logger::warning('retrieve_images: no body for item ' . $item['uri']);
		return;
	}

	// I suspect that the first two are not used any more?
	preg_match_all("/\[img\=([0-9]*)x([0-9]*)\](.*?)\[\/img\]/ism", $body, $matches1);
	preg_match_all("/\[img\](.*?)\[\/img\]/ism", $body, $matches2);
	preg_match_all("/\[img\=([^\]]*)\]([^[]*)\[\/img\]/ism", $body, $matches3);
	$matches = array_merge($matches1[3], $matches2[1], $matches3[1]);
	Logger::debug('retrieve_images: found ' . count($matches) . ' images for item ' . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);
	foreach ($matches as $url) {
		if (!$url) {
			continue;
		}
		if (strpos($url, System::baseUrl()) === FALSE) {
			$resource = add_retriever_resource($url, $item['uid'], $item['contact-id'], true);
			if (!$resource['completed']) {
				add_retriever_item($item, $resource);
			}
			else {
				retriever_transform_images($item, $resource);
			}
		}
	}
}

/**
 * @brief Checks if an item has been completed, i.e. all its associated retriever_item rows have been retrieved.  If so, update the item to be visible again.
 *
 * @param array &$item Row from the item table (by ref)
 */
function retriever_check_item_completed(&$item)
{
	$waiting = DBA::selectFirst('retriever_item', [], ['item-uri' => $item['uri'], 'item-uid' => intval($item['uid']), 'contact-id' => intval($item['contact-id']), 'finished' => 0]);
	Logger::debug('retriever_check_item_completed: item ' . $item['uri'] . ' ' . $item['uid'] . ' '. $item['contact-id'] . ' waiting for resources');
	$old_visible = $item['visible'];
	$item['visible'] = $waiting ? 0 : 1;
	if (array_key_exists('id', $item) && ($item['id'] > 0) && ($old_visible != $item['visible'])) {
		Logger::debug('retriever_check_item_completed: changing visible flag to ' . $item['visible']);
		Item::update(['visible' => $item['visible']], ['id' => intval($item['id'])]);
	}
}

/**
 * @brief Updates an item with a completed resource.  If the resource was text, update the body with the new content.  If the resource was an image, replace remote images in the body with a local version.
 *
 * @param array $retriever Rule configuration for this contact
 * @param array &$item Row from the item table (by ref)
 * @param array $resource The resource that has just been completed
 */
function retriever_apply_completed_resource_to_item($retriever, &$item, $resource) {
	Logger::debug('retriever_apply_completed_resource_to_item: retriever ' . ($retriever ? $retriever['id'] : 'none') . ' resource ' . $resource['url'] . ' plink ' . $item['plink']);
	if (strpos($resource['type'], 'image') !== false) {
		retriever_transform_images($item, $resource);
	}
	if (!$retriever) {
		Logger::warning('retriever_apply_completed_resource_to_item: no retriever');
		return;
	}
	if ((strpos($resource['type'], 'html') !== false) ||
	    (strpos($resource['type'], 'xml') !== false)) {
		retriever_apply_dom_filter($retriever, $item, $resource);
		if ($retriever['data']['images'] ) {
			retrieve_images($item);
		}
	}
}

/**
 * @brief Stores the image downloaded in the supplied resource and updates the item body by replacing the remote URL with the local URL.  The body will be updated in the supplied item array.  If the item has already been stored, and therefore has an ID already, the row in the database will be updated too.
 *
 * @param array &$item Row from the item table (by ref)
 * @param array $resource Row from the resource table containing successfully downloaded image
 *
 * TODO: split this into two functions, one to store the image, the other to change the item body
 */
function retriever_transform_images(&$item, $resource) {
	if (!$resource['data']) {
		Logger::info('retriever_transform_images: no data available for ' . $resource['id'] . ' ' . $resource['url']);
		return;
	}

	$data = $resource['data'];
	$type = $resource['type'];
	$uid = $item['uid'];
	$cid = $item['contact-id'];
	$rid = Photo::newResource();
	$path = parse_url($resource['url'], PHP_URL_PATH);
	$parts = pathinfo($path);
	$filename = $parts['filename'] . (array_key_exists('extension', $parts) ? '.' . $parts['extension'] : '');
	$album = 'Wall Photos';
	$scale = 0;
	$desc = ''; // TODO: store alt text with resource when it's requested so we can fill this in
	Logger::debug('retriever_transform_images storing ' . strlen($data) . ' bytes type ' . $type . ': uid ' . $uid . ' cid ' . $cid . ' rid ' . $rid . ' filename ' . $filename . ' album ' . $album . ' scale ' . $scale . ' desc ' . $desc);
	$image = new Image($data, $type);
	if (!$image->isValid()) {
		Logger::warning('retriever_transform_images: invalid image found at URL ' . $resource['url'] . ' for item ' . $item['id']);
		return;
	}
	$photo = Photo::store($image, $uid, $cid, $rid, $filename, $album, 0, 0, "", "", "", "", $desc);
	$new_url = System::baseUrl() . '/photo/' . $rid . '-0.' . $image->getExt();
	if (!strlen($new_url)) {
		Logger::warning('retriever_transform_images: no replacement URL for image ' . $resource['url']);
		return;
	}

	$body = retriever_get_body($item);

	Logger::debug('retriever_transform_images: replacing ' . $resource['url'] . ' with ' . $new_url . ' in item ' . $item['uri']);
	$body = str_replace($resource["url"], $new_url, $body);
	retriever_set_body($item, $body);
}

/**
 * @brief Displays the retriever configuration page for a contact.  Alternatively, if the user clicked the "help" button, display the help content.
 *
 * @param App $a The App object
 */
function retriever_content($a) {
	if (!local_user()) {
		$a->page['content'] .= "<p>Please log in</p>";
		return;
	}
	if ($a->argv[1] === 'help') {
		$feeds = DBA::selectToArray('contact', ['id', 'name', 'thumb'], ['uid' => local_user(), 'network' => 'feed']);
		for ($i = 0; $i < count($feeds); ++$i) {
			$feeds[$i]['url'] = System::baseUrl() . '/retriever/' . $feeds[$i]['id'];
		}
		$template = Renderer::getMarkupTemplate('/help.tpl', 'addon/retriever/');
		$a->page['content'] .= Renderer::replaceMacros($template, array(
								       '$config' => $a->getBaseUrl . '/settings/addon',
								       '$allow_images' => Config::get('retriever', 'allow_images'),
								       '$feeds' => $feeds));
		return;
	}
	if ($a->argv[1]) {
		$retriever_rule = get_retriever_rule($a->argv[1], local_user(), false);

		if (!empty($_POST["id"])) {
			$retriever_rule = get_retriever_rule($a->argv[1], local_user(), true);
			$retriever_rule['data'] = array();
			foreach (array('modurl', 'pattern', 'replace', 'enable', 'images', 'customxslt', 'storecookies', 'cookiedata') as $setting) {
				if (empty($_POST['retriever_' . $setting])) {
					$retriever_rule['data'][$setting] = NULL;
				}
				else {
					$retriever_rule['data'][$setting] = $_POST['retriever_' . $setting];
				}
			}
			foreach ($_POST as $k=>$v) {
				if (preg_match("/retriever-(include|exclude)-(\d+)-(element|attribute|value)/", $k, $matches)) {
					$retriever_rule['data'][$matches[1]][intval($matches[2])][$matches[3]] = $v;
				}
			}
			// You've gotta have an element, even if it's just "*"
			foreach ($retriever_rule['data']['include'] as $k=>$clause) {
				if (!$clause['element']) {
					unset($retriever_rule['data']['include'][$k]);
				}
			}
			foreach ($retriever_rule['data']['exclude'] as $k=>$clause) {
				if (!$clause['element']) {
					unset($retriever_rule['data']['exclude'][$k]);
				}
			}
			DBA::update('retriever_rule', ['data' => json_encode($retriever_rule['data'])], ['id' => intval($retriever_rule["id"])], ['data' => '']);
			$a->page['content'] .= "<p><b>Settings Updated";
			if (!empty($_POST["retriever_retrospective"])) {
				apply_retrospective($retriever_rule, $_POST["retriever_retrospective"]);
				$a->page['content'] .= " and retrospectively applied to " . $_POST["retriever_retrospective"] . " posts";
			}
			$a->page['content'] .= ".</p></b>";
		}

		$template = Renderer::getMarkupTemplate('/rule-config.tpl', 'addon/retriever/');
		$a->page['content'] .= Renderer::replaceMacros($template, array(
								       '$enable' => array(
									       'retriever_enable',
									       L10n::t('Enabled'),
									       $retriever_rule['data']['enable']),
								       '$modurl' => array(
									       'retriever_modurl',
									       L10n::t('Modify URL'),
									       $retriever_rule['data']['modurl'],
									       L10n::t("Modify each article's URL with regular expressions before retrieving.")),
								       '$pattern' => array(
									       'retriever_pattern',
									       L10n::t('URL Pattern'),
									       $retriever_rule['data']['pattern'],
									       L10n::t('Regular expression matching part of the URL to replace')),
								       '$replace' => array(
									       'retriever_replace',
									       L10n::t('URL Replace'),
									       $retriever_rule['data']['replace'],
									       L10n::t('Text to replace matching part of above regular expression')),
								       '$allow_images' => Config::get('retriever', 'allow_images'),
								       '$images' => array(
									       'retriever_images',
									       L10n::t('Download Images'),
									       $retriever_rule['data']['images']),
								       '$retrospective' => array(
									       'retriever_retrospective',
									       L10n::t('Retrospectively Apply'),
									       '0',
									       L10n::t('Reapply the rules to this number of posts')),
								       'storecookies' => array(
									       'retriever_storecookies',
									       L10n::t('Store cookies'),
									       $retriever_rule['data']['storecookies'],
									       L10n::t("Preserve cookie data across fetches.")),
								       '$cookiedata' => array(
									       'retriever_cookiedata',
									       L10n::t('Cookie Data'),
									       $retriever_rule['data']['cookiedata'],
									       L10n::t("Latest cookie data for this feed.  Netscape cookie file format.")),
								       '$customxslt' => array(
									       'retriever_customxslt',
									       L10n::t('Custom XSLT'),
									       $retriever_rule['data']['customxslt'],
									       L10n::t("When standard rules aren't enough, apply custom XSLT to the article")),
								       '$title' => L10n::t('Retrieve Feed Content'),
								       '$help' => $a->getBaseUrl . '/retriever/help',
								       '$help_t' => L10n::t('Get Help'),
								       '$submit_t' => L10n::t('Submit'),
								       '$submit' => L10n::t('Save Settings'),
								       '$id' => ($retriever_rule["id"] ? $retriever_rule["id"] : "create"),
								       '$tag_t' => L10n::t('Tag'),
								       '$attribute_t' => L10n::t('Attribute'),
								       '$value_t' => L10n::t('Value'),
								       '$add_t' => L10n::t('Add'),
								       '$remove_t' => L10n::t('Remove'),
								       '$include_t' => L10n::t('Include'),
								       '$include' => $retriever_rule['data']['include'],
								       '$exclude_t' => L10n::t('Exclude'),
								       '$exclude' => $retriever_rule['data']['exclude']));
		return;
	}
}

/**
 * @brief Hook that adds the retriever option to the contact menu
 *
 * @param App $a The App object
 * @param array $args Contact menu details to be filled in (by ref)
 */
function retriever_contact_photo_menu($a, &$args) {
	if (!$args) {
		return;
	}
	if ($args["contact"]["network"] == "feed") {
		$args["menu"]['retriever'] = array(L10n::t('Retriever'), System::baseUrl() . '/retriever/' . $args["contact"]['id']);
	}
}

/**
 * @brief Hook for processing new incoming items
 *
 * @param App $a The App object (by ref)
 * @param array $item New item, which has not yet been inserted into database (by ref)
 */
function retriever_post_remote_hook(&$a, &$item) {
	Logger::info('retriever_post_remote_hook: ' . $item['uri'] . ' ' . $item['uid'] . ' ' . $item['contact-id']);

	$retriever_rule = get_retriever_rule($item['contact-id'], $item["uid"], false);
	if ($retriever_rule) {
		retriever_on_item_insert($retriever_rule, $item);
	}
	else {
		if (PConfig::get($item["uid"], 'retriever', 'oembed')) {
			// Convert to HTML and back to take advantage of bbcode's resolution of oembeds.
			$body = retriever_get_body($item);
			$body = HTML::toBBCode(BBCode::convert($body));
			retriever_set_body($item, $body);
		}
		if (PConfig::get($item["uid"], 'retriever', 'all_photos')) {
			retrieve_images($item);
		}
	}
	retriever_check_item_completed($item);
}

/**
 * @brief Hook for adding per-user retriever settings to the user's settings page
 *
 * @param App $a The App object (by ref)
 * @param string $s HTML string to which to append settings content (by ref)
 */
function retriever_addon_settings(&$a, &$s) {
	$all_photos = PConfig::get(local_user(), 'retriever', 'all_photos');
	$oembed = PConfig::get(local_user(), 'retriever', 'oembed');
	$template = Renderer::getMarkupTemplate('/settings.tpl', 'addon/retriever/');
	$config = array('$submit' => L10n::t('Save Settings'),
			'$title' => L10n::t('Retriever Settings'),
			'$help' => $a->getBaseUrl . '/retriever/help',
			'$allow_images' => Config::get('retriever', 'allow_images'));
	$config['$allphotos'] = array('retriever_all_photos',
				      L10n::t('All Photos'),
				      $all_photos,
				      L10n::t('Check this to retrieve photos for all posts'));
	$config['$oembed'] = array('retriever_oembed',
				   L10n::t('Resolve OEmbed'),
				   $oembed,
				   L10n::t('Check this to attempt to retrieve embedded content for all posts'));
	$s .= Renderer::replaceMacros($template, $config);
}

/**
 * @brief Hook for processing post results from user's settings page
 *
 * @param App $a The App object
 * @param array $post Posted content
 */
function retriever_addon_settings_post($a, $post) {
	if ($post['retriever_all_photos']) {
		PConfig::set(local_user(), 'retriever', 'all_photos', $post['retriever_all_photos']);
	}
	else {
		PConfig::delete(local_user(), 'retriever', 'all_photos');
	}
	if ($post['retriever_oembed']) {
		PConfig::set(local_user(), 'retriever', 'oembed', $post['retriever_oembed']);
	}
	else {
		PConfig::delete(local_user(), 'retriever', 'oembed');
	}
}
