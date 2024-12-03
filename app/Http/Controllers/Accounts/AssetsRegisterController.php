<?php

namespace App\Http\Controllers\Accounts;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetsRegisterUpdateRequest;
use App\Models\AccAssetRegister;
use App\Models\AccAssetType;
use App\Models\AccBank;
use App\Models\Option;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetsRegisterController extends Controller
{
    public function index(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        return view('pages.accounts.assets.index', [
            'title' => 'Accounts Assets Register - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Assets Register', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count(),
            'types' => AccAssetType::where('active', 1)->orderBy('name', 'asc')->get()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);
        $type = (isset($request->type) && $request->type > 0 ? $request->type : 0);
        $queryDates = (isset($request->queryDate) && !empty($request->queryDate) && strlen($request->queryDate) == 23 ? explode(' - ', $request->queryDate) : []);
        $startDate = (isset($queryDates[0]) && !empty($queryDates[0]) ? date('Y-m-d', strtotime($queryDates[0])) : '');
        $endDate = (isset($queryDates[1]) && !empty($queryDates[1]) ? date('Y-m-d', strtotime($queryDates[1])) : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AccAssetRegister::with('trans', 'type')->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('description','LIKE','%'.$queryStr.'%');
                $q->orWhere('location','LIKE','%'.$queryStr.'%');
                $q->orWhere('serial','LIKE','%'.$queryStr.'%');
                $q->orWhere('barcode','LIKE','%'.$queryStr.'%');
            });
        endif;
        if(!empty($startDate) && !empty($endDate)):
            $query->whereHas('trans', function($q) use($startDate, $endDate){
                $q->whereBetween('transaction_date_2', [$startDate, $endDate]);
            });
        endif;
        if($type > 0): $query->where('acc_asset_type_id', $type); endif;
        if($status == 3):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'acc_transaction_id ' => (isset($list->acc_transaction_id ) && $list->acc_transaction_id > 0 ? $list->acc_transaction_id : ''),
                    'transaction_date_2' => (isset($list->trans->transaction_date_2) && !empty($list->trans->transaction_date_2) ? date('jS M, Y', strtotime($list->trans->transaction_date_2)) : ''),
                    'transaction_code' => (isset($list->trans->transaction_code) && !empty($list->trans->transaction_code) ? $list->trans->transaction_code : ''),
                    'detail' => (isset($list->trans->detail) && !empty($list->trans->detail) ? $list->trans->detail : ''),
                    'transaction_amount' => (isset($list->trans->transaction_amount) && $list->trans->transaction_amount > 0 ? '£'.number_format($list->trans->transaction_amount, 2) : '£0.00'),
                    'transaction_doc_name' => (isset($list->trans->transaction_doc_name) && !empty($list->trans->transaction_doc_name) ? $list->trans->transaction_amount : ''),
                    'acc_asset_type_id' => (isset($list->type->name) && !empty($list->type->name) ? $list->type->name : ''),
                    'description' => (isset($list->description) && !empty($list->description) ? $list->description : ''),
                    'location' => (isset($list->location) && !empty($list->location) ? $list->location : ''),
                    'serial' => (isset($list->serial) && !empty($list->serial) ? $list->serial : ''),
                    'barcode' => (isset($list->barcode) && !empty($list->barcode) ? $list->barcode : ''),
                    'life' => (isset($list->life) && !empty($list->life) ? ($list->life == 1 ? $list->life.' Year' : $list->life.' Years') : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function newRegisters(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        return view('pages.accounts.assets.new-register', [
            'title' => 'Accounts Assets Register - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Assets Register', 'href' => route('accounts.assets.register')],
                ['label' => 'Just In', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count(),
            'openedAssetList' => AccAssetRegister::where('active', 1)->get(),
            'types' => AccAssetType::where('active', 1)->orderBy('name', 'asc')->get()
        ]);
    }

    public function edit(Request $request){
        $row_id = $request->row_id;
        $AccAssets = AccAssetRegister::find($row_id);

        return response()->json(['row' => $AccAssets], 200);
    }

    public function update(Request $request){
        $id = $request->id;
        $oldRow = AccAssetRegister::find($id);
        $description = $request->description;
        $acc_asset_type_id = $request->acc_asset_type_id;
        $location = (isset($request->location) && !empty($request->location) ? $request->location : null);
        $serial = (isset($request->serial) && !empty($request->serial) ? $request->serial : null);
        $barcode = (isset($request->barcode) && !empty($request->barcode) ? $request->barcode : (isset($oldRow->barcode) && !empty($oldRow->barcode) ? $oldRow->barcode : random_int(10000000, 99999999)));
        $life = (isset($request->life) && !empty($request->life) ? $request->life : null);
        
        $register = AccAssetRegister::where('id', $id)->update([
            'description' => $description,
            'acc_asset_type_id' => $acc_asset_type_id,
            'location' => $location,
            'serial' => $serial,
            'barcode' => $barcode,
            'life' => $life,
            'active' => 2,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['msg' => 'Register successfully updated.'], 200);
    }

    public function updateSingle(AssetsRegisterUpdateRequest $request){
        $id = $request->id;
        $oldRow = AccAssetRegister::find($id);
        $description = $request->description;
        $acc_asset_type_id = $request->acc_asset_type_id;
        $location = (isset($request->location) && !empty($request->location) ? $request->location : null);
        $serial = (isset($request->serial) && !empty($request->serial) ? $request->serial : null);
        $barcode = (isset($request->barcode) && !empty($request->barcode) ? $request->barcode : (isset($oldRow->barcode) && !empty($oldRow->barcode) ? $oldRow->barcode : random_int(10000000, 99999999)));
        $life = (isset($request->life) && !empty($request->life) ? $request->life : null);
        $active = (isset($request->active) ? $request->active : 2);

        $register = AccAssetRegister::where('id', $id)->update([
            'description' => $description,
            'acc_asset_type_id' => $acc_asset_type_id,
            'location' => $location,
            'serial' => $serial,
            'barcode' => $barcode,
            'life' => $life,
            'active' => $active,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['msg' => 'Register successfully updated.'], 200);
    }

    public function destroy($id){
        $data = AccAssetRegister::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AccAssetRegister::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function exportRegisters(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);
        $type = (isset($request->type) && $request->type > 0 ? $request->type : 0);
        $queryDates = (isset($request->queryDate) && !empty($request->queryDate) && strlen($request->queryDate) == 23 ? explode(' - ', $request->queryDate) : []);
        $startDate = (isset($queryDates[0]) && !empty($queryDates[0]) ? date('Y-m-d', strtotime($queryDates[0])) : '');
        $endDate = (isset($queryDates[1]) && !empty($queryDates[1]) ? date('Y-m-d', strtotime($queryDates[1])) : '');

        $query = AccAssetRegister::with('trans', 'type');
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('description','LIKE','%'.$queryStr.'%');
                $q->orWhere('location','LIKE','%'.$queryStr.'%');
                $q->orWhere('serial','LIKE','%'.$queryStr.'%');
                $q->orWhere('barcode','LIKE','%'.$queryStr.'%');
            });
        endif;
        if(!empty($startDate) && !empty($endDate)):
            $query->whereHas('trans', function($q) use($startDate, $endDate){
                $q->whereBetween('transaction_date_2', [$startDate, $endDate]);
            });
        endif;
        if($type > 0): $query->where('acc_asset_type_id', $type); endif;
        if($status == 3):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
        endif;

        $theCollection = [];
        $theCollection[1][] = "TC No";
        $theCollection[1][] = "Date";
        $theCollection[1][] = "Supplier";
        $theCollection[1][] = "Price";
        $theCollection[1][] = "Type";
        $theCollection[1][] = "Description";
        $theCollection[1][] = "Location";
        $theCollection[1][] = "Serial";
        $theCollection[1][] = "Barcode";
        $theCollection[1][] = "Life Span";

        $row = 2;
        $assets = $query->get();
        if($assets->count() > 0):
            foreach($assets as $list):
                $theCollection[$row][] = (isset($list->trans->transaction_code) && !empty($list->trans->transaction_code) ? $list->trans->transaction_code : '');
                $theCollection[$row][] = (isset($list->trans->transaction_date_2) && !empty($list->trans->transaction_date_2) ? date('jS M, Y', strtotime($list->trans->transaction_date_2)) : '');
                $theCollection[$row][] = (isset($list->trans->detail) && !empty($list->trans->detail) ? $list->trans->detail : '');
                $theCollection[$row][] = (isset($list->trans->transaction_amount) && $list->trans->transaction_amount > 0 ? $list->trans->transaction_amount : '0.00');
                $theCollection[$row][] = (isset($list->type->name) && !empty($list->type->name) ? $list->type->name : '');
                $theCollection[$row][] = (isset($list->description) && !empty($list->description) ? $list->description : '');
                $theCollection[$row][] = (isset($list->location) && !empty($list->location) ? $list->location : '');
                $theCollection[$row][] = (isset($list->serial) && !empty($list->serial) ? $list->serial : '');
                $theCollection[$row][] = (isset($list->barcode) && !empty($list->barcode) ? $list->barcode : '');
                $theCollection[$row][] = (isset($list->life) && !empty($list->life) ? ($list->life == 1 ? $list->life.' Year' : $list->life.' Years') : '');

                $row += 1;
            endforeach;
        endif;

        $report_title = 'Transactions_'.date('d_m_Y', strtotime($startDate)).'_to_'.date('d_m_Y', strtotime($endDate)).'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function printRegisters(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);
        $type = (isset($request->type) && $request->type > 0 ? $request->type : 0);
        $queryDates = (isset($request->queryDate) && !empty($request->queryDate) && strlen($request->queryDate) == 23 ? explode(' - ', $request->queryDate) : []);
        $startDate = (isset($queryDates[0]) && !empty($queryDates[0]) ? date('Y-m-d', strtotime($queryDates[0])) : '');
        $endDate = (isset($queryDates[1]) && !empty($queryDates[1]) ? date('Y-m-d', strtotime($queryDates[1])) : '');

        $user = User::find(auth()->user()->id);
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $query = AccAssetRegister::with('trans', 'type');
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('description','LIKE','%'.$queryStr.'%');
                $q->orWhere('location','LIKE','%'.$queryStr.'%');
                $q->orWhere('serial','LIKE','%'.$queryStr.'%');
                $q->orWhere('barcode','LIKE','%'.$queryStr.'%');
            });
        endif;
        if(!empty($startDate) && !empty($endDate)):
            $query->whereHas('trans', function($q) use($startDate, $endDate){
                $q->whereBetween('transaction_date_2', [$startDate, $endDate]);
            });
        endif;
        if($type > 0): $query->where('acc_asset_type_id', $type); endif;
        if($status == 3):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
        endif;

        $report_title = 'Assets Register';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 65px !important; margin-right:65px !important; }

                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                .headerTable tr td{vertical-align: top; padding: 0; line-height: 13px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.reportTitle{font-size: 16px; line-height: 16px; font-weight: bold;}

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
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="2" class="reportTitle">'.$report_title.'</td>';
                            $PDFHTML .= '<td rowspan="3" class="text-right"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Date</td>';
                            $PDFHTML .= '<td>'.(!empty($startDate) ? date('jS M, Y', strtotime($startDate)) : '').(!empty($endDate) ? ' - '.date('jS M, Y', strtotime($endDate)) : '').'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Cereated By</td>';
                            $PDFHTML .= '<td>';
                                $PDFHTML .= (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
                                $PDFHTML .= '<br/>'.date('jS M, Y').' at '.date('h:i A');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="table table-bordered table-sm attenRateReportTable">';
                    $PDFHTML .= '<thead>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th>Transaction</th>';
                            $PDFHTML .= '<th>Supplier</th>';
                            $PDFHTML .= '<th>Price</th>';
                            $PDFHTML .= '<th>Type</th>';
                            $PDFHTML .= '<th>Description</th>';
                            $PDFHTML .= '<th>Location</th>';
                            $PDFHTML .= '<th>Serial</th>';
                            $PDFHTML .= '<th>Barcode</th>';
                            $PDFHTML .= '<th>Life Span</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</thead>';
                    $PDFHTML .= '<tbody>';
                        $assets = $query->get();
                        if($assets->count() > 0):
                            foreach($assets as $list):
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td>';
                                        $PDFHTML .= (isset($list->trans->transaction_date_2) && !empty($list->trans->transaction_date_2) ? date('jS M, Y', strtotime($list->trans->transaction_date_2)) : '');
                                        $PDFHTML .= (isset($list->trans->transaction_code) && !empty($list->trans->transaction_code) ? '<br/>'.$list->trans->transaction_code : '');
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>'.(isset($list->trans->detail) && !empty($list->trans->detail) ? $list->trans->detail : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->trans->transaction_amount) && $list->trans->transaction_amount > 0 ? $list->trans->transaction_amount : '0.00').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->type->name) && !empty($list->type->name) ? $list->type->name : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->description) && !empty($list->description) ? $list->description : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->location) && !empty($list->location) ? $list->location : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->serial) && !empty($list->serial) ? $list->serial : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->barcode) && !empty($list->barcode) ? $list->barcode : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($list->life) && !empty($list->life) ? ($list->life == 1 ? $list->life.' Year' : $list->life.' Years') : '').'</td>';
                                $PDFHTML .= '</tr>';
                            endforeach;
                        endif;
                    $PDFHTML .= '</tbody>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
