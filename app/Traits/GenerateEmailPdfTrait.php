<?php
namespace App\Traits;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

trait GenerateEmailPdfTrait{
    public function generateEmailPdf($studentEmailId, $student_id, $title, $body){
        $user = User::where('id', auth()->user()->id)->get()->first();

        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59);}
                                table{margin-left: 0px; width: 100%;}
                                figure{margin: 0;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                .vtop{vertical-align: top;}
                                .mailContentTable tr th, .mailContentTable tr td{ padding: 0 0 10px 0; vertical-align: top;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
                $PDFHTML .= '<table class="mailContentTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Issued Date</th>';
                            $PDFHTML .= '<td>'.date('d-m-Y').'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Issued BY</th>';
                            $PDFHTML .= '<td>'.(isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name).'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Email Body</th>';
                            $PDFHTML .= '<td>'.$body.'</td>';
                        $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
                
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = $studentEmailId.'_'.$student_id.'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        $content = $pdf->output();
        Storage::disk('s3')->put('public/students/'.$student_id.'/'.$fileName, $content );

        return $fileName;
    }
}