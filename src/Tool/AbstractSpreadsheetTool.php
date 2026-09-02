<?php

namespace PhpMcp\Http\Tool;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class AbstractSpreadsheetTool implements ToolInterface
{
    protected function loadSpreadsheet(string $file, bool $readDataOnly = false): Spreadsheet
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly($readDataOnly);
        return $reader->load($file);
    }

    protected function requireFile(array $args): ?string
    {
        $file = $args['file'] ?? '';
        if (empty($file)) {
            return null;
        }
        return $file;
    }

    protected function checkFile(string $file): ?string
    {
        if (!file_exists($file)) {
            return "文件不存在: {$file}";
        }
        return null;
    }

    protected function spreadsheetToHtmlBody(Spreadsheet $spreadsheet): string
    {
        $htmlWriter = IOFactory::createWriter($spreadsheet, 'Html');
        $htmlWriter->setSheetIndex(0);

        ob_start();
        $htmlWriter->save('php://output');
        return ob_get_clean();
    }

    protected function buildFullHtml(Spreadsheet $spreadsheet): string
    {
        $body = $this->spreadsheetToHtmlBody($spreadsheet);

        $header = '<!DOCTYPE html><html><head><meta charset="utf-8">'
            . '<style>'
            . 'body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }'
            . 'table { border-collapse: collapse; width: 100%; }'
            . 'td, th { border: 1px solid #000; padding: 4px 6px; text-align: left; }'
            . '</style></head><body>';

        return $header . $body . '</body></html>';
    }
}
