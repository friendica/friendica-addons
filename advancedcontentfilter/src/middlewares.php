<?php

use Friendica\DI;

$container = $slim->getContainer();

// Error handler based off https://stackoverflow.com/a/48135009/757392
$container['errorHandler'] = function () {
	return function(Psr\Http\Message\RequestInterface $request, Psr\Http\Message\ResponseInterface $response, Exception $exception)
	{
		$responseCode = 500;

		if (is_a($exception, 'Friendica\Network\HTTPException')) {
			$responseCode = $exception->httpcode;
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
