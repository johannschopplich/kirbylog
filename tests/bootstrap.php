<?php

require_once __DIR__ . '/bootstrap.php';

$kirby = new \Kirby\Cms\App();

echo $kirby->render();
