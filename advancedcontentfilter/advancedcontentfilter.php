<?php
/**
 * Name: Advanced content Filter
 * Description: Expression-based content filter
 * Version: 1.0
 * Author: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 * Maintainer: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 *
 * Copyright (c) 2018 Hypolite Petovan
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above
 *    * copyright notice, this list of conditions and the following disclaimer in
 *      the documentation and/or other materials provided with the distribution.
 *    * Neither the name of Friendica nor the names of its contributors
 *      may be used to endorse or promote products derived from this software
 *      without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL FRIENDICA BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

use Friendica\App;
use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Database\DBStructure;
use Friendica\Network\HTTPException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\ExpressionLanguage;

require_once 'boot.php';
require_once 'include/conversation.php';
require_once 'include/dba.php';
require_once 'include/security.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function advancedcontentfilter_install()
{
	Addon::registerHook('dbstructure_definition'     , __FILE__, 'advancedcontentfilter_dbstructure_definition');
	Addon::registerHook('prepare_body_content_filter', __FILE__, 'advancedcontentfilter_prepare_body_content_filter');
	Addon::registerHook('addon_settings'             , __FILE__, 'advancedcontentfilter_addon_settings');

	DBStructure::update(false, true);

	logger("installed advancedcontentfilter");
}

function advancedcontentfilter_uninstall()
{
	Addon::unregisterHook('dbstructure_definition'     , __FILE__, 'advancedcontentfilter_dbstructure_definition');
	Addon::unregisterHook('prepare_body_content_filter', __FILE__, 'advancedcontentfilter_prepare_body_content_filter');
	Addon::unregisterHook('addon_settings'             , __FILE__, 'advancedcontentfilter_addon_settings');
}

/*
 * Hooks
 */

function advancedcontentfilter_dbstructure_definition(App $a, &$database)
{
	$database["advancedcontentfilter_rules"] = [
		"comment" => "Advancedcontentfilter addon rules",
		"fields" => [
			"id"         => ["type" => "int unsigned", "not null" => "1", "extra" => "auto_increment", "primary" => "1", "comment" => "Auto incremented rule id"],
			"uid"        => ["type" => "int unsigned", "not null" => "1", "comment" => "Owner user id"],
			"name"       => ["type" => "varchar(255)", "not null" => "1", "comment" => "Rule name"],
			"expression" => ["type" => "mediumtext"  , "not null" => "1", "comment" => "Expression text"],
			"serialized" => ["type" => "mediumtext"  , "not null" => "1", "comment" => "Serialized parsed expression"],
			"active"     => ["type" => "boolean"     , "not null" => "1", "default" => "1", "comment" => "Whether the rule is active or not"],
			"created"    => ["type" => "datetime"    , "not null" => "1", "default" => NULL_DATE, "comment" => "Creation date"],
		],
		"indexes" => [
			"PRIMARY" => ["id"],
			"uid_active" => ["uid", "active"],
		]
	];
}

function advancedcontentfilter_prepare_body_content_filter(App $a, &$hook_data)
{
	static $expressionLanguage;

	if (is_null($expressionLanguage)) {
		$expressionLanguage = new ExpressionLanguage\ExpressionLanguage();
	}

	if (!local_user()) {
		return;
	}

	$vars = [];
	foreach ($hook_data['item'] as $key => $value) {
		$vars[str_replace('-', '_', $key)] = $value;
	}

	$rules = Friendica\Core\Cache::get('rules_' . local_user());
	if (!isset($rules)) {
		$rules = dba::inArray(dba::select(
			'advancedcontentfilter_rules',
			['name', 'expression', 'serialized'],
			['uid' => local_user(), 'active' => true]
		));
	}

	if ($rules) {
		foreach($rules as $rule) {
			try {
				$serializedParsedExpression = new ExpressionLanguage\SerializedParsedExpression(
					$rule['expression'],
					$rule['serialized']
				);

				$found = (bool) $expressionLanguage->evaluate($serializedParsedExpression, $vars);
			} catch (Exception $e) {
				$found = false;
			}

			if ($found) {
				$hook_data['filter_reasons'][] = L10n::t('Filtered by rule: %s', $rule['name']);
				break;
			}
		}
	}
}


function advancedcontentfilter_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$advancedcontentfilter = L10n::t('Advanced Content Filter');

	$s .= <<<HTML
		<span class="settings-block fakelink" style="display: block;"><h3><a href="advancedcontentfilter">$advancedcontentfilter <i class="glyphicon glyphicon-share"></i></a></h3></span>
HTML;

	return;
}

