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

class ProcessExtractedFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $extractPath;
    protected $tempPath;
    protected $dirName;
    protected $type;
    protected $holiday_year_Id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tempPath, $dirName, $type, $holiday_year_Id)
    {
        $this->tempPath = $tempPath; // path relative to storage/app
        $this->dirName = $dirName;
        $this->type = $type;
        $this->holiday_year_Id = $holiday_year_Id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Extract the uploaded ZIP (stored under storage/app/...) into a unique temp folder
        $zipPath = storage_path('app/' . $this->tempPath);
        $extractPath = storage_path('app/temp/extracted/' . uniqid());

        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            // can't open zip, cleanup and exit
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }
            return;
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Get the list of directories in the extracted path, excluding __MACOSX
        $directories = $this->getDirectories($extractPath);

        // Get all extracted files
        foreach ($directories as $path) {
            $directoryName = basename($path);
            $files = File::files($extractPath . DIRECTORY_SEPARATOR . $directoryName);
            
            // Loop through the files and store them in the desired location
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    // Store the file in the 'public' disk with its original name
                    $fileName = $file->getFilename();
                    // Get the original name without extension
                    $originalNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
                    $destinationPath = 'employee_payslips/'.$this->dirName ;// Define the destination path
                    
                    Storage::disk('public')->put($destinationPath . '/' . $fileName, File::get($file));
                    
                    // Get the file path after storage
                    $filePath = Storage::disk('public')->url($destinationPath . '/' . $fileName);
                    
                    $paySlipUploadSync = [];
                    $employeeList =Employee::all();

                    foreach($employeeList as $employee) {
                        // find employee first_name and last_name from $fileName string
                        // Extract first_name and last_name from the filename
                        // we change it now it will be ni_number based matching
                        // remove string space or hipen from originalNameWithoutExtension
                        $fileNameWithoutAnyHipen = preg_replace('/[\s-]+/', '', strtoupper(trim($originalNameWithoutExtension)));
                        $employeeNINumber = preg_replace('/[\s-]+/', '', strtoupper(trim($employee->ni_number)));
                        
                        if($employeeNINumber == $fileNameWithoutAnyHipen){

                            $employeeFound = $employee->id;
                            break;

                        } else {

                            $employeeFound = 0;
                            $paySync=PaySlipUploadSync::all();
                            foreach($paySync as $pay) {
            
                                if($pay->file_name == $fileName && $pay->employee_id != null) {
            
                                    $employeeFound = $pay->employee_id;
                                    break;
                                }
                            }
                            
                        }   
                    
                        
                    }

                    // payslipuploadSync table data insert if file_name and month_year not exist
                    $paySlipUploadSync = PaySlipUploadSync::updateOrCreate(
                        [
                            
                            'file_name' => $fileName,
                            'month_year' => $this->dirName,

                        ],[
                        'employee_id' => ($employeeFound) ? $employeeFound : NULL,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'holiday_year_id' => $this->holiday_year_Id,
                        'month_year' => $this->dirName,
                        'type' => isset($type) ? $type : 'Payslips',
                        'is_file_exist' => ($employeeFound) ? 1 : 0,
                        'file_transffered' => 0,
                        'file_transffered_at' => null,
                        'is_file_uploaded' => 1,

                    ]);
                    if($paySlipUploadSync){
                        $updated = true;
                    }
                
                }
            }
            break;
        }

        // cleanup extracted files and uploaded zip
        try {
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }
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