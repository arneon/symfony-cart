<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

header('Content-Type: application/json');

echo Generator::scan(
    [
        __DIR__ . '/../src',
        __DIR__ . '/../siroko-bundles',
    ]
)->toJson();
