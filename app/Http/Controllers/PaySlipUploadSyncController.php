<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayslipSyncUploadUpdateRequest;
use App\Jobs\ProcessSendPaySlipEmail;
use App\Models\PaySlipUploadSync;
use App\Services\PayslipManagementService;
use Illuminate\Support\Facades\DB;
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
            $exists = $id ? PaySlipUploadSync::where('id', $id)->exists() : false;

            $data = [
                'employee_id' => $employee_ids[$index] ?? null,
                'file_transffered_at' => now(),
                'file_transffered' => 1,
                'updated_at' => now(),
                'updated_by' => auth()->id(),
            ];

            if (!$exists) {
                $data['created_by'] = auth()->id();
            }

            $paySlipUploadSync = PaySlipUploadSync::updateOrCreate(
                [
                    'id' => $id
                ],
                $data
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
        if (!PayslipManagementService::canManage()) {
            return response()->json(['message' => 'You do not have permission to delete payslips.'], 403);
        }

        if (!$this->removePaySlip($payslip_upload)) {
            return response()->json(['message' => 'The payslip could not be deleted. Please try again.'], 500);
        }

        return response()->json(['message' => 'Payslip deleted successfully.']);
    }

    /**
     * Put a new PDF in place of the one stored for this payslip. The record,
     * its month, type and file name stay as they are; only the file changes.
     */
    public function replace(Request $request, PaySlipUploadSync $payslip_upload)
    {
        if (!PayslipManagementService::canManage()) {
            return response()->json(['message' => 'You do not have permission to replace payslips.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $location = $this->resolveStorageLocation($payslip_upload);
        if (empty($location)) {
            return response()->json(['message' => 'The stored file for this payslip could not be located.'], 422);
        }

        try {
            DB::transaction(function () use ($request, $payslip_upload, $location) {
                $payslip_upload->update([
                    'is_file_uploaded' => 1,
                    'updated_by' => auth()->id(),
                ]);

                // The line is written first and the file last, inside one
                // transaction: if either fails neither is kept.
                if (!PayslipManagementService::log('replaced', $payslip_upload)) {
                    throw new \RuntimeException('The action could not be logged.');
                }

                $stream = fopen($request->file('file')->getRealPath(), 'r');
                try {
                    Storage::disk($location['disk'])->put($location['path'], $stream);
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'The payslip could not be replaced. Nothing was changed.'], 500);
        }

        return response()->json(['message' => 'Payslip replaced successfully.']);
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
        if (!PayslipManagementService::canManage()) {
            return response()->json(['message' => 'You do not have permission to delete payslips.'], 403);
        }

        $resultIds = array_filter(array_unique($request->input('ids', [])));

        $deleted = 0;
        foreach (PaySlipUploadSync::whereIn('id', $resultIds)->get() as $result) {
            if ($this->removePaySlip($result)) {
                $deleted++;
            }
        }

        if ($deleted > 0)
            return response()->json(['message' => 'Result successfully deleted.'], 200);
        else
            return response()->json(['message' => 'Result could not be deleted'], 302);

    }

    /**
     * Delete the record and its stored file. The log line and the record go
     * together or not at all; the file is removed once they are committed.
     */
    protected function removePaySlip(PaySlipUploadSync $payslip): bool
    {
        $location = $this->resolveStorageLocation($payslip);

        try {
            DB::transaction(function () use ($payslip) {
                if (!PayslipManagementService::log('deleted', $payslip)) {
                    throw new \RuntimeException('The action could not be logged.');
                }

                $payslip->forceDelete();
            });
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        if (!empty($location)) {
            try {
                Storage::disk($location['disk'])->delete($location['path']);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return true;
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
