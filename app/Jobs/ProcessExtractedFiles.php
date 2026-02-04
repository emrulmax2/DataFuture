<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\PaySlipUploadSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ProcessExtractedFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $extractPath;
    protected $tempPath;
    protected $dirName;
    protected $type;
    protected $holiday_year_Id;
    protected $employeeMap;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tempPath, $dirName, $type, $holiday_year_Id,$employeeMap)
    {
        $this->tempPath = $tempPath; // path relative to storage/app
        $this->dirName = $dirName;
        $this->type = $type;
        $this->holiday_year_Id = $holiday_year_Id;
        $this->employeeMap = $employeeMap;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // The controller stored the uploaded ZIP locally (storage/app/<tempPath>).
        // Transfer the local ZIP to S3, then extract locally for processing.
        $localZipPath = storage_path('app/' . $this->tempPath);

        if (!File::exists($localZipPath)) {
            // nothing to do
            return;
        }

        // (no longer uploading the original ZIP to S3 — we'll extract locally
        // and transfer extracted files to S3 directly)

        $extractPath = storage_path('app/temp/extracted/' . uniqid());
        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($localZipPath) !== TRUE) {
            // can't open zip, cleanup local zip and exit
            if (File::exists($localZipPath)) {
                File::delete($localZipPath);
            }
            return;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Get the list of directories in the extracted path, excluding __MACOSX
        $directories = $this->getDirectories($extractPath);



        // $paySyncMap = [];
        // $paySyncItems = PaySlipUploadSync::whereNotNull('employee_id')
        //     ->get(['file_name', 'employee_id']);
        // foreach ($paySyncItems as $pay) {
        //     if (!empty($pay->file_name)) {
        //         $paySyncMap[$pay->file_name] = $pay->employee_id;
        //     }
        // }

        // Get all extracted files
        foreach ($directories as $path) {
            $directoryName = basename($path);
            $files = File::files($extractPath . DIRECTORY_SEPARATOR . $directoryName);
            
            // Loop through the files and store them in the desired location
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    // Store the file in the 'public' disk with the month suffix appended to its name
                    $fileName = $file->getFilename();
                    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    // keep original name without extension for NI matching
                    $originalNameWithoutExtension = $baseName;

                    // build suffixed filename (e.g., payslip-2024-08.pdf)
                    $fileNameWithSuffix = $baseName . '-' . $this->dirName . ($extension ? '.' . $extension : '');

                    $destinationPath = 'public/employee_payslips/'.$this->dirName; // Define the destination path on S3

                    // Stream upload to S3 to avoid loading whole file into memory
                    $localRealPath = $file->getRealPath();
                    if ($localRealPath && File::exists($localRealPath)) {
                        $stream = fopen($localRealPath, 'r');
                        Storage::disk('s3')->put($destinationPath . '/' . $fileNameWithSuffix, $stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    } else {
                        // fallback to reading file contents
                        Storage::disk('s3')->put($destinationPath . '/' . $fileNameWithSuffix, File::get($file));
                    }

                    // Get the file path (S3 URL) after storage
                    $filePath = Storage::disk('s3')->url($destinationPath . '/' . $fileNameWithSuffix);
                    
                    $paySlipUploadSync = [];
                    // normalize NI number from filename and resolve employee
                    $fileNameWithoutAnyHipen = preg_replace('/[\s-]+/', '', strtoupper(trim($originalNameWithoutExtension)));
                    $employeeFound = $this->employeeMap[$fileNameWithoutAnyHipen] ?? 0;

                    // fallback to previously mapped filename if NI is ambiguous or not found
                    // if (!$employeeFound) {
                    //     if (isset($paySyncMap[$fileName])) {
                    //         $employeeFound = $paySyncMap[$fileName];
                    //     } elseif (isset($paySyncMap[$fileNameWithSuffix])) {
                    //         $employeeFound = $paySyncMap[$fileNameWithSuffix];
                    //     }
                    // }

                    // payslipuploadSync table data insert if file_name and month_year not exist
                    $paySlipUploadSync = PaySlipUploadSync::updateOrCreate(
                        [
                            'file_name' => $fileNameWithSuffix,
                            'month_year' => $this->dirName,

                        ],[
                        'employee_id' => ($employeeFound) ? $employeeFound : NULL,
                        'file_name' => $fileNameWithSuffix,
                        'file_path' => $filePath,
                        'holiday_year_id' => $this->holiday_year_Id,
                        'month_year' => $this->dirName,
                        'type' => isset($this->type) && !empty($this->type) ? $this->type : 'Payslips',
                        'is_file_exist' => ($employeeFound) ? 1 : 0,
                        'file_transffered' => 0,
                        'file_transffered_at' => null,
                        'is_file_uploaded' => 1,
                        'created_by' => auth()->id(),

                    ]);
                    if($paySlipUploadSync){
                        $updated = true;
                    }
                
                }
            }
            break;
        }

        // cleanup extracted files, local zip and remove original zip from S3
        try {
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            if (File::exists($localZipPath)) {
                File::delete($localZipPath);
            }
            // finished — local extracted files and local zip cleaned up
        } catch (\Exception $e) {
            // ignore cleanup errors
        }
    }

    /**
     * Get the list of directories in the given path, excluding __MACOSX.
     *
     * @param string $path
     * @return array
     */
    protected function getDirectories($path)
    {
        $directories = File::directories($path);
        return array_filter($directories, function ($dir) {
            return basename($dir) !== '__MACOSX';
        });
    }
}