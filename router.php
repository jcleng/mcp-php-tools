<?php
// router.php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 只处理 /mcp 请求，其他返回 404
if ($path === '/mcp') {
    require __DIR__ . '/index.php';
    return;
}

// 静态文件（如果有）
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false; // 交给内置服务器直接处理
}

// 其他路由返回 404
http_response_code(404);
echo 'Not Found';
