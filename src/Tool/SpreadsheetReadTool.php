<?php

namespace PhpMcp\Http\Tool;

class SpreadsheetReadTool extends AbstractSpreadsheetTool
{
    public function name(): string
    {
        return 'spreadsheet_read';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '读取 xlsx 文件的单元格数据',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'description' => 'xlsx 文件的绝对路径',
                    ],
                    'sheet_index' => [
                        'type' => 'integer',
                        'description' => '工作表索引，默认 0',
                        'default' => 0,
                    ],
                    'range' => [
                        'type' => 'string',
                        'description' => '读取范围，如 A1:M20，默认读取全部数据',
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
            return $this->readFile($args);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage(), 'file' => $file]);
        }
    }

    private function readFile(array $args): string
    {
        $file = $args['file'];
        $sheetIndex = $args['sheet_index'] ?? 0;
        $range = $args['range'] ?? null;

        $spreadsheet = $this->loadSpreadsheet($file);
        $sheet = $spreadsheet->getSheet($sheetIndex);

        if ($range) {
            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();
            $startCell = preg_replace('/[0-9]+/', '1', $range);
            $endCell = $range;
            if (!preg_match('/[A-Z]+[0-9]+/', $range)) {
                $endCell = $highestCol . $highestRow;
                $startCell = 'A1';
            }
            $data = $sheet->rangeToArray("{$startCell}:{$endCell}", null, true, true);
        } else {
            $data = $sheet->toArray(null, true, true, true);
        }

        $result = [
            'sheet_name' => $sheet->getTitle(),
            'highest_row' => $sheet->getHighestRow(),
            'highest_column' => $sheet->getHighestColumn(),
            'data' => $data,
        ];

        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
