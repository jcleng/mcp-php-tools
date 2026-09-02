#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

use PhpMcp\Http\Server;
use PhpMcp\Http\Tool\TimeTool;

$server = new Server([
    new TimeTool(),
]);

$server->handleStdin();
