<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayslipSyncUploadUpdateRequest;
use App\Jobs\ProcessSendPaySlipEmail;
use App\Models\PaySlipUploadSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $ids = $request->id ?? [];
        $employee_ids = $request->employee_id ?? [];
        
        foreach ($ids as $index => $id) {
            $paySlipUploadSync = PaySlipUploadSync::updateOrCreate(
                [
                    'id' => $id
                ],
                [
                    'employee_id' => $employee_ids[$index] ?? null,
                    'file_transffered_at' => now(),
                    'file_transffered' => 1,
                    'updated_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );

            if ($paySlipUploadSync && !empty($paySlipUploadSync->employee_id)) {
                ProcessSendPaySlipEmail::dispatch($paySlipUploadSync->id);
            }
        }       

        return response()->json([
            'message' => 'Pay slip sync updated successfully',
            'ids' => array_values(array_filter($ids)),
            'total' => count(array_filter($ids)),
        ]);
        
    }

    public function emailProgress(Request $request)
    {
        $ids = array_values(array_filter($request->input('ids', [])));

        if (empty($ids)) {
            return response()->json([
                'total' => 0,
                'completed' => 0,
                'percentage' => 0,
            ]);
        }

        $baseQuery = PaySlipUploadSync::whereIn('id', $ids)
            ->whereNotNull('employee_id')
            ->whereHas('employee', function ($query) {
                $query->whereNotNull('email')->where('email', '!=', '');
            });

        $total = (clone $baseQuery)->count();
        $completed = (clone $baseQuery)->whereNotNull('email_transferred_at')->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return response()->json([
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
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
            $location = $this->resolveStorageLocation($payslip_upload);

            if (!empty($location)) {
                Storage::disk($location['disk'])->delete($location['path']);
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
                $location = $this->resolveStorageLocation($result);
                if (!empty($location)) {
                    Storage::disk($location['disk'])->delete($location['path']);
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

        $location = $this->resolveStorageLocation($paySlip);
        if (!empty($location)) {
            $fileName = $paySlip->file_name ?: basename($location['path']);
            return response()->streamDownload(function () use ($location) {
                echo Storage::disk($location['disk'])->get($location['path']);
            }, $fileName);
        }

        return response()->json(['message' => 'File not found.'], 404);
    }

    protected function resolveStorageLocation(?PaySlipUploadSync $paySlip): array
    {
        if (!$paySlip || empty($paySlip->file_path)) {
            return [];
        }

        $filePath = $paySlip->file_path;
        $s3Base = rtrim((string) config('filesystems.disks.s3.url', ''), '/');
        $publicBase = rtrim((string) config('filesystems.disks.public.url', ''), '/');

        if ($s3Base !== '' && Str::startsWith($filePath, $s3Base)) {
            $path = ltrim(Str::after($filePath, $s3Base), '/');
            return $path !== '' ? ['disk' => 's3', 'path' => $path] : [];
        }

        if ($publicBase !== '' && Str::startsWith($filePath, $publicBase)) {
            $path = ltrim(Str::after($filePath, $publicBase), '/');
            return $path !== '' ? ['disk' => 'local', 'path' => $path] : [];
        }

        if (Str::startsWith($filePath, ['http://', 'https://'])) {
            return [];
        }

        return $filePath !== '' ? ['disk' => 'local', 'path' => $filePath] : [];
    }
   
    
}
