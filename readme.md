### php-mcp-http 简易php实现的mcp服务器

基于 Composer PSR-4 自动加载，命名空间 `PhpMcp\Http`，映射到 `src/` 目录。

```shell
# 安装依赖（生成自动加载）
composer dump-autoload
```

```shell
# 流式http
PHP_CLI_SERVER_WORKERS=20 php -S 0.0.0.0:8087 router.php
curl -X POST http://localhost:8087/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

```shell
# stdio
php ./server.php
{"jsonrpc":"2.0","id":1,"method":"tools/list"}
```

### 项目结构

```
src/
  Server.php            核心服务器类（PhpMcp\Http\Server）
  Tool/
    ToolInterface.php   工具接口
    TimeTool.php        示例工具
index.php               HTTP 入口
router.php              内置服务器路由
server.php              stdio 入口
composer.json           PSR-4 自动加载配置
```

### 添加新工具

实现 `PhpMcp\Http\Tool\ToolInterface` 接口（`name()`、`definition()`、`execute()`），并在 `index.php` / `server.php` 中注册即可。
