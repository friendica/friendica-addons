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
use Friendica\BaseModule;
use Friendica\Content\Text\Markdown;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\Database\DBStructure;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Model\Tag;
use Friendica\Module\Security\Login;
use Friendica\Network\HTTPException;
use Friendica\Util\DateTimeFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\ExpressionLanguage;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function advancedcontentfilter_install(App $a)
{
	Hook::register('dbstructure_definition'     , __FILE__, 'advancedcontentfilter_dbstructure_definition');
	Hook::register('prepare_body_content_filter', __FILE__, 'advancedcontentfilter_prepare_body_content_filter');
	Hook::register('addon_settings'             , __FILE__, 'advancedcontentfilter_addon_settings');

	Hook::add('dbstructure_definition'          , __FILE__, 'advancedcontentfilter_dbstructure_definition');
	DBStructure::performUpdate();

	Logger::log("installed advancedcontentfilter");
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
			"created"    => ["type" => "datetime"    , "not null" => "1", "default" => DBA::NULL_DATETIME, "comment" => "Creation date"],
		],
		"indexes" => [
			"PRIMARY" => ["id"],
			"uid_active" => ["uid", "active"],
		]
	];
}

function advancedcontentfilter_get_filter_fields(array $item)
{
	$vars = [];

	// Convert the language JSON text into a filterable format
	if (!empty($item['language']) && ($languages = json_decode($item['language'], true))) {
		foreach ($languages as $key => $value) {
			$vars['language_' . strtolower($key)] = $value;
		}
	}

	foreach ($item as $key => $value) {
		$vars[str_replace('-', '_', $key)] = $value;
	}

	ksort($vars);

	return $vars;
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

	$vars = advancedcontentfilter_get_filter_fields($hook_data['item']);

	$rules = DI::cache()->get('rules_' . local_user());
	if (!isset($rules)) {
		$rules = DBA::toArray(DBA::select(
			'advancedcontentfilter_rules',
			['name', 'expression', 'serialized'],
			['uid' => local_user(), 'active' => true]
		));

		DI::cache()->set('rules_' . local_user(), $rules);
	}

	if ($rules) {
		foreach($rules as $rule) {
			try {
				$serializedParsedExpression = new ExpressionLanguage\SerializedParsedExpression(
					$rule['expression'],
					$rule['serialized']
				);

				// The error suppression operator is used because of potentially broken user-supplied regular expressions
				$found = (bool) @$expressionLanguage->evaluate($serializedParsedExpression, $vars);
			} catch (Exception $e) {
				$found = false;
			}

			if ($found) {
				$hook_data['filter_reasons'][] = DI::l10n()->t('Filtered by rule: %s', $rule['name']);
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

	$advancedcontentfilter = DI::l10n()->t('Advanced Content Filter');

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
	if ($a->argc > 1 && $a->argv[1] == 'api') {
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
		return Login::form('/' . implode('/', $a->argv));
	}

	if ($a->argc > 1 && $a->argv[1] == 'help') {
		$lang = $a->user['language'];

		$default_dir = 'addon/advancedcontentfilter/doc/';
		$help_file = 'advancedcontentfilter.md';
		$help_path = $default_dir . $help_file;
		if (file_exists($default_dir . $lang . '/' . $help_file)) {
			$help_path = $default_dir . $lang . '/' . $help_file;
		}

		$content = file_get_contents($help_path);

		$html = Markdown::convert($content, false);

		$html = str_replace('code>', 'key>', $html);

		return $html;
	} else {
		$t = Renderer::getMarkupTemplate('settings.tpl', 'addon/advancedcontentfilter/');
		return Renderer::replaceMacros($t, [
			'$messages' => [
				'backtosettings'    => DI::l10n()->t('Back to Addon Settings'),
				'title'             => DI::l10n()->t('Advanced Content Filter'),
				'add_a_rule'        => DI::l10n()->t('Add a Rule'),
				'help'              => DI::l10n()->t('Help'),
				'intro'             => DI::l10n()->t('Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the help page.'),
				'your_rules'        => DI::l10n()->t('Your rules'),
				'no_rules'          => DI::l10n()->t('You have no rules yet! Start adding one by clicking on the button above next to the title.'),
				'disabled'          => DI::l10n()->t('Disabled'),
				'enabled'           => DI::l10n()->t('Enabled'),
				'disable_this_rule' => DI::l10n()->t('Disable this rule'),
				'enable_this_rule'  => DI::l10n()->t('Enable this rule'),
				'edit_this_rule'    => DI::l10n()->t('Edit this rule'),
				'edit_the_rule'     => DI::l10n()->t('Edit the rule'),
				'save_this_rule'    => DI::l10n()->t('Save this rule'),
				'delete_this_rule'  => DI::l10n()->t('Delete this rule'),
				'rule'              => DI::l10n()->t('Rule'),
				'close'             => DI::l10n()->t('Close'),
				'addtitle'          => DI::l10n()->t('Add new rule'),
				'rule_name'         => DI::l10n()->t('Rule Name'),
				'rule_expression'   => DI::l10n()->t('Rule Expression'),
				'cancel'            => DI::l10n()->t('Cancel'),
			],
			'$current_theme' => $a->getCurrentTheme(),
			'$rules' => advancedcontentfilter_get_rules(),
			'$form_security_token' => BaseModule::getFormSecurityToken()
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
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	$rules = DBA::toArray(DBA::select('advancedcontentfilter_rules', [], ['uid' => local_user()]));

	return json_encode($rules);
}

function advancedcontentfilter_get_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	$rule = DBA::selectFirst('advancedcontentfilter_rules', [], ['id' => $args['id'], 'uid' => local_user()]);

	return json_encode($rule);
}

function advancedcontentfilter_post_rules(ServerRequestInterface $request)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	if (!BaseModule::checkFormSecurityToken()) {
		throw new HTTPException\BadRequestException(DI::l10n()->t('Invalid form security token, please refresh the page.'));
	}

	$data = json_decode($request->getBody(), true);

	try {
		$fields = advancedcontentfilter_build_fields($data);
	} catch (Exception $e) {
		throw new HTTPException\BadRequestException($e->getMessage(), $e);
	}

	if (empty($fields['name']) || empty($fields['expression'])) {
		throw new HTTPException\BadRequestException(DI::l10n()->t('The rule name and expression are required.'));
	}

	$fields['uid'] = local_user();
	$fields['created'] = DateTimeFormat::utcNow();

	if (!DBA::insert('advancedcontentfilter_rules', $fields)) {
		throw new HTTPException\ServiceUnavailableException(DBA::errorMessage());
	}

	$rule = DBA::selectFirst('advancedcontentfilter_rules', [], ['id' => DBA::lastInsertId()]);

	return json_encode(['message' => DI::l10n()->t('Rule successfully added'), 'rule' => $rule]);
}

function advancedcontentfilter_put_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	if (!BaseModule::checkFormSecurityToken()) {
		throw new HTTPException\BadRequestException(DI::l10n()->t('Invalid form security token, please refresh the page.'));
	}

	if (!DBA::exists('advancedcontentfilter_rules', ['id' => $args['id'], 'uid' => local_user()])) {
		throw new HTTPException\NotFoundException(DI::l10n()->t('Rule doesn\'t exist or doesn\'t belong to you.'));
	}

	$data = json_decode($request->getBody(), true);

	try {
		$fields = advancedcontentfilter_build_fields($data);
	} catch (Exception $e) {
		throw new HTTPException\BadRequestException($e->getMessage(), $e);
	}

	if (!DBA::update('advancedcontentfilter_rules', $fields, ['id' => $args['id']])) {
		throw new HTTPException\ServiceUnavailableException(DBA::errorMessage());
	}

	return json_encode(['message' => DI::l10n()->t('Rule successfully updated')]);
}

function advancedcontentfilter_delete_rules_id(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	if (!BaseModule::checkFormSecurityToken()) {
		throw new HTTPException\BadRequestException(DI::l10n()->t('Invalid form security token, please refresh the page.'));
	}

	if (!DBA::exists('advancedcontentfilter_rules', ['id' => $args['id'], 'uid' => local_user()])) {
		throw new HTTPException\NotFoundException(DI::l10n()->t('Rule doesn\'t exist or doesn\'t belong to you.'));
	}

	if (!DBA::delete('advancedcontentfilter_rules', ['id' => $args['id']])) {
		throw new HTTPException\ServiceUnavailableException(DBA::errorMessage());
	}

	return json_encode(['message' => DI::l10n()->t('Rule successfully deleted')]);
}

function advancedcontentfilter_get_variables_guid(ServerRequestInterface $request, ResponseInterface $response, $args)
{
	if (!local_user()) {
		throw new HTTPException\UnauthorizedException(DI::l10n()->t('You must be logged in to use this method'));
	}

	if (!isset($args['guid'])) {
		throw new HTTPException\BadRequestException(DI::l10n()->t('Missing argument: guid.'));
	}

	$condition = ["`guid` = ? AND (`uid` = ? OR `uid` = 0)", $args['guid'], local_user()];
	$params = ['order' => ['uid' => true]];
	$item = Post::selectFirstForUser(local_user(), [], $condition, $params);

	if (!DBA::isResult($item)) {
		throw new HTTPException\NotFoundException(DI::l10n()->t('Unknown post with guid: %s', $args['guid']));
	}

	$tags = Tag::populateFromItem($item);

	$item['tags'] = $tags['tags'];
	$item['hashtags'] = $tags['hashtags'];
	$item['mentions'] = $tags['mentions'];

	$return = advancedcontentfilter_get_filter_fields($item);

	return json_encode(['variables' => str_replace('\\\'', '\'', var_export($return, true))]);
}
