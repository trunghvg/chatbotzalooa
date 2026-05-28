<?php

namespace App\Libraries;

use App\Models\KnowledgeModel;

/**
 * Thu vien import du lieu tu file Excel (.xlsx) vao Knowledge Base
 *
 * Ho tro cau truc Excel 2 cot:
 *   - Cot A: Tieu de / Truong
 *   - Cot B: Noi dung / Gia tri
 *
 * Dong 1: Ten sheet (bo qua)
 * Dong 2+: Cac dong du lieu
 *
 * Hoac cau truc tu do - tu dong phan tich theo noi dung
 */
class ExcelKnowledgeImporter
{
    private KnowledgeModel $model;

    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public function __construct()
    {
        $this->model = new KnowledgeModel();
    }

    /**
     * Import tu file Excel, tra ve so muc da import
     */
    public function import(string $filePath): int
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: $filePath");
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Cannot open Excel file.");
        }

        // Doc shared strings neu co
        $shared = $this->readSharedStrings($zip);

        // Doc workbook de lay ten sheets
        $wbXml     = $zip->getFromName('xl/workbook.xml');
        $wb        = simplexml_load_string($wbXml);
        $wb->registerXPathNamespace('x', self::NS);
        $sheetNodes = $wb->xpath('//x:sheet');
        $sheetNames = array_map(fn($s) => (string) $s['name'], $sheetNodes);

        $zip->close();

        $totalImported = 0;
        $now           = date('Y-m-d H:i:s');

        foreach ($sheetNames as $idx => $sheetName) {
            $sheetNum = $idx + 1;
            $rows     = $this->readSheet($filePath, $sheetNum, $shared);

            if (empty($rows)) continue;

            // Chuyen ten sheet thanh category slug
            $category = $this->slugify($sheetName);

            // Xu ly tung nhom du lieu trong sheet
            $imported = $this->processSheet($rows, $sheetName, $category, $now);
            $totalImported += $imported;
        }

        return $totalImported;
    }

    /**
     * Xu ly noi dung mot sheet
     */
    private function processSheet(array $rows, string $sheetName, string $category, string $now): int
    {
        // Hang dau tien thuong la tieu de sheet - bo qua neu giong tieu de
        $count        = 0;
        $currentTitle = null;
        $currentLines = [];

        foreach ($rows as $rowIdx => $row) {
            $cellA = trim($row[0] ?? '');
            $cellB = trim($row[1] ?? '');

            // Bo qua dong trong
            if (empty($cellA) && empty($cellB)) continue;

            // Bo qua hang header/title cua sheet (dong dau)
            if ($rowIdx === 0) {
                // Dong 0 la tieu de sheet - luu lam title neu co data
                if (!empty($cellA) && empty($cellB)) {
                    // Day la tieu de section moi
                    if ($currentTitle && !empty($currentLines)) {
                        $this->saveEntry($currentTitle, implode("\n", $currentLines), $category, $sheetName, $count * 10, $now);
                        $count++;
                    }
                    $currentTitle = $cellA;
                    $currentLines = [];
                }
                continue;
            }

            // Pattern: Co 2 cot A va B → day la du lieu dang bang
            if (!empty($cellA) && !empty($cellB)) {
                // Luu muc truoc do
                if ($currentTitle && !empty($currentLines)) {
                    $this->saveEntry($currentTitle, implode("\n", $currentLines), $category, $sheetName, $count * 10, $now);
                    $count++;
                    $currentLines = [];
                }

                // Tao muc moi tu cap A-B
                $currentTitle = $cellA;
                $currentLines = [$cellB];
            } elseif (!empty($cellA) && empty($cellB)) {
                // Chi co cot A → co the la tieu de muc moi hoac tiep tuc
                if ($currentTitle && !empty($currentLines)) {
                    $this->saveEntry($currentTitle, implode("\n", $currentLines), $category, $sheetName, $count * 10, $now);
                    $count++;
                }
                $currentTitle = $cellA;
                $currentLines = [];
            } elseif (empty($cellA) && !empty($cellB)) {
                // Chi co cot B → tiep tuc noi dung muc hien tai
                $currentLines[] = $cellB;
            }
        }

        // Luu muc cuoi
        if ($currentTitle && !empty($currentLines)) {
            $this->saveEntry($currentTitle, implode("\n", $currentLines), $category, $sheetName, $count * 10, $now);
            $count++;
        }

        return $count;
    }

    private function saveEntry(
        string $title, string $content, string $category,
        string $source, int $sortOrder, string $now
    ): void {
        // Tao keywords tu title + 100 ky tu dau cua content
        $keywords = mb_strtolower($title . ' ' . mb_substr($content, 0, 100));
        $keywords = preg_replace('/[^\p{L}\p{N}\s,]/u', ' ', $keywords);
        $keywords = preg_replace('/\s+/', ' ', trim($keywords));

        $this->model->insert([
            'title'           => mb_substr($title, 0, 300),
            'category'        => $category,
            'source_document' => mb_substr($source, 0, 200),
            'content'         => $content,
            'keywords'        => mb_substr($keywords, 0, 1000),
            'is_active'       => 1,
            'sort_order'      => $sortOrder,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }

    /**
     * Doc tat ca rows cua mot sheet
     */
    private function readSheet(string $filePath, int $sheetNum, array $shared): array
    {
        $zip = new \ZipArchive();
        $zip->open($filePath);

        $sheetContent = $zip->getFromName("xl/worksheets/sheet{$sheetNum}.xml");
        $zip->close();

        if (!$sheetContent) return [];

        $xml  = simplexml_load_string($sheetContent);
        $xml->registerXPathNamespace('x', self::NS);

        $rowNodes = $xml->xpath('//x:row');
        $rows     = [];

        foreach ($rowNodes as $rowNode) {
            $rowData = [];
            foreach ($rowNode->children(self::NS) as $cell) {
                $colLetter = preg_replace('/[0-9]/', '', (string) $cell['r']);
                $colIdx    = $this->colToIndex($colLetter);
                $value     = $this->getCellValue($cell, $shared);

                // Ensure array is large enough
                while (count($rowData) <= $colIdx) {
                    $rowData[] = '';
                }
                $rowData[$colIdx] = $value;
            }
            $rows[] = $rowData;
        }

        return $rows;
    }

    private function getCellValue(\SimpleXMLElement $cell, array $shared): string
    {
        $type = (string) ($cell['t'] ?? '');

        // Inline string
        $is = $cell->children(self::NS)->is ?? null;
        if ($is !== null) {
            $text = '';
            foreach ($is->children(self::NS) as $t) {
                $text .= (string) $t;
            }
            return $text;
        }

        // Shared string
        $v = $cell->children(self::NS)->v ?? null;
        if ($v === null) return '';

        $val = (string) $v;

        if ($type === 's' && isset($shared[(int) $val])) {
            return $shared[(int) $val];
        }

        return $val;
    }

    private function readSharedStrings(\ZipArchive $zip): array
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if (!$content) return [];

        $xml    = simplexml_load_string($content);
        $shared = [];

        foreach ($xml->children(self::NS) as $si) {
            $text = '';
            foreach ($si->children(self::NS) as $t) {
                $text .= (string) $t;
            }
            $shared[] = $text;
        }

        return $shared;
    }

    private function colToIndex(string $col): int
    {
        $col   = strtoupper($col);
        $index = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        return mb_substr($text, 0, 50);
    }
}
