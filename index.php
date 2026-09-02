<?php
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

use PhpMcp\Http\Server;
use PhpMcp\Http\Tool\TimeTool;
use PhpMcp\Http\Tool\SpreadsheetReadTool;
use PhpMcp\Http\Tool\SpreadsheetModifyTool;
use PhpMcp\Http\Tool\SpreadsheetToPdfTool;
use PhpMcp\Http\Tool\SpreadsheetToHtmlTool;

$server = new Server([
    new TimeTool(),
    new SpreadsheetReadTool(),
    new SpreadsheetModifyTool(),
    new SpreadsheetToPdfTool(),
    new SpreadsheetToHtmlTool(),
]);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 支持 /mcp 和 /index.php/mcp
if (!preg_match('#/mcp$#', $path)) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

if ($method === 'POST') {
    $server->handlePost();
} elseif ($method === 'GET') {
    $server->handleSse();
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}
