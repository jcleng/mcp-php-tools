<?php

namespace PhpMcp\Http\Tool;

use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SpreadsheetToPdfTool extends AbstractSpreadsheetTool
{
    public function name(): string
    {
        return 'spreadsheet_to_pdf';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '将 xlsx 文件转换为 PDF 文件',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'file' => [
                        'type' => 'string',
                        'description' => '源 xlsx 文件的绝对路径',
                    ],
                    'output_file' => [
                        'type' => 'string',
                        'description' => '输出 PDF 文件的绝对路径，默认与原文件同名 .pdf',
                    ],
                    'orientation' => [
                        'type' => 'string',
                        'description' => 'PDF 页面方向，portrait 或 landscape',
                        'enum' => ['portrait', 'landscape'],
                        'default' => 'landscape',
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
            return $this->convertToPdf($args);
        } catch (\Throwable $e) {
            return json_encode(['error' => $e->getMessage(), 'file' => $file]);
        }
    }

    private function convertToPdf(array $args): string
    {
        $file = $args['file'];
        $outputFile = $args['output_file'] ?? preg_replace('/\.xlsx$/i', '.pdf', $file);
        $orientation = $args['orientation'] ?? 'landscape';

        $spreadsheet = $this->loadSpreadsheet($file);

        $writer = new CnMpdfWriter($spreadsheet);
        $writer->setOrientation(
            $orientation === 'portrait'
                ? PageSetup::ORIENTATION_PORTRAIT
                : PageSetup::ORIENTATION_LANDSCAPE
        );
        $writer->save($outputFile);

        return json_encode([
            'success' => true,
            'output_file' => $outputFile,
            'format' => 'pdf',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
