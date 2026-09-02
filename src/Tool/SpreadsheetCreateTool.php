<?php

namespace PhpMcp\Http\Tool;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SpreadsheetCreateTool implements ToolInterface
{
    public function name(): string
    {
        return 'spreadsheet_create';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '创建空的 xlsx 文件',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'description' => '输出 xlsx 文件的绝对路径',
                    ],
                    'sheet_name' => [
                        'type' => 'string',
                        'description' => '工作表名称，默认 Sheet1',
                        'default' => 'Sheet1',
                    ],
                ],
                'required' => ['file'],
            ],
        ];
    }

    public function execute(array $args): string
    {
        $file = $args['file'] ?? '';
        if (empty($file)) {
            return json_encode(['error' => '缺少必填参数 file']);
        }

        $sheetName = $args['sheet_name'] ?? 'Sheet1';

        try {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->setTitle($sheetName);

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($file);

            return json_encode([
                'success' => true,
                'file' => $file,
                'sheet_name' => $sheetName,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage(), 'file' => $file]);
        }
    }
}
