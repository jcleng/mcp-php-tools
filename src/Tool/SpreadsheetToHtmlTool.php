<?php

namespace PhpMcp\Http\Tool;

class SpreadsheetToHtmlTool extends AbstractSpreadsheetTool
{
    public function name(): string
    {
        return 'spreadsheet_to_html';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '将 xlsx 文件转换为 HTML 文件',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'description' => '源 xlsx 文件的绝对路径',
                    ],
                    'output_file' => [
                        'type' => 'string',
                        'description' => '输出 HTML 文件的绝对路径，默认与原文件同名 .html',
                    ],
                    'sheet_index' => [
                        'type' => 'integer',
                        'description' => '工作表索引，默认 0',
                        'default' => 0,
                    ],
                ],
                'required' => ['file'],
            ],
        ];
    }

    public function execute(array $args): string
    {
        $file = $this->requireFile($args);
        if ($file === null) {
            return json_encode(['error' => '缺少必填参数 file']);
        }

        $error = $this->checkFile($file);
        if ($error !== null) {
            return json_encode(['error' => $error]);
        }

        try {
            return $this->convertToHtml($args);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage(), 'file' => $file]);
        }
    }

    private function convertToHtml(array $args): string
    {
        $file = $args['file'];
        $outputFile = $args['output_file'] ?? preg_replace('/\.xlsx$/i', '.html', $file);
        $sheetIndex = $args['sheet_index'] ?? 0;

        $spreadsheet = $this->loadSpreadsheet($file);
        $html = $this->buildFullHtml($spreadsheet, (int) $sheetIndex);

        file_put_contents($outputFile, $html);

        return json_encode([
            'success' => true,
            'output_file' => $outputFile,
            'format' => 'html',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
