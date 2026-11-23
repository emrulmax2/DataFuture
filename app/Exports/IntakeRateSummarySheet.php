<?php

namespace App\Exports;

use App\Services\Reports\IntakeRateReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * "Report" sheet: the PDF's content in cells, in the same order and colours,
 * for one or more intakes (IntakeRateReport::buildMany())
 * (teal #0e5a54, orange #c8601a / #8a3d0c for drop-outs and low rates, grey
 * #55616b for notes). Written cell by cell in AfterSheet.
 */
class IntakeRateSummarySheet implements FromArray, WithTitle, WithEvents
{
    const INK = '16242B';
    const GREY = '55616B';
    const TEAL = '0E5A54';
    const ORANGE = 'C8601A';
    const RUST = '8A3D0C';
    const RULE = 'E3E7EA';
    const SHADE = 'E6ECEF';

    public function __construct(private array $report, private array $copy, private string $author, private string $created)
    {
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Report';
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->write($event->sheet->getDelegate());
        }];
    }

    protected function write(Worksheet $s): void
    {
        $t = $this->report['totals'];
        $s->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10)->getColor()->setRGB(self::INK);
        foreach (['A' => 52, 'B' => 40, 'C' => 15, 'D' => 13, 'E' => 13, 'F' => 13, 'G' => 17] as $col => $width) {
            $s->getColumnDimension($col)->setWidth($width);
        }
        $s->setShowGridlines(false);

        // Header (as the PDF's running header).
        $s->setCellValue('A1', 'LONDON CHURCHILL COLLEGE');
        $this->font($s, 'A1', self::GREY, true, 9);
        $s->setCellValue('A2', $this->copy['title'].' Report');
        $this->font($s, 'A2', self::INK, true, 14);
        // Right-hand header over E:G so the creator line never wraps.
        $s->setCellValue('E1', $this->report['intake']);
        $this->font($s, 'E1', self::TEAL, true);
        $s->setCellValue('E2', 'Created by '.$this->author.' · '.$this->created);
        $this->font($s, 'E2', self::GREY);
        $s->mergeCells('E1:G1');
        $s->mergeCells('E2:G2');
        $s->getStyle('E1:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $s->getStyle('A2:G2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::INK);
        $s->getRowDimension(2)->setRowHeight(22);

        // Headline rate.
        $s->setCellValue('A4', strtoupper($this->copy['rate'].(!empty($this->report['many']) ? ' · All intakes' : '')));
        $this->font($s, 'A4', self::GREY, true, 9);
        $row = 5;
        if ($this->report['has_due'] && $t['rate'] !== null) {
            $s->setCellValue('A5', $t['rate'] / 100);
            $s->getStyle('A5')->getNumberFormat()->setFormatCode('0.0%');
            $s->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $this->font($s, 'A5', self::INK, true, 26);
            $s->getRowDimension(5)->setRowHeight(36);
            $s->setCellValue('A6', strtr($this->copy['sentence'], [':kept' => $t['kept'], ':registered' => $t['registered'], ':dropped' => $t['dropped_out']]));
            $row = 8;

            // What happened in each intake, then the outcomes over all of them.
            $s->setCellValue('A'.$row, 'What happened to the '.$t['intake'].' students'.(!empty($this->report['many']) ? ' in each intake' : ''));
            $this->font($s, 'A'.$row, self::INK, true, 12);
            $row++;
            $this->header($s, $row, ['A' => 'Intake', 'B' => 'Note', 'C' => 'Students', 'D' => $this->copy['kept'], 'E' => 'Dropped out', 'F' => 'Never registered', 'G' => $this->copy['rate']]);
            foreach ($this->report['intakes'] as $intake) {
                $it = $intake['totals'];
                $row++;
                $s->setCellValue('A'.$row, $intake['intake']);
                $this->font($s, 'A'.$row, self::INK, true);
                if (!$intake['has_due']) {
                    $s->setCellValue('B'.$row, !empty($intake['next_due']) ? 'Not due until '.date('j M Y', strtotime($intake['next_due'])) : 'No courses with students');
                    $this->font($s, 'B'.$row, self::GREY);
                } else {
                    $s->fromArray([$it['intake'], $it['kept'], $it['dropped_out'], $it['never_registered']], null, 'C'.$row, true);
                    $this->rateCell($s, 'G'.$row, $it['rate'], false, true);
                }
                $this->rule($s, 'A'.$row.':G'.$row);
            }
            $row += 2;
            $this->header($s, $row, ['A' => 'Outcome', 'B' => '', 'C' => 'Students', 'D' => 'Share']);
            foreach ([
                [$this->copy['kept'], $t['kept'], $this->copy['kept_note'], self::TEAL],
                ['Dropped out', $t['dropped_out'], $this->copy['dropped_note'], self::ORANGE],
                ['Never registered', $t['never_registered'], 'Not counted in the rate', 'A3ACB6'],
            ] as [$label, $count, $note, $colour]) {
                $row++;
                $s->setCellValue('A'.$row, $label);
                $this->font($s, 'A'.$row, $colour === 'A3ACB6' ? self::GREY : $colour, true);
                $s->getStyle('A'.$row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB($colour);
                $s->setCellValue('B'.$row, $note);
                $this->font($s, 'B'.$row, self::GREY);
                $s->setCellValue('C'.$row, $count);
                $s->setCellValue('D'.$row, ($t['intake'] > 0 ? $count / $t['intake'] : 0));
                $s->getStyle('D'.$row)->getNumberFormat()->setFormatCode('0.0%');
                $this->rule($s, 'A'.$row.':D'.$row);
            }
            $row += 2;
        } else {
            $s->setCellValue('A5', $this->report['has_due'] ? 'No rate' : (!empty($this->report['next_due']) ? 'Not due yet' : 'No data'));
            $this->font($s, 'A5', self::INK, true, 20);
            $s->setCellValue('A6', !empty($this->report['next_due']) && !$this->report['has_due']
                ? $this->copy['not_due'].' '.date('j M Y', strtotime($this->report['next_due'])).'.'
                : 'No registered students to work a rate out from.');
            $row = 8;
        }

        // By intake and course: each intake (shaded) above its courses.
        if (!empty($this->report['has_courses'])) {
            $s->setCellValue('A'.$row, 'By intake and course');
            $this->font($s, 'A'.$row, self::INK, true, 12);
            $s->setCellValue('G'.$row, 'Lowest rate first within each intake');
            $this->font($s, 'G'.$row, self::GREY);
            $s->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
            $this->header($s, $row, ['A' => 'Intake and course', 'B' => 'Note', 'C' => $this->copy['rate'], 'D' => 'Registered', 'E' => $this->copy['kept'], 'F' => 'Dropped out', 'G' => 'Never registered']);
            $first = $row + 1;
            foreach ($this->report['intakes'] as $intake) {
                $it = $intake['totals'];
                $row++;
                $s->setCellValue('A'.$row, $intake['intake']);
                $s->getStyle('A'.$row.':G'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::SHADE);
                $s->getStyle('A'.$row.':G'.$row)->getFont()->setBold(true);
                if (empty($intake['courses']) || !$intake['has_due']) {
                    $s->setCellValue('B'.$row, empty($intake['courses']) ? 'No courses with students' : 'Not due yet');
                    $this->font($s, 'B'.$row, self::GREY, true);
                } else {
                    $this->rateCell($s, 'C'.$row, $it['rate'], false, true);
                    $s->fromArray([$it['registered'], $it['kept'], $it['dropped_out'], $it['never_registered']], null, 'D'.$row, true);
                }
                $s->getStyle('A'.$row.':G'.$row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C3C9D1');
                foreach ($intake['courses'] as $course) {
                    $row++;
                    $s->setCellValue('A'.$row, $course['name']);
                    $s->getStyle('A'.$row)->getAlignment()->setIndent(2);
                    $s->setCellValue('B'.$row, implode(' · ', array_filter([
                        (!$course['due'] ? 'Not due until '.date('j M Y', strtotime($course['due_date'])) : null),
                        ($course['due'] && $course['rate'] === null ? 'No registered students, so no rate' : null),
                        (!empty($this->copy['show_running']) && $course['due'] && $course['running'] ? 'Still running · ends '.date('j M Y', strtotime($course['end_date'])) : null),
                        ($course['small'] ? 'Small cohort' : null),
                    ])));
                    $this->font($s, 'B'.$row, self::GREY);
                    if (!$course['due'] || $course['rate'] === null) {
                        $s->setCellValue('C'.$row, $course['due'] ? 'No rate' : 'Not due');
                        $this->font($s, 'C'.$row, self::GREY);
                        $s->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $this->rateCell($s, 'C'.$row, $course['rate'], $course['low'], true);
                    }
                    if ($course['due']) {
                        $s->fromArray([$course['registered'], $course['kept'], $course['dropped_out'], $course['never_registered']], null, 'D'.$row, true);
                    } else {
                        $s->fromArray(['–', '–', '–', '–'], null, 'D'.$row);
                        $s->getStyle('D'.$row.':G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                    $this->rule($s, 'A'.$row.':G'.$row);
                }
            }
            $s->getStyle('A'.$first.':B'.$row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $s->getStyle('C'.$first.':G'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            if (!empty($this->report['many']) && $this->report['has_due']) {
                $s->getStyle('A'.$row.':G'.$row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::INK);
                $row++;
                $s->setCellValue('A'.$row, 'All intakes');
                $this->rateCell($s, 'C'.$row, $t['rate'], false, true);
                $s->fromArray([$t['registered'], $t['kept'], $t['dropped_out'], $t['never_registered']], null, 'D'.$row, true);
                $s->getStyle('A'.$row.':G'.$row)->getFont()->setBold(true);
                $s->getStyle('A'.$row.':G'.$row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::INK);
            }
        }

        // Notes.
        $row += 2;
        $notes = [['How the rate is worked out.', $this->copy['how']]];
        if (!empty($this->report['small_note'])) {
            $notes[] = ['Small cohort.', $this->report['small_note']];
        }
        foreach ($notes as [$lead, $text]) {
            $s->setCellValue('A'.$row, $lead.' '.$text);
            $s->mergeCells('A'.$row.':G'.$row);
            $s->getStyle('A'.$row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $this->font($s, 'A'.$row, self::GREY);
            $s->getRowDimension($row)->setRowHeight(28);
            $row++;
        }

        $s->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0);
        $s->getHeaderFooter()->setOddFooter('&LLondon Churchill College · '.$this->copy['title'].' Report · '.$this->report['intake'].'&RConfidential, internal use only');
        $s->setSelectedCell('A1');
    }

    protected function header(Worksheet $s, int $row, array $cells): void
    {
        foreach ($cells as $col => $label) {
            $s->setCellValue($col.$row, $label);
        }
        $range = array_key_first($cells).$row.':'.array_key_last($cells).$row;
        $s->getStyle($range)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB(self::GREY);
        $s->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::INK);
        foreach (array_keys($cells) as $col) {
            if (!in_array($col, ['A', 'B'], true)) {
                $s->getStyle($col.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }

    /** A rate as a percentage cell, or "No rate"; orange when low. */
    protected function rateCell(Worksheet $s, string $cell, ?float $rate, bool $low, bool $bold): void
    {
        if ($rate === null) {
            $s->setCellValue($cell, 'No rate');
            $this->font($s, $cell, self::GREY);
            $s->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            return;
        }
        $s->setCellValue($cell, $rate / 100);
        $s->getStyle($cell)->getNumberFormat()->setFormatCode('0.0%');
        $this->font($s, $cell, $low ? self::RUST : self::INK, $bold);
    }

    protected function rule(Worksheet $s, string $range): void
    {
        $s->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::RULE);
    }

    protected function font(Worksheet $s, string $cell, string $colour, bool $bold = false, ?int $size = null): void
    {
        $font = $s->getStyle($cell)->getFont()->setBold($bold);
        $font->getColor()->setRGB($colour);
        if ($size) {
            $font->setSize($size);
        }
    }
}
