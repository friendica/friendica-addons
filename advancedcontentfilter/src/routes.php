<?php
/**
 * @copyright Copyright (C) 2020, Friendica
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

/* @var $slim Slim\App */
$slim->group('/advancedcontentfilter/api', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->group('/rules', function (\Slim\Routing\RouteCollectorProxy $app) {
		$app->get('', 'advancedcontentfilter_get_rules');
		$app->post('', 'advancedcontentfilter_post_rules');

		$app->get('/{id}', 'advancedcontentfilter_get_rules_id');
		$app->put('/{id}', 'advancedcontentfilter_put_rules_id');
		$app->delete('/{id}', 'advancedcontentfilter_delete_rules_id');
	});

	$app->group('/variables', function (\Slim\Routing\RouteCollectorProxy $app) {
		$app->get('/{guid}', 'advancedcontentfilter_get_variables_guid');
	});
});
