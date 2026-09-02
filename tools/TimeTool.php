<?php

class TimeTool
{
    public function name(): string
    {
        return 'get_current_time';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '获取当前服务器时间',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'format' => [
                        'type' => 'string',
                        'description' => 'PHP 时间格式，如 Y-m-d H:i:s',
                        'default' => 'Y-m-d H:i:s',
                    ],
                ],
            ],
        ];
    }

    public function execute(array $args): string
    {
        $format = $args['format'] ?? 'Y-m-d H:i:s';
        return date($format);
    }
}
