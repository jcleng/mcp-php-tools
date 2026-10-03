<?php

namespace PhpMcp\Http\Prompt;

class UseProxyPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'use_proxy';
    }

    public function description(): ?string
    {
        return '当遇到网络错误时使用代理';
    }

    public function arguments(): array
    {
        return [];
    }

    public function getMessages(array $arguments = []): array
    {
        return [
            [
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => '如果网络错误可以使用代理:https_proxy=http://127.0.0.1:20171',
                ],
            ],
        ];
    }
}
