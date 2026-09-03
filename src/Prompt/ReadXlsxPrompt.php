<?php

namespace PhpMcp\Http\Prompt;

class ReadXlsxPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'read_xlsx';
    }

    public function description(): ?string
    {
        return '读取 xlsx 文件内容';
    }

    public function arguments(): array
    {
        return [
            [
                'name' => 'file',
                'description' => '要读取的 xlsx 文件路径',
                'required' => true,
            ],
        ];
    }

    public function getMessages(array $arguments = []): array
    {
        return [
            [
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => '使用mcp工具spreadsheet_read读取文件内容',
                ],
            ],
        ];
    }
}
