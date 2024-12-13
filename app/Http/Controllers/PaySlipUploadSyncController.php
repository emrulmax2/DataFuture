<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayslipSyncUploadUpdateRequest;
use App\Models\PaySlipUploadSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaySlipUploadSyncController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PayslipSyncUploadUpdateRequest $request)
    {
        $ids = $request->id;
        $employee_ids = $request->employee_id;

        foreach ($ids as $index => $id) {
            PaySlipUploadSync::updateOrCreate(
                [
                    'id' => $id
                ],
                [
                    'employee_id' => $employee_ids[$index],
                    'file_transffered_at' => now(),
                    'file_transffered' => 1,
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );
        }       

        return response()->json([
            'message' => 'Pay slip sync updated successfully',
        ]);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(PaySlipUploadSync $payslip_upload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaySlipUploadSync $payslip_upload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaySlipUploadSync $payslip_upload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaySlipUploadSync $payslip_upload)
    {

        // Assuming the file path is stored in a column named 'file_path'
        $filePath = $payslip_upload->file_path;

        // Delete the file from storage
        if (Storage::exists($filePath)) {

            Storage::delete($filePath);

        }

        // Delete the record from the database
        $payslip_upload->forceDelete();

        return response()->json(['message' => 'Payslip deleted successfully.']);

    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function restore(PaySlipUploadSync $payslip_upload)
    {
        //
    }

    public function deleteResultBulk(Request $request)
    {
        
        $resultIds = array_filter(array_unique($request->input('ids')));
        
        PaySlipUploadSync::whereIn('id', $resultIds)->get()->each(function($result){
            if (Storage::exists($result->file_path)) {
                Storage::delete($result->file_path);
            }
        });
        
        
        $baseResultDelete = PaySlipUploadSync::whereIn('id', $resultIds)->delete();

        if($baseResultDelete)
            return response()->json(['message' => 'Result successfully deleted.'], 200);
        else
            return response()->json(['message' => 'Result could not be deleted'], 302);
        
    }


    public function downloadPaySlip($id)
    {
        $paySlip = PaySlipUploadSync::find($id);

        if (Storage::exists($paySlip->file_path)) {
            return Storage::download($paySlip->file_path);
        }

        return response()->json(['message' => 'File not found.'], 404);
    }
   
    
}