/*
 * Module
 */

function advancedcontentfilter_module() {}

function advancedcontentfilter_init(App $a)
{
	if ($a->argv[1] == 'api') {
		$slim = new \Slim\App();

		require __DIR__ . '/src/middlewares.php';

		require __DIR__ . '/src/routes.php';
		$slim->run();

		exit;
	}
}

function advancedcontentfilter_content(App $a)
{
	if (!local_user()) {
		return \Friendica\Module\Login::form('/' . implode('/', $a->argv));
	}

	if ($a->argc > 0 && $a->argv[1] == 'help') {
		$lang = $a->user['language'];

		$default_dir = 'addon/advancedcontentfilter/doc/';
		$help_file = 'advancedcontentfilter.md';
		$help_path = $default_dir . $help_file;
		if (file_exists($default_dir . $lang . '/' . $help_file)) {
			$help_path = $default_dir . $lang . '/' . $help_file;
		}

		$content = file_get_contents($help_path);

		$html = \Friendica\Content\Text\Markdown::convert($content, false);

		$html = str_replace('code>', 'key>', $html);

		return $html;
	} else {
		$t = get_markup_template('settings.tpl', 'addon/advancedcontentfilter/');
		return replace_macros($t, [
			'$backtosettings' => L10n::t('Back to Addon Settings'),
			'$title' => L10n::t('Advanced Content Filter'),
			'$add_a_rule' => L10n::t('Add a Rule'),
			'$help' => L10n::t('Help'),
			'$advanced_content_filter_intro' => L10n::t('Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href="advancedcontentfilter/help">help page</a>.'),
			'$your_rules' => L10n::t('Your rules'),
			'$no_rules' => L10n::t('You have no rules yet! Start adding one by clicking on the button above next to the title.'),
			'$disabled' => L10n::t('Disabled'),
			'$enabled' => L10n::t('Enabled'),
			'$disable_this_rule' => L10n::t('Disable this rule'),
			'$enable_this_rule' => L10n::t('Enable this rule'),
			'$edit_this_rule' => L10n::t('Edit this rule'),
			'$edit_the_rule' => L10n::t('Edit the rule'),
			'$save_this_rule' => L10n::t('Save this rule'),
			'$delete_this_rule' => L10n::t('Delete this rule'),
			'$rule' => L10n::t('Rule'),
			'$close' => L10n::t('Close'),
			'$addtitle' => L10n::t('Add new rule'),
			'$rule_name' => L10n::t('Rule Name'),
			'$rule_expression' => L10n::t('Rule Expression'),
			'$examples' => L10n::t('<p>Examples:</p><ul><li><pre>author_link == \'https://friendica.mrpetovan.com/profile/hypolite\'</pre></li><li>tags</li></ul>'),
			'$cancel' => L10n::t('Cancel'),
			'$rules' => advancedcontentfilter_get_rules(),
			'$baseurl' => System::baseUrl(true),
			'$form_security_token' => get_form_security_token()
		]);
	}
}

/*
 * Common functions
 */
function advancedcontentfilter_build_fields($data)
{
	$fields = [];

	if (!empty($data['name'])) {
		$fields['name'] = $data['name'];
	}

	if (!empty($data['expression'])) {
		$allowed_keys = [
			'author_id', 'author_link', 'author_name', 'author_avatar',
			'owner_id', 'owner_link', 'owner_name', 'owner_avatar',
			'contact_id', 'uid', 'id', 'parent', 'uri',
			'thr_parent', 'parent_uri',
			'content_warning',
			'commented', 'created', 'edited', 'received',
			'verb', 'object_type', 'postopts', 'plink', 'guid', 'wall', 'private', 'starred',
			'title', 'body',
			'file', 'event_id', 'location', 'coord', 'app', 'attach',
			'rendered_hash', 'rendered_html', 'object',
			'allow_cid', 'allow_gid', 'deny_cid', 'deny_gid',
			'item_id', 'item_network', 'author_thumb', 'owner_thumb',
			'network', 'url', 'name', 'writable', 'self',
			'cid', 'alias',
			'event_created', 'event_edited', 'event_start', 'event_finish', 'event_summary',
			'event_desc', 'event_location', 'event_type', 'event_nofinish', 'event_adjust', 'event_ignore',
			'children', 'pagedrop', 'tags', 'hashtags', 'mentions',
		];

		$expressionLanguage = new ExpressionLanguage\ExpressionLanguage();

		$parsedExpression = $expressionLanguage->parse($data['expression'], $allowed_keys);

		$serialized = serialize($parsedExpression->getNodes());

		$fields['expression'] = $data['expression'];
		$fields['serialized'] = $serialized;
	}

	if (isset($data['active'])) {
		$fields['active'] = intval($data['active']);
	} else {
		$fields['active'] = 1;
	}

	return $fields;
}

