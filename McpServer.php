<?php

class McpServer
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
                        'tools' => new stdClass(),
                    ],
                    'serverInfo' => [
                        'name' => 'php-mcp-http',
                        'version' => '1.0.0',
                    ],
                ]);
                return;

            case 'tools/list':
                $list = array_map(fn($t) => $t->definition(), $this->tools);
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

        // 保持连接 30 秒心跳
        for ($i = 0; $i < 30; $i++) {
            echo "event: heartbeat\ndata: {\"ts\":" . time() . "}\n\n";
            flush();
            sleep(1);
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
}
