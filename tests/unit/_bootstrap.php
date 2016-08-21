<?php

use Nette\Configurator;
use Nette\Utils\FileSystem;
use Codeception\Util\Fixtures;

$logPath = __DIR__ . '/../_nette/_log/_unit';
$tempPath = __DIR__ . '/../_nette/_temp/_unit';
$confPath = __DIR__ . '/../_nette/_config';

FileSystem::delete($logPath);
FileSystem::createDir($logPath);
FileSystem::delete($tempPath);
FileSystem::createDir($tempPath);

$configurator = new Configurator;

$configurator->enableDebugger($logPath);
$configurator->setTempDirectory($tempPath);

$confFile = (file_exists($confPath . '/config.neon') === TRUE) ?
    $confPath . '/config.neon' :
    $confPath . '/config.sample.neon';

$configurator->addConfig($confFile);

$container = $configurator->createContainer();
Fixtures::add('container', $container);
