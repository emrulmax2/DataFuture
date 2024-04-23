<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use App\Models\AccCategory;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index($bank){
        return view('pages.accounts.storage.index', [
            'title' => 'Accounts Storage - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Storage', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->orderBy('bank_name', 'ASC')->get(),
            'bank' => AccBank::find($bank),
            'in_categories' => $this->catTree(0, 0),
            'out_categories' => $this->catTree(0, 1),
        ]);
    }

    public function catTree($id = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->orderBy('category_name', 'ASC')->get();

        if($categories):
            foreach ($categories as $cat):
                $categs[$cat['id']]['category_name'] = str_repeat('|&nbsp;&nbsp;&nbsp;', $level-1) . '|__'. $cat['category_name'];
                $categs[$cat['id']]['id'] = $cat['id'];
                $categs[$cat['id']]['status'] = $cat['status'];
    
                $this->catTree($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }
}
