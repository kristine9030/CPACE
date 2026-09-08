<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds a downloadable "fill this in" sample file for each import format, so
 * faculty have something to follow instead of guessing at the expected shape.
 * The content is the same 3 sample questions in every format — only the
 * container differs. Keep this in sync with QuestionImportParser's rules
 * (numbered stem, "A./B./C./D." choices, "Answer:"/"Explanation:" lines for
 * documents; a Question/Choice A-D/Answer header row for spreadsheets).
 */
class QuestionImportTemplateGenerator
{
    public const TYPES = ['txt', 'csv', 'xlsx', 'docx', 'pdf'];

    /** @return array{0: string, 1: string, 2: string} [binary contents, filename, mime type] */
    public function build(string $type): array
    {
        return match ($type) {
            'txt'   => [$this->txt(), 'cpace-question-import-template.txt', 'text/plain'],
            'csv'   => [$this->csv(), 'cpace-question-import-template.csv', 'text/csv'],
            'xlsx'  => [$this->xlsx(), 'cpace-question-import-template.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'docx'  => [$this->docx(), 'cpace-question-import-template.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'pdf'   => [$this->pdf(), 'cpace-question-import-template.pdf', 'application/pdf'],
            default => throw new \InvalidArgumentException("Unknown template type: {$type}"),
        };
    }

    /** The 3 sample questions, as plain data, reused by every format. */
    private function sampleQuestions(): array
    {
        return [
            [
                'text' => "Which financial statement shows a company's financial position at a point in time?",
                'choices' => ['A' => 'Income Statement', 'B' => 'Statement of Cash Flows', 'C' => 'Balance Sheet', 'D' => 'Statement of Changes in Equity'],
                'answer' => 'C',
                'explanation' => "The Balance Sheet reports assets, liabilities, and equity as of a specific date.",
                'difficulty' => 'Easy',
            ],
            [
                'text' => "Using FIFO during a period of rising prices, which statement is TRUE?",
                'choices' => ['A' => 'Ending inventory is understated', 'B' => 'Cost of goods sold is higher than under LIFO', 'C' => 'Ending inventory reflects the most recent (higher) costs', 'D' => 'Net income is lower than under LIFO'],
                'answer' => 'C',
                'explanation' => "FIFO leaves the most recently purchased (higher-cost) units in ending inventory, which also makes reported net income higher than under LIFO.",
                'difficulty' => 'Medium',
            ],
            [
                'text' => 'Cash is classified as a current asset.',
                'choices' => ['A' => 'True', 'B' => 'False'],
                'answer' => 'A',
                'explanation' => 'Cash is always current since it is already in its most liquid form.',
                'difficulty' => 'Easy',
                'type' => 'True / False',
            ],
        ];
    }

    private function txt(): string
    {
        $lines = ["Replace these 3 sample questions with your own — keep the same numbering, choice letters, and \"Answer:\" style.\n"];

        foreach ($this->sampleQuestions() as $i => $q) {
            $n = $i + 1;
            $lines[] = "{$n}. {$q['text']}";
            foreach ($q['choices'] as $label => $text) {
                $lines[] = "{$label}. {$text}";
            }
            $lines[] = "Answer: {$q['answer']}";
            $lines[] = "Explanation: {$q['explanation']}";
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function csv(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Question', 'Choice A', 'Choice B', 'Choice C', 'Choice D', 'Answer', 'Explanation', 'Difficulty']);

        foreach ($this->sampleQuestions() as $q) {
            $c = $q['choices'];
            fputcsv($handle, [
                $q['text'], $c['A'] ?? '', $c['B'] ?? '', $c['C'] ?? '', $c['D'] ?? '',
                $q['answer'], $q['explanation'], $q['difficulty'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv; // UTF-8 BOM so Excel opens it cleanly.
    }

    private function xlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions');

        $headers = ['Question', 'Choice A', 'Choice B', 'Choice C', 'Choice D', 'Answer', 'Explanation', 'Difficulty'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($this->sampleQuestions() as $q) {
            $c = $q['choices'];
            $sheet->fromArray([
                $q['text'], $c['A'] ?? '', $c['B'] ?? '', $c['C'] ?? '', $c['D'] ?? '',
                $q['answer'], $q['explanation'], $q['difficulty'],
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(24);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    private function docx(): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText(
            'Replace these 3 sample questions with your own — keep the same numbering, choice letters, and "Answer:" style.',
            ['italic' => true, 'color' => '888888', 'size' => 10]
        );
        $section->addTextBreak();

        foreach ($this->sampleQuestions() as $i => $q) {
            $n = $i + 1;
            $section->addText("{$n}. {$q['text']}", ['bold' => true, 'size' => 12]);
            foreach ($q['choices'] as $label => $text) {
                $section->addText("{$label}. {$text}", ['size' => 11]);
            }
            $section->addText("Answer: {$q['answer']}", ['bold' => true, 'color' => '059669', 'size' => 11]);
            $section->addText("Explanation: {$q['explanation']}", ['italic' => true, 'size' => 10, 'color' => '555555']);
            $section->addTextBreak();
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'cpace_tpl_') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);
        $contents = file_get_contents($tempPath);
        unlink($tempPath);

        return $contents;
    }

    private function pdf(): string
    {
        $rows = '';
        foreach ($this->sampleQuestions() as $i => $q) {
            $n = $i + 1;
            $choiceLines = '';
            foreach ($q['choices'] as $label => $text) {
                $choiceLines .= "<div class='choice'>{$label}. " . e($text) . '</div>';
            }
            $rows .= "<div class='q'>
                <div class='stem'>{$n}. " . e($q['text']) . "</div>
                {$choiceLines}
                <div class='answer'>Answer: {$q['answer']}</div>
                <div class='expl'>Explanation: " . e($q['explanation']) . "</div>
            </div>";
        }

        $html = <<<HTML
        <html>
        <head>
        <style>
            body { font-family: sans-serif; color:#333; font-size:12px; }
            h1 { color:#7B1D1D; font-size:16px; margin-bottom:4px; }
            .note { color:#888; font-style:italic; font-size:10px; margin-bottom:16px; }
            .q { border:1px solid #e6e6e6; border-radius:6px; padding:10px 14px; margin-bottom:12px; }
            .stem { font-weight:bold; margin-bottom:6px; }
            .choice { padding:2px 0 2px 12px; }
            .answer { color:#059669; font-weight:bold; margin-top:6px; }
            .expl { color:#666; font-style:italic; margin-top:4px; }
        </style>
        </head>
        <body>
            <h1>CPACE Question Import Template</h1>
            <div class="note">Replace these 3 sample questions with your own — keep the same numbering, choice letters, and "Answer:" style.</div>
            {$rows}
        </body>
        </html>
        HTML;

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
