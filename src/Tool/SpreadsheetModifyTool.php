<?php

namespace PhpMcp\Http\Tool;

class SpreadsheetModifyTool extends AbstractSpreadsheetTool
{
    public function name(): string
    {
        return 'spreadsheet_modify';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '修改 xlsx 文件中指定单元格的内容并保存',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'description' => '源 xlsx 文件的绝对路径',
                    ],
                    'cells' => [
                        'type' => 'array',
                        'description' => '要修改的单元格数据数组，每项包含 cell 和 value',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'cell' => [
                                    'type' => 'string',
                                    'description' => '单元格坐标，如 A1、C8',
                                ],
                                'value' => [
                                    'description' => '单元格的值（字符串/数字）',
                                ],
                            ],
                            'required' => ['cell'],
                        ],
                    ],
                    'output_file' => [
                        'type' => 'string',
                        'description' => '输出文件的绝对路径，默认覆盖原文件',
                    ],
                ],
                'required' => ['file', 'cells'],
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

        $cells = $args['cells'] ?? [];
        if (empty($cells)) {
            return json_encode(['error' => '缺少必填参数 cells']);
        }

        try {
            return $this->modifyFile($args);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage(), 'file' => $file]);
        }
    }

    private function modifyFile(array $args): string
    {
        $file = $args['file'];
        $outputFile = $args['output_file'] ?? $file;
        $cells = $args['cells'];

        $spreadsheet = $this->loadSpreadsheet($file, false);
        $sheet = $spreadsheet->getActiveSheet();

        $modified = [];
        foreach ($cells as $item) {
            $cell = $item['cell'] ?? null;
            $value = $item['value'] ?? null;
            if ($cell) {
                $sheet->setCellValue($cell, $value);
                $modified[] = "{$cell}={$value}";
            }
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputFile);

        return json_encode([
            'success' => true,
            'output_file' => $outputFile,
            'modified_cells' => $modified,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
