<?php 
namespace App\Traits;

use App\Models\Applicant;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use App\Models\Signatory;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

trait GenerateBulkCommunicationPdfTrait{
    public function generateBulkLetterPdf($the_content, $pin){
        /* Generate PDF Start */
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();
        $LetterHeader = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
        $LetterFooters = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>Print All Letters</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59); padding-top: 10px;}
                                table{margin-left: 0px; width: 100%;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important;margin-right:85px !important;margin-bottom: 95px;}
                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
                if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/header/'.$LetterHeader->current_file_name)):
                    $headerImageURL = url('storage/letterheaderfooter/header/'.$LetterHeader->current_file_name);
                    $PDFHTML .= '<header>';
                        $PDFHTML .= '<img alt="'.$LetterHeader->current_file_name.'" style="width: 100%; height: auto;" src="'.$headerImageURL.'"/>';
                    $PDFHTML .= '</header>';
                endif;

                $PDFHTML .= '<footer>';
                    $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; font-family: serif; 
                                font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                        if(isset($LetterFooters->current_file_name) && !empty($LetterFooters->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/footer/'.$LetterFooters->current_file_name)):
                            $footerImageURL = url('storage/letterheaderfooter/footer/'.$LetterFooters->current_file_name);
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td colspan="2" class="footerPartners" style="text-align: center; vertical-align: middle; padding-bottom: 5px;">';
                                    $PDFHTML .= '<img style=" width: 100%; height: auto; margin-left:0; margin-right:0;" src="'.$footerImageURL.'" alt="'.$LetterFooters->name.'"/>';
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                        endif;
                        $PDFHTML .= '<tr class="pinRow">';
                            $PDFHTML .= '<td style="padding-bottom: 3px;">';
                                $PDFHTML .= '<span class="pageCounter text-left"></span>';
                            $PDFHTML .= '</td>';
                            $PDFHTML .= '<td class="pinNumber text-right" style="padding-bottom: 3px;">';
                                $PDFHTML .= 'pin - '.$pin;
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';

                        if(!empty($regNo) || !empty($regAt)):
                        $PDFHTML .= '<tr class="regInfoRow">';
                            $PDFHTML .= '<td colspan="2" class="text-center" style="padding-top: 3px;">';
                                $PDFHTML .= (!empty($regNo) ? 'Company Reg. No. '.$regNo->value : '');
                                $PDFHTML .= (!empty($regAt) ? (!empty($regNo) ? ', ' : '').$regAt->value : '');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                        endif;
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</footer>';

                $PDFHTML .= $the_content;
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = 'All_'.auth()->user()->id.'_'.time().'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        $content = $pdf->output();
        Storage::disk('local')->put('public/bulk-communication/'.$fileName, $content );

        return Storage::disk('local')->url('public/bulk-communication/'.$fileName);
    }
}