#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Katzgrau\KLogger\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Application;

// Set the default timezone to use.
date_default_timezone_set('UTC');

// Load application .env config file:
$file = __DIR__.DIRECTORY_SEPARATOR.'.env';
if (file_exists($file)) {
    $dotenv = new Dotenv();
    $dotenv->populate([
        'APPDIR' => __DIR__,
    ], true);
    $dotenv->load($file);
}

// Create a logger object:
$logger = null;
if (getenv('LOG')) {
    $logDir = (getenv('LOGDIR')) ?: 'log';
    if (class_exists('Logger')) {
        $logger = new Logger($logDir, Psr\Log\LogLevel::INFO, [
            'extension' => 'log',
        ]);
    }
}

// Create the application:
$app = new Application('Simple Web Crawler', '1.0 (stable)');

// Register all commands from src/Commands directory:
foreach (glob('src'.DIRECTORY_SEPARATOR.'Command'.DIRECTORY_SEPARATOR.'*') as $command) {
    $class = 'Console\Command\\'.pathinfo($command)['filename'];
    $app->add(new $class($logger));
}

$app->run();
