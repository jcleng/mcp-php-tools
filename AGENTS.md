# AGENTS.md

## 项目概述

简易 PHP 实现的 MCP（Model Context Protocol，模型上下文协议）服务器，同时支持 HTTP（SSE 流式）和 stdio 两种传输方式。

- 项目名：php-mcp-http
- 命名空间：`PhpMcp\Http`
- PHP 版本要求：`>= 8.1`

## 项目结构

```
src/
  Server.php            核心服务器类（PhpMcp\Http\Server）
  Tool/
    ToolInterface.php   MCP 工具接口（PhpMcp\Http\Tool\ToolInterface）
    TimeTool.php        示例工具（PhpMcp\Http\Tool\TimeTool）
index.php               HTTP 入口
router.php              内置 Web 服务器路由（PHP -S）
server.php              stdio 入口
composer.json           Composer / PSR-4 自动加载配置
```

## 自动加载与命名空间

- Composer PSR-4 自动加载：`"PhpMcp\\Http\\": "src/"`
- 命名空间与目录一一对应：`PhpMcp\Http\Server` → `src/Server.php`，`PhpMcp\Http\Tool\TimeTool` → `src/Tool/TimeTool.php`
- 修改 `composer.json` 或新增/移动命名空间类后，需运行 `composer dump-autoload` 重新生成自动加载

## 核心类

### `PhpMcp\Http\Server`（src/Server.php）
MCP 服务器核心类，通过构造器注入工具数组：
```php
$server = new Server([ new TimeTool() ]);
```
主要方法：
- `handlePost(): void` — 处理 JSON-RPC POST 请求（initialize / tools/list / tools/call）
- `handleSse(): void` — 处理 SSE 流式 GET 请求，发送 ready 事件并保持 30 秒心跳
- `handleStdin(): void` — 处理 stdio 模式的 JSON-RPC 行请求

支持的方法：`initialize`、`tools/list`、`tools/call`。

### `PhpMcp\Http\Tool\ToolInterface`（src/Tool/ToolInterface.php）
所有工具必须实现的接口：
```php
interface ToolInterface
{
    public function name(): string;        // 工具名
    public function definition(): array;   // 工具描述/JSON Schema
    public function execute(array $args): string; // 执行并返回文本结果
}
```

### `PhpMcp\Http\Tool\TimeTool`（src/Tool/TimeTool.php）
示例工具：`get_current_time`，通过 `format` 参数指定 PHP 时间格式（默认 `Y-m-d H:i:s`）。

## 运行方式

### HTTP（SSE 流式）
```shell
PHP_CLI_SERVER_WORKERS=20 php -S 0.0.0.0:8087 router.php
curl -X POST http://localhost:8087/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```
入口：`index.php`。`router.php` 将 `/mcp` 请求转发到 `index.php`，其他路径返回 404。

### stdio
```shell
php ./server.php
{"jsonrpc":"2.0","id":1,"method":"tools/list"}
```
入口：`server.php`，复用 `Server::handleStdin()`。

## 添加新工具

1. 在 `src/Tool/` 目录新建类，实现 `PhpMcp\Http\Tool\ToolInterface`
2. 在 `index.php` 和 `server.php` 中实例化并加入 `new Server([...])` 的工具数组
3. 无需手动 `require`，命名空间类由 Composer 自动加载

## 时区

入口文件（`index.php`、`server.php`）通过 `date_default_timezone_set('Asia/Shanghai')` 设置默认时区为上海。

## 依赖与安装

- PHP >= 8.1
- Composer（用于生成 `vendor/autoload.php`）

```shell
composer dump-autoload
```

`composer.json` 无第三方运行时依赖，仅配置 PSR-4 自动加载。

## 验证命令

```shell
# 语法检查
php -l src/Server.php src/Tool/TimeTool.php src/Tool/ToolInterface.php index.php server.php router.php

# HTTP 功能测试
php -S 127.0.0.1:8099 router.php &
curl -X POST http://127.0.0.1:8099/mcp -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'

# stdio 功能测试
printf '%s\n' '{"jsonrpc":"2.0","id":1,"method":"initialize"}' | php server.php
```
