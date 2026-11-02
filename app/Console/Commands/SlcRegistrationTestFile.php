<?php

namespace App\Console\Commands;

use App\Services\SlcRegistration\RegistrationState;
use App\Services\SlcRegistration\SisFile;
use App\Services\SlcRegistration\StudentSnapshots;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * A made-up SIS Registration Confirmation Report built from this install's students, for trying
 * /slc-registration without a real SLC export. Not on production. Most lines match our records;
 * a few carry the differences the page is built for (name spelling, TFL, swapped DOB, postcode,
 * start date, a student without an SSN on our side, an SSN nobody holds).
 */
class SlcRegistrationTestFile extends Command
{
    protected $signature = 'slc-registration:test-file {--year= : two-digit academic year, e.g. 26} {--count=16 : lines matched by SSN} {--out= : file to write (default storage/app/slc-registration/test-export.txt)}';

    protected $description = 'Write a test SIS Registration Confirmation Report from local students (not on production)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Not on production.');

            return self::FAILURE;
        }
        $ay = (string) ($this->option('year') ?: now()->subMonths(7)->format('y'));
        $year = RegistrationState::academicYear($ay);
        if (!$year) {
            $this->error('No academic year for '.$ay.'.');

            return self::FAILURE;
        }

        // students with an SSN on a course that has an instance this year and an SLC code
        $ids = DB::table('students as s')
            ->join('student_course_relations as r', fn ($j) => $j->on('r.student_id', '=', 's.id')->where('r.active', 1)->whereNull('r.deleted_at'))
            ->join('course_creation_instances as i', fn ($j) => $j->on('i.course_creation_id', '=', 'r.course_creation_id')->where('i.academic_year_id', $year->id))
            ->whereNull('s.deleted_at')->whereNotNull('s.ssn_no')->where('s.ssn_no', '!=', '')
            ->orderByDesc('s.id')->limit(400)->pluck('s.id')->all();
        $snap = array_values(array_filter(StudentSnapshots::load($ids, $year->id), fn ($s) => $s['slc_code'] !== '' && $s['instance'] && $s['addresses'] && $s['dob'] && $s['gender']));
        $snap = array_slice($snap, 0, (int) $this->option('count'));
        if (count($snap) < 8) {
            $this->error('Not enough students with an SSN, an SLC course code and a '.$year->name.' instance.');

            return self::FAILURE;
        }
        $noSsn = DB::table('students as s')
            ->join('student_course_relations as r', fn ($j) => $j->on('r.student_id', '=', 's.id')->where('r.active', 1)->whereNull('r.deleted_at'))
            ->whereNull('s.deleted_at')->where(fn ($q) => $q->whereNull('s.ssn_no')->orWhere('s.ssn_no', ''))
            ->orderByDesc('s.id')->limit(60)->pluck('s.id')->all();
        $noSsn = array_values(array_filter(StudentSnapshots::load($noSsn, $year->id), fn ($s) => $s['addresses'] && $s['dob']));

        $date = now()->format('dmY');
        $line = function (array $s, array $over = []) use ($date, $ay) {
            $a = $s['addresses'][0];
            $parts = array_map('trim', explode(',', $a['text']));
            $city = count($parts) > 1 ? array_pop($parts) : '';
            $f = [
                'title' => $s['title'] ?: 'Mr', 'first' => strtoupper($s['first']), 'other' => '', 'last' => strtoupper($s['last']), 'gender' => $s['gender'],
                'dob' => $s['dob'] ? date('dmY', strtotime($s['dob'])) : '', 'slcCode' => $s['slc_code'],
                'courseName' => substr(strtoupper(trim(explode('(', str_ireplace(['HND in ', 'HND '], '', $s['course_name']))[0])).' SEP I...ENG-NI-SCO', 0, 40),
                'year' => (string) ($s['instance']['rank'] ?? 1), 'start' => $s['instance'] ? date('dmY', strtotime($s['instance']['start'])) : '',
                'address' => strtoupper(implode(' ', $parts)).'   '.strtoupper($city).' ', 'postcode' => $a['postcode'], 'ssn' => $s['ssn'],
                'tfl' => number_format((float) ($s['instance']['fee'] ?? 0), 2, '.', ''),
            ];
            $f = array_merge($f, $over);

            return implode('~', [$date, $ay, $f['title'], $f['first'], $f['other'], $f['last'], $f['gender'], $f['dob'], '', '', $f['slcCode'], $f['courseName'], $f['year'], $f['start'], $f['address'], $f['postcode'], $f['ssn'], $f['tfl'], $f['tfl'], '0.00', '0.00', 'F', 'N', 'F', 'Y', 'Y', 'N', 'N', '', '']);
        };

        $lines = [];
        foreach ($snap as $k => $s) {
            $over = match ($k) {
                1 => ['first' => strtoupper(substr($s['first'], 0, -1)).'E'],                // a spelling difference
                2 => ['tfl' => '0.00'],                                                         // no Tuition Fee Loan
                3 => ['dob' => date('mdY', strtotime($s['dob']))],                               // day and month swapped
                4 => ['postcode' => substr($s['addresses'][0]['postcode'], 0, -3).'9ZZ'],       // a different postcode
                5 => ['start' => date('dmY', strtotime($s['instance']['start'].' +21 days'))], // a later start
                6 => ['tfl' => number_format(((float) $s['instance']['fee']) / 2, 2, '.', '')], // half the fee
                7 => ['title' => $s['title'] === 'Ms' ? 'Miss' : 'Ms'],                           // a title difference
                default => [],
            };
            $lines[] = $line($s, $over);
        }
        // a student we hold without an SSN (Identify suggests them), and an SSN nobody holds
        if ($noSsn) {
            $lines[] = $line($noSsn[0] + ['instance' => null], ['ssn' => 'SFTS'.$ay.'90000001A', 'slcCode' => $snap[0]['slc_code'], 'year' => '1', 'start' => $snap[0]['instance'] ? date('dmY', strtotime($snap[0]['instance']['start'])) : '', 'tfl' => '6350.00']);
        }
        $lines[] = implode('~', [$date, $ay, 'Ms', 'TESTA', '', 'NOBODY', 'F', '01011990', '', '', $snap[0]['slc_code'], 'TEST COURSE SEP I...ENG-NI-SCO', '1', date('dmY', strtotime($snap[0]['instance']['start'])), '1 TEST STREET   LONDON ', 'E1 1AA', 'SFTS'.$ay.'90000002B', '6350.00', '6350.00', '0.00', '0.00', 'F', 'N', 'F', 'Y', 'Y', 'N', 'N', '', '']);

        $text = implode("\r\n", array_merge(['HH'.$ay.'LCHC00001CR'], $lines))."\r\n";
        $out = $this->option('out') ?: storage_path('app/slc-registration/test-export.txt');
        @mkdir(dirname($out), 0775, true);
        file_put_contents($out, $text);
        $parsed = SisFile::parse($text);
        $this->info('Wrote '.count($parsed['rows']).' lines for '.$year->name.' to '.$out);

        return self::SUCCESS;
    }
}
