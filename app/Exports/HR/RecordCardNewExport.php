<?php

namespace App\Exports\HR;

use App\Services\HR\RecordCardReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Employee Record Card, new version: one row per card. Every cell is text (phones, NI and works numbers keep their form). */
class RecordCardNewExport extends StringValueBinder implements FromArray, WithHeadings, WithStyles, WithTitle, WithCustomValueBinder, ShouldAutoSize
{
    public function __construct(private array $list)
    {
    }

    public function title(): string
    {
        return 'Record Cards';
    }

    public function headings(): array
    {
        return ['Title', 'Forename', 'Surname', 'Date of birth', 'Gender', 'Ethnic origin', 'Nationality', 'NI number',
            'Company', 'Works number', 'Job title', 'Department', 'Type', 'Started on', 'Ended on', 'Current status', 'Service',
            'Address', 'Telephone', 'Mobile', 'Email',
            'Emergency contact', 'Relationship', 'Emergency telephone', 'Emergency mobile', 'Emergency email',
            'Disabled', 'Car registration', 'Missing details'];
    }

    public function array(): array
    {
        $d = fn ($v) => $v ? date('d/m/Y', strtotime($v)) : '';

        return array_map(fn ($r) => [
            $r['title'], $r['forename'], $r['surname'], $d($r['dob']), $r['gender'], $r['ethnicity'], $r['nationality'], $r['ni'],
            RecordCardReport::COMPANY, $r['works'], $r['jobTitle'], $r['department'], $r['typeName'], $d($r['started']), $d($r['ended']),
            $r['status'] === 1 ? 'Active' : 'Inactive', (string) $r['service'],
            $r['address'], $r['telephone'], $r['mobile'], $r['email'],
            $r['eName'], $r['eRel'], $r['eTel'], $r['eMob'], $r['eEmail'],
            $r['disability'], $r['carReg'], count($r['missing']) ? (string) count($r['missing']) : '',
        ], $this->list);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('D2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
