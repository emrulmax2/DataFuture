<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class AccSettingController extends Controller
{
    public function index()
    {
        return view('pages.settings.accounts.account', [
            'title' => 'Site Settings - London Churchill College',
            'subtitle' => 'Account Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Account Settings', 'href' => 'javascript:void(0);']
            ],
            'opt' => Option::where('category', 'ACC_SETTINGS')->pluck('value', 'name')->toArray()
        ]);
    }

    public function update(Request $request){
        $category = $request->category;
        $allFields = $request->except(['category']);

        foreach($allFields as $name => $value):
            $row = Option::updateOrCreate([ 'category' => $category, 'name' => $name ], [
                'category' => $category,
                'name' => $name,
                'value' => $value,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id
            ]);
        endforeach;

        return response()->json(['msg' => 'Option value successfully updated'], 200);
    }
}
