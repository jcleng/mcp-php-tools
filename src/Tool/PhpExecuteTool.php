<?php

namespace PhpMcp\Http\Tool;

class PhpExecuteTool implements ToolInterface
{
    public function name(): string
    {
        return 'php_execute';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '执行PHP代码或脚本文件',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'string',
                        'description' => '要执行的PHP代码字符串',
                    ],
                    'file' => [
                        'type' => 'string',
                        'description' => '要执行的PHP文件的绝对路径',
                    ],
                    'timeout' => [
                        'type' => 'integer',
                        'description' => '执行超时时间（秒），默认30秒',
                        'default' => 30,
                    ],
                ],
            ],
        ];
    }

    public function execute(array $args): string
    {
        $code = $args['code'] ?? null;
        $file = $args['file'] ?? null;
        $timeout = $args['timeout'] ?? 30;

        if (empty($code) && empty($file)) {
            return json_encode(['error' => '必须提供 code 或 file 参数']);
        }

        if (!empty($file)) {
            if (!file_exists($file)) {
                return json_encode(['error' => "文件不存在: {$file}"]);
            }
            if (!is_readable($file)) {
                return json_encode(['error' => "文件不可读: {$file}"]);
            }
        }

        $output = [];
        $exitCode = 0;

        $tempFile = null;
        try {
            if (!empty($file)) {
                $command = sprintf('php -d max_execution_time=%d %s 2>&1', $timeout, escapeshellarg($file));
            } else {
                $tempFile = tempnam(sys_get_temp_dir(), 'php_exec_') . '.php';
                file_put_contents($tempFile, '<?php ' . $code);
                $command = sprintf('php -d max_execution_time=%d %s 2>&1', $timeout, escapeshellarg($tempFile));
            }

            exec($command, $output, $exitCode);

            if (!empty($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return json_encode([
                'exit_code' => $exitCode,
                'output' => implode("\n", $output),
            ]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}