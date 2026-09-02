### php-tools简易php实现的mcp服务器

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
