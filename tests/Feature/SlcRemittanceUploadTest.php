<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Slc\RemittanceFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * The SLC remittance upload: the tilde-separated `.dat` the SLC issues and the
 * comma-separated `.csv` the office used to save from it read as the same rows,
 * and the preview endpoint takes either. Fixtures are made up here - no real
 * SSNs or names - and the preview writes nothing, so nothing is left behind.
 */
class SlcRemittanceUploadTest extends TestCase
{
    use DatabaseTransactions;

    private const DAT = "LCHC~LONDON CHURCHILL COLLEGE~3~23052018~T~7706.25~~\r\n"
        . "WIN~SFDU00000001A~ALPHA~ANNA~01011966~~818441~BUSINESS-ENG-JAN~2~~1541.25~~~~~R~\r\n"
        . "AUT~SFDU00000002B~BETA~BEN~19071979~~818442~BUSINESS-ENG-SEP~1~~3082.50~~~~~R~\r\n"
        . "\r\n"
        . "AUT~SFDU00000003C~GAMMA~GEORGE~07051976~~818442~BUSINESS-ENG-SEP~2~~3082.50~~~~~R~\r\n";

    /* the same file after a trip through Excel: commas, padded summary, lost zeros */
    private const CSV = "\xEF\xBB\xBFLCHC,LONDON CHURCHILL COLLEGE,3,23052018,T,7706.25,,,,,,,,,,\r\n"
        . "WIN,SFDU00000001A,ALPHA,ANNA,1011966,,818441,BUSINESS-ENG-JAN,2,,1541.25,,,,,R\r\n"
        . "AUT,SFDU00000002B,BETA,BEN,19071979,,818442,BUSINESS-ENG-SEP,1,,3082.5,,,,,R\r\n"
        . "AUT,SFDU00000003C,GAMMA,GEORGE,7051976,,818442,BUSINESS-ENG-SEP,2,,3082.5,,,,,R\r\n";

    /** the columns uploadCSV() reads */
    private const USED = [0, 1, 2, 3, 6, 7, 8, 10];

    private function tmp(string $content, string $ext): string
    {
        $path = tempnam(sys_get_temp_dir(), 'slc') . '.' . $ext;
        file_put_contents($path, $content);

        return $path;
    }

    public function test_the_dat_file_reads_as_rows_with_the_delimiter_detected(): void
    {
        $rows = RemittanceFile::rows($this->tmp(self::DAT, 'dat'));

        $this->assertCount(4, $rows, 'summary + 3 transactions, blank line skipped');
        $this->assertSame(['LCHC', 'LONDON CHURCHILL COLLEGE', '3', '23052018', 'T', '7706.25'], array_slice($rows[0], 0, 6));
        $this->assertSame('SFDU00000001A', $rows[1][1]);
        $this->assertSame('01011966', $rows[1][4], 'leading zero kept');
        $this->assertSame('1541.25', $rows[1][10]);
        $this->assertSame('R', $rows[1][15]);
    }

    public function test_the_csv_copy_gives_the_same_values_in_the_same_places(): void
    {
        $dat = RemittanceFile::rows($this->tmp(self::DAT, 'dat'));
        $csv = RemittanceFile::rows($this->tmp(self::CSV, 'csv'));

        $this->assertCount(count($dat), $csv);
        $this->assertSame('LCHC', $csv[0][0], 'the BOM is gone');
        $this->assertSame(array_slice($dat[0], 0, 6), array_slice($csv[0], 0, 6));
        foreach ([1, 2, 3] as $i) {
            foreach (self::USED as $col) {
                $a = $dat[$i][$col];
                $b = $csv[$i][$col];
                /* Excel drops leading zeros on dates and trailing zeros on amounts */
                if ($col === 4) {
                    $this->assertSame(ltrim($a, '0'), ltrim($b, '0'), "row $i col $col");
                } elseif ($col === 10) {
                    $this->assertSame((float) $a, (float) $b, "row $i col $col");
                } else {
                    $this->assertSame($a, $b, "row $i col $col");
                }
            }
        }
    }

    public function test_an_unreadable_path_gives_no_rows(): void
    {
        $this->assertSame([], RemittanceFile::rows('/nonexistent/remittance.dat'));
    }

    public function test_the_preview_accepts_the_dat_file(): void
    {
        $this->actingAs(User::find(1));
        $file = new UploadedFile($this->tmp(self::DAT, 'dat'), 'LCHC_2017_FT_T_23052018_remittance.dat', 'application/octet-stream', null, true);

        $res = $this->post(route('reports.account.payment.upload.csv'), ['payment_file_csv' => $file, 'submited' => 1]);

        $res->assertOk();
        $htm = $res->json('htm');
        $this->assertStringContainsString('LONDON CHURCHILL COLLEGE', $htm);
        $this->assertStringContainsString('<td>3</td>', $htm, 'transaction count from the summary row');
        $this->assertStringContainsString('23-05-2018', $htm, 'the summary date');
        $this->assertStringContainsString('7706.25', $htm);
        /* the preview draws two rows per transaction: the values and a detail line */
        $this->assertSame(3 * 2, substr_count($htm, '<tr class="match_'));
        foreach (['SFDU00000001A', 'SFDU00000002B', 'SFDU00000003C'] as $ssn) {
            $this->assertStringContainsString($ssn, $htm);
        }
        $this->assertStringContainsString('Student Not Found', $htm, 'made-up SSNs match nobody');
    }

    public function test_the_preview_still_accepts_the_csv_copy(): void
    {
        $this->actingAs(User::find(1));
        $file = new UploadedFile($this->tmp(self::CSV, 'csv'), 'remittance.csv', 'text/csv', null, true);

        $res = $this->post(route('reports.account.payment.upload.csv'), ['payment_file_csv' => $file, 'submited' => 1]);

        $res->assertOk();
        $this->assertStringContainsString('23-05-2018', $res->json('htm'));
        $this->assertSame(3 * 2, substr_count($res->json('htm'), '<tr class="match_'));
    }

    public function test_other_file_types_are_turned_away(): void
    {
        $this->actingAs(User::find(1));
        $file = new UploadedFile($this->tmp(self::DAT, 'xlsx'), 'remittance.xlsx', 'application/octet-stream', null, true);

        $res = $this->post(route('reports.account.payment.upload.csv'), ['payment_file_csv' => $file, 'submited' => 1]);

        $res->assertOk();
        $this->assertStringContainsString('as .dat or .csv', $res->json('htm'));
        $this->assertStringNotContainsString('<table', $res->json('htm'));
    }
}
