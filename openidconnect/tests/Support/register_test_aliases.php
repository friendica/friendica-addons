<?php

declare(strict_types=1);

$testClassAliases = [
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\BaseModule' => 'Friendica\\BaseModule',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\DI' => 'Friendica\\DI',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\TestHttpResponse' => 'Friendica\\TestHttpResponse',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Database\\DBA' => 'Friendica\\Database\\DBA',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Model\\User' => 'Friendica\\Model\\User',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Model\\Contact' => 'Friendica\\Model\\Contact',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Core\\Hook' => 'Friendica\\Core\\Hook',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Core\\Renderer' => 'Friendica\\Core\\Renderer',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Core\\Config\\Util\\ConfigFileManager' => 'Friendica\\Core\\Config\\Util\\ConfigFileManager',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Core\\Config\\ValueObject\\Cache' => 'Friendica\\Core\\Config\\ValueObject\\Cache',
	'Friendica\\Addon\\OpenIdConnect\\Tests\\Doubles\\Core\\Network\\HTTPClient\\Client\\HttpClientOptions' => 'Friendica\\Core\\Network\\HTTPClient\\Client\\HttpClientOptions',
];

foreach ($testClassAliases as $doubleClass => $runtimeClass) {
	if (!class_exists($runtimeClass, false)) {
		class_alias($doubleClass, $runtimeClass);
	}
}