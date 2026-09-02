<?php

namespace PhpMcp\Http;

use PhpMcp\Http\Tool\ToolInterface;

class Server
{
    private array $tools;

    public function __construct(array $tools)
    {
        $this->tools = $tools;
    }

    public function handlePost(): void
    {
        $raw = file_get_contents('php://input');
        $request = json_decode($raw, true);

        if (!$request || !isset($request['method'])) {
            $this->jsonError(null, 'Invalid request');
            return;
        }

        $method = $request['method'];
        $id = $request['id'] ?? null;

        switch ($method) {
            case 'initialize':
                $this->jsonResponse($id, [
                    'protocolVersion' => '2024-11-05',
                    'capabilities' => [
                        'tools' => new \stdClass(),
                    ],
                    'serverInfo' => [
                        'name' => 'php-mcp-http',
                        'version' => '1.0.0',
                    ],
                ]);
                return;

            case 'tools/list':
                $list = array_map(fn(ToolInterface $t) => $t->definition(), $this->tools);
                $this->jsonResponse($id, ['tools' => $list]);
                return;

            case 'tools/call':
                $name = $request['params']['name'] ?? '';
                $args = $request['params']['arguments'] ?? [];

                foreach ($this->tools as $tool) {
                    if ($tool->name() === $name) {
                        $result = $tool->execute($args);
                        $this->jsonResponse($id, [
                            'content' => [
                                ['type' => 'text', 'text' => $result],
                            ],
                        ]);
                        return;
                    }
                }

                $this->jsonError($id, "Tool '{$name}' not found");
                return;

            default:
                $this->jsonError($id, "Method '{$method}' not supported");
        }
    }

    public function handleSse(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');

        echo "event: ready\ndata: {\"status\":\"connected\"}\n\n";
        flush();

        for ($i = 0; $i < 30; $i++) {
            echo "event: heartbeat\ndata: {\"ts\":" . time() . "}\n\n";
            flush();
            sleep(1);
        }
    }

    public function handleStdin(): void
    {
        $in = fopen('php://stdin', 'r');
        while (true) {
            $line = fgets($in);
            if ($line === false) {
                break;
            }

            $request = json_decode($line, true);
            if (!$request) {
                continue;
            }

            $method = $request['method'] ?? '';
            $id = $request['id'] ?? null;

            if ($method === 'initialize') {
                $this->stdioRespond($id, [
                    'protocolVersion' => '2024-11-05',
                    'capabilities' => [
                        'tools' => new \stdClass(),
                    ],
                    'serverInfo' => [
                        'name' => 'php-mcp-server',
                        'version' => '1.0.0',
                    ],
                ]);
            }

            if ($method === 'tools/list') {
                $toolList = array_map(fn(ToolInterface $t) => $t->definition(), $this->tools);
                $this->stdioRespond($id, ['tools' => $toolList]);
            }

            if ($method === 'tools/call') {
                $toolName = $request['params']['name'] ?? '';
                $args = $request['params']['arguments'] ?? [];

                foreach ($this->tools as $tool) {
                    if ($tool->name() === $toolName) {
                        $result = $tool->execute($args);
                        $this->stdioRespond($id, [
                            'content' => [
                                ['type' => 'text', 'text' => $result],
                            ],
                        ]);
                        continue 2;
                    }
                }

                $this->stdioRespond($id, ['error' => 'Tool not found']);
            }
        }
    }

    private function jsonResponse($id, $result): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function jsonError($id, string $msg): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => -32600,
                'message' => $msg,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    private function stdioRespond($id, $result): void
    {
        $resp = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
        fwrite(STDOUT, json_encode($resp) . "\n");
    }
}
