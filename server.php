#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

use PhpMcp\Http\Server;
use PhpMcp\Http\Tool\TimeTool;
use PhpMcp\Http\Tool\SpreadsheetReadTool;
use PhpMcp\Http\Tool\SpreadsheetModifyTool;
use PhpMcp\Http\Tool\SpreadsheetToPdfTool;
use PhpMcp\Http\Tool\SpreadsheetToHtmlTool;
use PhpMcp\Http\Tool\PhpExecuteTool;
use PhpMcp\Http\Tool\SpreadsheetCreateTool;
use PhpMcp\Http\Prompt\ReadXlsxPrompt;

$server = new Server([
    new TimeTool(),
    new SpreadsheetReadTool(),
    new SpreadsheetModifyTool(),
    new SpreadsheetToPdfTool(),
    new SpreadsheetToHtmlTool(),
    new SpreadsheetCreateTool(),
    new PhpExecuteTool(),
], [
    new ReadXlsxPrompt(),
]);

$server->handleStdin();
