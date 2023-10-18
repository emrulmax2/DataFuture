<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getAddress(Request $request){
        $address = Address::find($request->address_id);

        return response()->json(['res' => $address], 200);
    }

    public function store(AddressRequest $request){
        $address_id = $request->address_id;
        $address_line_1 = $request->student_address_address_line_1;
        $address_line_2 = (isset($request->student_address_address_line_2) && !empty($request->student_address_address_line_2) ? $request->student_address_address_line_2 : null);
        $state = (isset($request->student_address_state_province_region) && !empty($request->student_address_state_province_region) ? $request->student_address_state_province_region : null);
        $city = $request->student_address_city;
        $post_code = $request->student_address_postal_zip_code;
        $country = $request->student_address_country;

        $res = [];
        $data = [];
        $data['address_line_1'] = $address_line_1;
        $data['address_line_2'] = $address_line_2;
        $data['state'] = $state;
        $data['post_code'] = $post_code;
        $data['city'] = $city;
        $data['country'] = $country;
        $data['active'] = 1;
        $data['created_by'] = auth()->user()->id;

        if($address_id > 0){
            $theAddr = Address::find($address_id);
            if(
                $address_line_1 == $theAddr->address_line_1 && $address_line_2 == $theAddr->address_line_2 && 
                $state == $theAddr->state && $city == $theAddr->city && $post_code == $theAddr->post_code && 
                $country == $theAddr->country
            ):
                $res['id'] = $address_id;
            else:
                Address::where('id', $address_id)->update(['active' => 0, 'updated_by' => auth()->user()->id]);

                $address = Address::create($data);
                $insertId = $address->id;

                $res['id'] = $insertId;
            endif;
        }else{
            $address = Address::create($data);
            $insertId = $address->id;

            $res['id'] = $insertId;
        }

        return response()->json(['res' => $res], 200);
    }
}
