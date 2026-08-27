<?php return array(
    'root' => array(
        'name' => 'friendica-addons/s3_storage',
        'pretty_version' => 'dev-2026.08-rc',
        'version' => 'dev-2026.08-rc',
        'reference' => '4df4f9eeff86ae9fe9d58aea7abd9c59ce9da8ee',
        'type' => 'friendica-addon',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'akeeba/s3' => array(
            'pretty_version' => '2.5.0',
            'version' => '2.5.0.0',
            'reference' => '111fdfc7d2a9f9cebedf07a0300f24942f25b42d',
            'type' => 'library',
            'install_path' => __DIR__ . '/../akeeba/s3',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'friendica-addons/s3_storage' => array(
            'pretty_version' => 'dev-2026.08-rc',
            'version' => 'dev-2026.08-rc',
            'reference' => '4df4f9eeff86ae9fe9d58aea7abd9c59ce9da8ee',
            'type' => 'friendica-addon',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
