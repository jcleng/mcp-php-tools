<?php

namespace PhpMcp\Http\Prompt;

class SummarizeUsePrompt implements PromptInterface
{
    public function name(): string
    {
        return 'local_rag_summarize_use';
    }

    public function description(): ?string
    {
        return '先检索当前项目/目录的知识库，读取相关已有内容，再结合实际情况产出关联的分析与汇总';
    }

    public function arguments(): array
    {
        return [];
    }

    public function getMessages(array $arguments = []): array
    {
        $instruction = <<<EOT
请按以下顺序执行：

1. 先用 MCP 工具 `mcp-local-rag` 检索当前项目（当前目录）的知识库，把范围限定在当前项目/目录，避免检索到其他项目的内容。

2. 如果知识库中检索到了与本次任务相关的已有内容，请先把这些相关内容读取并理解清楚。

3. 再结合当前实际情况，把知识库里匹配得上、真正关联的内容挑出来，作为依据来产出你的分析与汇总，不要凭空编造与知识库冲突的信息。

4. 最终基于“知识库相关内容 + 实际关联内容”给出结论与说明。

注意：先检索、先读已有知识库，再与实际关联内容匹配，这是必须的顺序。
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
