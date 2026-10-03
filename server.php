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
use PhpMcp\Http\Tool\DingTalkNotifyTool;
use PhpMcp\Http\Prompt\ReadXlsxPrompt;
use PhpMcp\Http\Prompt\UseProxyPrompt;
use PhpMcp\Http\Prompt\SummarizeAndIndexPrompt;

$server = new Server([
    new TimeTool(),
    new SpreadsheetReadTool(),
    new SpreadsheetModifyTool(),
    new SpreadsheetToPdfTool(),
    new SpreadsheetToHtmlTool(),
    new SpreadsheetCreateTool(),
    new PhpExecuteTool(),
    new DingTalkNotifyTool(),
], [
    new ReadXlsxPrompt(),
    new UseProxyPrompt(),
    new SummarizeAndIndexPrompt(),
]);

$server->handleStdin();
