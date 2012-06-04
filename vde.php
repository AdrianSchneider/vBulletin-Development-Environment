<?php

error_reporting(E_ALL ^ E_NOTICE ^ 8192);
define('THIS_SCRIPT', 'vde');

if (!is_array($argv)) {
    die('VDE must be run via CLI');
}

define('CLI_ARGS', serialize($argv));
chdir(dirname($_SERVER['SCRIPT_NAME']));

require('./global.php');
require_once(DIR . '/includes/vde/functions.php');
require_once(DIR . '/includes/vde/project.php');
require_once(DIR . '/includes/vde/cli.php');
require_once(DIR . '/includes/vde/command.php');

$argv = unserialize(CLI_ARGS);

try {
    $cli = new VDE_CLI($vbulletin, $argv);
    $cli->run();
} catch (VDE_CLI_Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}