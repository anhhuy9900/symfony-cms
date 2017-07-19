<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

/**
 * @import libraries vendor
 */
$loader->add('PHPExcel', __DIR__.'/../vendor/PHPExcel');

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
