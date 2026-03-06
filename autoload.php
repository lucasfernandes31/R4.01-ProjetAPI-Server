<?php

require_once __DIR__ . '/Psr4AutoloaderClass.php';

$loader = new R301\Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

?>