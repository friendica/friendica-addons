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
$slim->group('/advancedcontentfilter/api', function () {
	/* @var $this Slim\App */
	$this->group('/rules', function () {
		/* @var $this Slim\App */
		$this->get('', 'advancedcontentfilter_get_rules');
		$this->post('', 'advancedcontentfilter_post_rules');

		$this->get('/{id}', 'advancedcontentfilter_get_rules_id');
		$this->put('/{id}', 'advancedcontentfilter_put_rules_id');
		$this->delete('/{id}', 'advancedcontentfilter_delete_rules_id');
	});

	$this->group('/variables', function () {
		/* @var $this Slim\App */
		$this->get('/{guid}', 'advancedcontentfilter_get_variables_guid');
	});
});
