<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.index', [
            'title' => 'Site Settings - LCC Data Future Managment',
            'subtitle' => 'Site Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => 'javascript:void(0);']
            ],
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray()
        ]);
    }

    public function addressApi()
    {
        return view('pages.settings.address-capture', [
            'title' => 'Site Settings - LCC Data Future Managment',
            'subtitle' => 'Site Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Address API', 'href' => 'javascript:void(0);'],
            ],
            'opt' => Option::where('category', 'ADDR_ANYWHR_API')->pluck('value', 'name')->toArray()
        ]);
    }

    public function smsApi()
    {
        return view('pages.settings.sms-api', [
            'title' => 'SMS Settings - LCC Data Future Managment',
            'subtitle' => 'SMS Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'SMS API', 'href' => 'javascript:void(0);'],
            ],
            'opt' => Option::where('category', 'SMS')->pluck('value', 'name')->toArray()
        ]);
    }

    public function update(Request $request){
        $category = $request->category;
        $allFields = $request->except(['file', 'site_logo', 'site_favicon', 'category']);

        
        if(isset($request->site_logo)):
            $siteLogoRow = Option::where('category', $category)->where('name', 'site_logo')->first();
            $site_logo_name = (isset($siteLogoRow->value) && !empty($siteLogoRow->value) ? $siteLogoRow->value : '');
            if($request->hasFile('site_logo')):
                if(isset($siteLogoRow->value) && !empty($siteLogoRow->value)):
                    if(Storage::disk('local')->exists('public/'.$siteLogoRow->value)):
                        Storage::disk('local')->delete('public/'.$siteLogoRow->value);
                    endif;
                endif;

                $site_logo = $request->file('site_logo');
                $imageName = 'company_logo.' . $site_logo->getClientOriginalExtension();
                $path = $site_logo->storeAs('public/', $imageName);

                $site_logo_name = $imageName;
            endif;
            $allFields['site_logo'] = $site_logo_name;
            Cache::forever('site_logo', $site_logo_name);
        endif;

        if(isset($request->site_favicon)):
            $siteFaviconRow = Option::where('category', $category)->where('name', 'site_favicon')->first();
            $site_favicon_name = (isset($siteFaviconRow->value) && !empty($siteFaviconRow->value) ? $siteFaviconRow->value : '');
            if($request->hasFile('site_favicon')):
                if(isset($siteFaviconRow->value) && !empty($siteFaviconRow->value)):
                    if(Storage::disk('local')->exists('public/'.$siteFaviconRow->value)):
                        Storage::disk('local')->delete('public/'.$siteFaviconRow->value);
                    endif;
                endif;

                $site_favicon = $request->file('site_favicon');
                $imageName = 'company_favicon.' . $site_favicon->getClientOriginalExtension();
                $path = $site_favicon->storeAs('public/', $imageName);

                $site_favicon_name = $imageName;
            endif;
            $allFields['site_favicon'] = $site_favicon_name;
            Cache::forever('site_favicon', $site_favicon_name);
        endif;

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
