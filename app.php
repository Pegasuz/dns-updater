#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/UpdateCommand.php';

use Symfony\Component\ClassLoader\ClassLoader;
use Symfony\Component\Console\Application;

$loader = new ClassLoader();
$loader->setUseIncludePath(true);
$loader->register();

$application = new Application();
$application->add(new UpdateCommand());
$application->run();
