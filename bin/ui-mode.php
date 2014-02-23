#!/usr/bin/env php
<?php

use Zend\Console\Exception\RuntimeException;
use Zend\Console\Getopt;

chdir(__DIR__ . '/../');
require_once 'vendor/autoload.php';
$viewfile   = './view/app.phtml';
$configfile = './config/module.config.php';

$opts = new Getopt(array(
    'help|h'       => 'This usage message',
    'dev|d'        => 'Enable Development mode (use src UI)',
    'production|p' => 'Enable Production mode (use dist UI)',
));

try {
    $opts->parse();
} catch (RuntimeException $e) {
    echo $e->getUsageMessage();
    exit(1);
}

if (isset($opts->h)) {
    echo $opts->getUsageMessage();
    exit(0);
}

if ((!isset($opts->d) && !isset($opts->p))
    || (isset($opts->d) && isset($opts->p))
) {
    echo "Please select one of EITHER --dev OR --production.\n";
    echo $opts->getUsageMessage();
    exit(1);
}

if (isset($opts->d)) {
    echo "Enabling development mode\n";
    $version = 'src';
} else {
    echo "Enabling production mode\n";
    $version = 'dist';
}

updateConfig($configfile, $version);
updateView($viewfile, $version);

echo "Done!\n";

function updateView($viewfile, $version)
{
    echo "    Updating view\n";
    $view  = file_get_contents($viewfile);
    $regex = '/^(\$version\s+=\s+\')([^\']+)/m';
    $view  = preg_replace($regex, '$1' . $version, $view);
    file_put_contents($viewfile, $view);
}

function updateConfig($configfile, $version)
{
    echo "    Updating config\n";
    $config = file_get_contents($configfile);
    $regex  = '#(\'/\.\./asset/)([^\']+)#m';
    $config = preg_replace($regex, '$1' . $version, $config);
    file_put_contents($configfile, $config);
}
