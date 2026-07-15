<?php return array(
    'root' => array(
        'name' => 'friendica-addons/monolog',
        'pretty_version' => 'dev-develop',
        'version' => 'dev-develop',
        'reference' => '542c6f8cbbea5e2339617fb836f656b1bdbdc8f0',
        'type' => 'friendica-addon',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'friendica-addons/monolog' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'reference' => '542c6f8cbbea5e2339617fb836f656b1bdbdc8f0',
            'type' => 'friendica-addon',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.11.0',
            'version' => '2.11.0.0',
            'reference' => '37308608e599f34a1a4845b16440047ec98a172a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '3.0.2',
            'version' => '3.0.2.0',
            'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '1.0.0 || 2.0.0 || 3.0.0',
            ),
        ),
    ),
);
