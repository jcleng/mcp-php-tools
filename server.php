#!/usr/bin/env php
<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/tools/TimeTool.php';

$tools = [
    new TimeTool(),
];

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
        respond($id, [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => new stdClass()
            ],
            'serverInfo' => [
                'name' => 'php-mcp-server',
                'version' => '1.0.0'
            ]
        ]);
    }

    if ($method === 'tools/list') {
        $toolList = array_map(fn($t) => $t->definition(), $tools);
        respond($id, ['tools' => $toolList]);
    }

    if ($method === 'tools/call') {
        $toolName = $request['params']['name'] ?? '';
        $args = $request['params']['arguments'] ?? [];

        foreach ($tools as $tool) {
            if ($tool->name() === $toolName) {
                $result = $tool->execute($args);
                respond($id, [
                    'content' => [
                        ['type' => 'text', 'text' => $result]
                    ]
                ]);
                continue 2;
            }
        }

        respond($id, ['error' => 'Tool not found']);
    }
}

function respond($id, $result)
{
    $resp = [
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => $result
    ];
    fwrite(STDOUT, json_encode($resp) . "\n");
}
