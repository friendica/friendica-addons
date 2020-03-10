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

use Friendica\DI;

$container = $slim->getContainer();

// Error handler based off https://stackoverflow.com/a/48135009/757392
$container['errorHandler'] = function () {
	return function(Psr\Http\Message\RequestInterface $request, Psr\Http\Message\ResponseInterface $response, Exception $exception)
	{
		$responseCode = 500;

		if (is_a($exception, 'Friendica\Network\HTTPException')) {
			$responseCode = $exception->getCode();
		}

		$errors['message'] = $exception->getMessage();

		$errors['responseCode'] = $responseCode;

		return $response
				->withStatus($responseCode)
				->withJson($errors);
	};
};

$container['notFoundHandler'] = function () {
	return function ()
	{
		throw new \Friendica\Network\HTTPException\NotFoundException(DI::l10n()->t('Method not found'));
	};
};
