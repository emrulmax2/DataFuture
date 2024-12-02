<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccAssetRegister;
use App\Models\AccAssetType;
use App\Models\AccBank;
use Illuminate\Http\Request;

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
                    'transaction_date_2' => (isset($list->trans->transaction_date_2) && !empty($list->trans->transaction_date_2) ? date('jS M, Y', strtotime($list->trans->transaction_date_2)) : ''),
                    'transaction_code' => (isset($list->trans->transaction_code) && !empty($list->trans->transaction_code) ? $list->trans->transaction_code : ''),
                    'detail' => (isset($list->trans->detail) && !empty($list->trans->detail) ? $list->trans->detail : ''),
                    'transaction_amount' => (isset($list->trans->transaction_amount) && $list->trans->transaction_amount > 0 ? '£'.number_format($list->trans->transaction_amount, 2) : '£0.00'),
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

    public function update(Request $request){
        $id = $request->id;
        $description = $request->description;
        $acc_asset_type_id = $request->acc_asset_type_id;
        $location = (isset($request->location) && !empty($request->location) ? $request->location : null);
        $serial = (isset($request->serial) && !empty($request->serial) ? $request->serial : null);
        $barcode = (isset($request->barcode) && !empty($request->barcode) ? $request->barcode : random_int(10000000, 99999999));
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
}
