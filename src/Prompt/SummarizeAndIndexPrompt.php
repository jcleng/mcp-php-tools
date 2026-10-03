<?php

namespace PhpMcp\Http\Prompt;

class SummarizeAndIndexPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'local_rag_summarize_and_index';
    }

    public function description(): ?string
    {
        return '汇总之前的分析与思考，生成 Markdown 文件（第一行必须写明当前项目目录，因知识库检索需按项目目录匹配）保存到 ARG_BASE_DIR，并调用 mcp-local-rag 更新知识库为最新';
    }

    public function arguments(): array
    {
        return [
            [
                'name' => 'title',
                'description' => '汇总文件的标题/主题，用于生成文件名与标题',
                'required' => false,
            ],
            [
                'name' => 'project_dir',
                'description' => '项目目录路径，写入文件第一行说明；不传则使用当前工作目录',
                'required' => false,
            ],
        ];
    }

    public function getMessages(array $arguments = []): array
    {
        $title = $arguments['title'] ?? 'summary';
        $projectDir = $arguments['project_dir'] ?? getcwd();
        $envDir = getenv('ARG_BASE_DIR');

        if ($envDir === false || $envDir === '') {
            $envDir = '${ARG_BASE_DIR}';
        }

        $safeName = preg_replace('/[^\w\-]+/u', '_', $title);
        if ($safeName === '' || $safeName === null) {
            $safeName = 'summary';
        }
        $filename = $safeName . '_' . date('Ymd_His') . '.md';
        $filePath = rtrim($envDir, '/') . '/' . $filename;

        $instruction = <<<EOT
请基于之前的分析与思考，编写一份汇总 Markdown 文件，并按以下要求执行：

1. 文件第一行必须写明当前项目目录（知识库后续检索需按项目目录匹配，请务必准确填写，不要省略），格式为：
   `# 项目目录: {$projectDir}`

2. 紧接着说明本次汇总的详情/背景（标题：{$title}）。

3. 然后汇总之前的分析和思考，包含：
   - 关键结论
   - 分析过程与推理思路
   - 待办或后续建议

4. 将文件保存到环境变量 ARG_BASE_DIR 指定的目录下，完整路径为：
   `{$filePath}`
   （ARG_BASE_DIR 当前值为：{$envDir}）

5. 文件保存完成后，调用 MCP 工具 `mcp-local-rag` 更新知识库为最新，确保刚才写入的汇总文件已被纳入检索范围，且知识库能通过项目目录匹配到本文档。

请先生成并写入文件，再调用 `mcp-local-rag` 完成知识库更新。
EOT;

        return [
            [
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => $instruction,
                ],
            ],
        ];
    }
}
