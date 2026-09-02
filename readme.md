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

```shell
# Docker
docker build -t php-mcp-http .
docker run -p 8087:8087 php-mcp-http
```

### 内置 MCP 工具

| 工具名称 | 功能说明 | 必填参数 | 可选参数 |
| --- | --- | --- | --- |
| `get_current_time` | 获取当前服务器时间 | — | `format`：PHP 时间格式，默认 `Y-m-d H:i:s` |
| `spreadsheet_read` | 读取 xlsx 文件的单元格数据 | `file`：xlsx 文件绝对路径 | `sheet_index`：工作表索引（默认 0）；`range`：读取范围，如 `A1:M20` |
| `spreadsheet_modify` | 修改 xlsx 文件中指定单元格的内容并保存 | `file`：源 xlsx 文件绝对路径；`cells`：要修改的单元格数组（含 `cell` 和 `value`） | `output_file`：输出文件路径，默认覆盖原文件 |
| `spreadsheet_to_pdf` | 将 xlsx 文件转换为 PDF 文件（支持中文） | `file`：源 xlsx 文件绝对路径 | `output_file`：输出 PDF 路径；`orientation`：页面方向 `portrait`/`landscape`（默认 `landscape`） |
| `spreadsheet_to_html` | 将 xlsx 文件转换为 HTML 文件 | `file`：源 xlsx 文件绝对路径 | `output_file`：输出 HTML 路径 |
| `php_execute` | 执行 PHP 代码或脚本文件 | —（`code` 或 `file` 至少填一个） | `code`：要执行的 PHP 代码字符串；`file`：要执行的 PHP 文件绝对路径；`timeout`：执行超时秒数（默认 30） |

### 项目结构

```
src/
  Server.php            核心服务器类（PhpMcp\Http\Server）
  Tool/
    ToolInterface.php           工具接口
    AbstractSpreadsheetTool.php Excel 工具基类（共享加载/HTML 逻辑）
    TimeTool.php                示例工具（获取当前时间）
    SpreadsheetReadTool.php     读取 xlsx
    SpreadsheetModifyTool.php   修改 xlsx 单元格
    SpreadsheetToPdfTool.php    xlsx → PDF
    CnMpdfWriter.php            PDF 中文字体写入器（msyh.ttf）
    SpreadsheetToHtmlTool.php   xlsx → HTML
    PhpExecuteTool.php          执行 PHP 代码（php_execute）
index.php               HTTP 入口
router.php              内置服务器路由
server.php              stdio 入口
Dockerfile              Docker 构建文件
fonts/                  PDF 中文字体目录（msyh.ttf）
composer.json           PSR-4 自动加载配置
```

### 添加新工具

实现 `PhpMcp\Http\Tool\ToolInterface` 接口（`name()`、`definition()`、`execute()`），并在 `index.php` / `server.php` 中注册即可。
