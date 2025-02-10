<?php 
namespace App\Traits;

use App\Models\AgentComission;
use App\Models\AgentComissionDetail;
use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Number;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

trait GenerateAgentComissionPdfTrait{
    public function generagePdf($payment_id, $comission_id){
        $comission = AgentComission::find($comission_id);
        $comission_details = AgentComissionDetail::with('student')->where('agent_comission_id', $comission_id)->get();
        $remittanceRef = (isset($comission->remittance_ref) && !empty($comission->remittance_ref) ? $comission->remittance_ref : '');

        $user = User::find(auth()->user()->id);
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Agent Remittance ('.$remittanceRef.') Report';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                header{position: fixed;left: 0px;right: 0px; height: 80px; margin-top: -90px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.headerRightCol{ width: 200px;}
                                .headerTable tr td{vertical-align: top; padding: 0;}
                                .headerTable tr td.headerRightCol{font-size: 12px; line-height: 14px;}
                                .headerTable tr td.headerRightCol table tr.headerHeadingRow td{font-size: 16px; text-transform: uppercase; font-weight: bold; padding-bottom: 2px;}
                                .headerTable tr td.headerRightCol table tr.headerBodyRow td{padding-top: 3px;}
                                .headerTable tr td.headerRightCol table tr td.htd2{ text-align: right;}

                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.attenRateReportTable tr th{ background: #0d9488; color: #FFF; font-size: 12px; text-transform: uppercase; font-weight: bold; padding-top: 10px; padding-bottom: 10px;}
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
                                .table.attenRateReportTable tr th.amountHeading, .table.attenRateReportTable tr td.amountColumn{width: 90px; text-align: center;}
                                .attenRateReportTable.table {border-collapse: separate;}
                                .attenRateReportTable.table tr th, .attenRateReportTable.table tr td{border-spacing: 3px;}
                                .attenRateReportTable.table tr:nth-child(even) td{background: rgba(241, 245, 249, .9);}
                                .table.attenRateReportTable tfoot tr th.amountHeading{font-size: 14px;}
                                .table.attenRateReportTable thead tr td.serialHeading, .table.attenRateReportTable tbody tr td.serialColumn{width: 30px;}

                                .invInfoTable{margin-top: 30px; margin-bottom: 50px;}
                                .invToTable{width: 300px;}
                                .invToTable.payInfoTable{margin-top: 30px;}
                                .invInfoTableRight{width: 200px; vertical-align: top;}
                                .invInfoTableRight .invToTable{width: 100%;}
                                .invToLabel{ font-size: 12px; font-weight: bold; text-transform: uppercase; color: #0d9488; line-height: 1; padding-bottom: 10px;}
                                .invToName{ font-size: 18px; font-weight: bold; text-transform: capitalize; line-height: 1; padding-bottom: 2px;}
                                .invToOrg{ font-size: 13px; line-height: 1; padding-bottom: 7px;}
                                .invInfoRow td{ vertical-align: top; font-size: 13px; line-height: 16px; padding-bottom: 5px;}
                                .invInfoRow td:first-child{ width: 80px;}
                                .invInfoRow td.addressCol{ width: 220px; line-height: 16px;}
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td class="text-left"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                            $PDFHTML .= '<td class="headerRightCol">';
                                
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="invInfoTable">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="invInfoTableLeft">';
                            $PDFHTML .= '<table class="invToTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Remit To</td></tr>';
                                $PDFHTML .= '<tr><td colspan="2" class="invToName">'.(isset($comission->agent->organization) && !empty($comission->agent->organization) ? $comission->agent->organization : '').'</td></tr>';
                                //$PDFHTML .= '<tr><td colspan="2" class="invToOrg">'.(isset($comission->agent->organization) && !empty($comission->agent->organization) ? $comission->agent->organization : '').'</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td colspan="2">'.(isset($comission->agent->email) && !empty($comission->agent->email) ? $comission->agent->email : '').'</td>';
                                $PDFHTML .= '</tr>';
                                if(isset($comission->agent->address->full_address_pdf) && !empty($comission->agent->address->full_address_pdf)):
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Address</td>';
                                    $PDFHTML .= '<td class="addressCol">'.$comission->agent->address->full_address_pdf.'</td>';
                                $PDFHTML .= '</tr>';
                                endif;
                            $PDFHTML .= '</table>';

                            $PDFHTML .= '<table class="invToTable payInfoTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Payment Information</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Sort Code</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->sort_code) && !empty($comission->agent->bank->sort_code) ? $comission->agent->bank->sort_code : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Account No</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->ac_no) && !empty($comission->agent->bank->ac_no) ? $comission->agent->bank->ac_no : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Beneficiary</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->beneficiary) && !empty($comission->agent->bank->beneficiary) ? $comission->agent->bank->beneficiary : '').'</td>';
                                $PDFHTML .= '</tr>';
                            $PDFHTML .= '</table>';

                        $PDFHTML .= '</td>';
                        $PDFHTML .= '<td class="invInfoTableRight text-right" style="vertical-align: top;">';
                            $PDFHTML .= '<table class="invToTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Remit Report</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Reference</td>';
                                    $PDFHTML .= '<td class="text-right">'.(!empty($remittanceRef) ? '#'.$remittanceRef : '---').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Date</td>';
                                    $PDFHTML .= '<td class="text-right">'.(isset($comission->entry_date) && !empty($comission->entry_date) ? date('jS M, Y', strtotime($comission->entry_date)) : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Semester</td>';
                                    $PDFHTML .= '<td class="text-right">'.(isset($comission->semester->name) && !empty($comission->semester->name) ? $comission->semester->name : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>No of Student</td>';
                                    $PDFHTML .= '<td class="text-right">'.(!empty($comission_details) && $comission_details->count() > 0 ? $comission_details->count() : 0).'</td>';
                                $PDFHTML .= '</tr>';
                            $PDFHTML .= '</table>';

                            
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';

                

                $PDFHTML .= '<table class="table attenRateReportTable table-sm" id="continuationListTable">';
                    $PDFHTML .= '<thead>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th class="serialHeading">SL</th>';
                            $PDFHTML .= '<th>Reference</th>';
                            $PDFHTML .= '<th>Name</th>';
                            $PDFHTML .= '<th class="amountHeading">Amount</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</thead>';
                    $PDFHTML .= '<tbody>';
                    if($comission_details->count() > 0):
                        $sl = 1;
                        foreach($comission_details as $list):
                            $comissionFor = (isset($list->comission_for) && !empty($list->comission_for) ? $list->comission_for : 'Course Fee');
                            $receiptAmount = (isset($list->receipt->amount) && $list->receipt->amount > 0 ? ($comissionFor == 'Refund' ? abs($list->receipt->amount) * -1 : $list->receipt->amount) : 0);
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="serialColumn">'.$sl.'</td>';
                                $PDFHTML .= '<td style="font-weight: bold; font-size: 12px;">';
                                    $PDFHTML .= (isset($list->student->application_no) ? $list->student->application_no : '');
                                    $PDFHTML .= (isset($list->student->registration_no) ? '<br/><span style="display: block; padding-top: 3px; font-weight: normal; font-size: 11px; color: #64748b;">'.$list->student->registration_no.'</span>' : '');
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td>';
                                    $PDFHTML .= (isset($list->student->full_name) ? $list->student->full_name : $list->student->full_name);
                                    $PDFHTML .= (isset($list->student->activeCR->creation->course->name) && !empty($list->student->activeCR->creation->course->name) ? '<span style="display: block; padding-top: 3px; font-weight: normal; font-size: 11px; color: #64748b;">'.$list->student->activeCR->creation->course->name.'</span>' : '');
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td class="amountColumn" style="'.($list->amount < 0 ? 'color: red;' : '').'">'.Number::currency($list->amount, in: 'GBP').'</td>';
                            $PDFHTML .= '</tr>';
                            $sl++;
                        endforeach;
                    endif;
                    $PDFHTML .= '</tbody>';
                    $PDFHTML .= '<tfoot>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th colspan="3">Total</th>';
                            $PDFHTML .= '<th class="amountHeading">'.Number::currency($comission->comissions->sum('amount'), in: 'GBP').'</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</tfoot>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = time().'_'.str_replace(' ', '_', $report_title).'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        $content = $pdf->output();
        Storage::disk('s3')->put('public/agents/payment/'.$payment_id.'/'.$fileName, $content );

        return ['path' => Storage::disk('s3')->url('public/agents/payment/'.$payment_id.'/'.$fileName), 'filename' => $fileName];
    }
}