/*
 * API
 */

function advancedcontentfilter_get_rules()
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	$rules = dba::inArray(dba::select('advancedcontentfilter_rules', [], ['uid' => local_user()]));

	return json_encode($rules);
}

function advancedcontentfilter_get_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	$rule = dba::selectFirst('advancedcontentfilter_rules', [], ['id' => $args['id'], 'uid' => local_user()]);

	return json_encode($rule);
}

function advancedcontentfilter_post_rules(ServerRequestInterface $request)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	if (!check_form_security_token()) {
		throw new HTTPException\BadRequestException(L10n::t('Invalid form security token, please refresh the page.'));
	}

	$data = json_decode($request->getBody(), true);

	try {
		$fields = advancedcontentfilter_build_fields($data);
	} catch (Exception $e) {
		throw new HTTPException\BadRequestException($e->getMessage(), 0, $e);
	}

	if (empty($fields['name']) || empty($fields['expression'])) {
		throw new HTTPException\BadRequestException(L10n::t('The rule name and expression are required.'));
	}

	$fields['uid'] = local_user();
	$fields['created'] = \Friendica\Util\DateTimeFormat::utcNow();

	if (!dba::insert('advancedcontentfilter_rules', $fields)) {
		throw new HTTPException\ServiceUnavaiableException(dba::errorMessage());
	}

	$rule = dba::selectFirst('advancedcontentfilter_rules', [], ['id' => dba::lastInsertId()]);

	return json_encode(['message' => L10n::t('Rule successfully added'), 'rule' => $rule]);
}

function advancedcontentfilter_put_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	if (!check_form_security_token()) {
		throw new HTTPException\BadRequestException(L10n::t('Invalid form security token, please refresh the page.'));
	}

	if (!dba::exists('advancedcontentfilter_rules', ['id' => $args['id'], 'uid' => local_user()])) {
		throw new HTTPException\NotFoundException(L10n::t('Rule doesn\'t exist or doesn\'t belong to you.'));
	}

	$data = json_decode($request->getBody(), true);

	try {
		$fields = advancedcontentfilter_build_fields($data);
	} catch (Exception $e) {
		throw new HTTPException\BadRequestException($e->getMessage(), 0, $e);
	}

	if (!dba::update('advancedcontentfilter_rules', $fields, ['id' => $args['id']])) {
		throw new HTTPException\ServiceUnavaiableException(dba::errorMessage());
	}

	return json_encode(['message' => L10n::t('Rule successfully updated')]);
}

function advancedcontentfilter_delete_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	if (!check_form_security_token()) {
		throw new HTTPException\BadRequestException(L10n::t('Invalid form security token, please refresh the page.'));
	}

	if (!dba::exists('advancedcontentfilter_rules', ['id' => $args['id'], 'uid' => local_user()])) {
		throw new HTTPException\NotFoundException(L10n::t('Rule doesn\'t exist or doesn\'t belong to you.'));
	}

	if (!dba::delete('advancedcontentfilter_rules', ['id' => $args['id']])) {
		throw new HTTPException\ServiceUnavaiableException(dba::errorMessage());
	}

	return json_encode(['message' => L10n::t('Rule successfully deleted')]);
}

function advancedcontentfilter_get_variables_guid(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(L10n::t('You must be logged in to use this method'));
	}

	if (!isset($args['guid'])) {
		throw new HTTPException\BadRequestException(L10n::t('Missing argument: guid.'));
	}

	$item = dba::fetch_first(item_query() . " AND `item`.`guid` = ? AND (`item`.`uid` = ? OR `item`.`uid` = 0) ORDER BY `item`.`uid` DESC", $args['guid'], local_user());

	if (!\Friendica\Database\DBM::is_result($item)) {
		throw new HTTPException\NotFoundException(L10n::t('Unknown post with guid: %s', $args['guid']));
	}

	$tags = \Friendica\Model\Term::populateTagsFromItem($item);

	$item['tags'] = $tags['tags'];
	$item['hashtags'] = $tags['hashtags'];
	$item['mentions'] = $tags['mentions'];

	$return = [];
	foreach ($item as $key => $value) {
		$return[str_replace('-', '_', $key)] = $value;
	}

	return json_encode(['variables' => str_replace('\\\'', '\'', var_export($return, true))]);
}