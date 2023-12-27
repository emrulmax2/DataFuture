<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\VenueRequest;
use App\Http\Requests\VenueUpdateRequest;
use App\Models\DatafutureField;
use App\Models\Venue;
use App\Models\User;
use App\Models\VenueIpAddress;
use Illuminate\Support\Str;


class VenueController extends Controller
{
    public function index()
    {
        return view('pages.settings.venues.index', [
            'title' => 'Venues - LCC Data Future Managment',
            'subtitle' => 'Campus Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Venues', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = Venue::count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = Venue::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $ipAddress = VenueIpAddress::where('venue_id', $list->id)->pluck('ip')->toArray();
                $ip_addresses = (!empty($ipAddress) ? implode(', ', $ipAddress) : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'idnumber'=> $list->idnumber,
                    'ukprn'=> $list->ukprn,
                    'postcode'=> $list->postcode,
                    'ip' => $ip_addresses,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(VenueRequest $request){
        $ip_addresses = (isset($request->ip_addresses) && !empty($request->ip_addresses) ? Str::of($request->ip_addresses)->explode('|') : []);
        $data = Venue::create([
            'name'=> $request->name,
            'idnumber'=> $request->idnumber,
            'ukprn'=> $request->ukprn,
            'postcode'=> $request->postcode,
            'created_by' => auth()->user()->id
        ]);
        if(!empty($ip_addresses)):
            foreach($ip_addresses as $ip):
                $datas = VenueIpAddress::create([
                    'venue_id'=> $data->id,
                    'ip'=> $ip,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        return response()->json($data);
    }

    public function edit($id){
        $data = Venue::find($id);
        $ipAddress = VenueIpAddress::where('venue_id', $id)->pluck('ip')->toArray();
        $data['ip_addresses'] = (!empty($ipAddress) ? implode('|', $ipAddress) : '');

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(VenueUpdateRequest $request, Venue $dataId){
        $ip_addresses = (isset($request->ip_addresses) && !empty($request->ip_addresses) ? Str::of($request->ip_addresses)->explode('|') : []);
        $data = Venue::where('id', $request->id)->update([
            'name'=> $request->name,
            'idnumber'=> $request->idnumber,
            'ukprn'=> $request->ukprn,
            'postcode'=> $request->postcode,
            'updated_by' => auth()->user()->id
        ]);

        $ipDel = VenueIpAddress::where('venue_id', $request->id)->forceDelete();
        if(!empty($ip_addresses)):
            foreach($ip_addresses as $ip):
                $datas = VenueIpAddress::create([
                    'venue_id'=> $request->id,
                    'ip'=> $ip,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        return response()->json($data);
    }

    public function show($id)
    {
        return view('pages.settings.venues.show', [
            'title' => 'Venues - LCC Data Future Managment',
            'subtitle' => 'Campus Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Venues', 'href' => route('venues')],
                ['label' => 'Venues Details', 'href' => 'javascript:void(0);']
            ],
            'venue' => Venue::find($id),
            'df_fields' => DatafutureField::whereIn('datafuture_field_category_id', [3])->orderBy('name', 'ASC')->get()
        ]);
    }

    public function destroy($id){
        $data = Venue::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Venue::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
