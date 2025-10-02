<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReportItAllUpload extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['file_image_url'];

    public function reportItAll(){
        return $this->belongsTo(ReportItAll::class,'report_it_all_id','id');
    }

    public function getFileImageUrlAttribute(){
        $reportItAll = $this->reportItAll;
            //get student Uploaded Image check the file is a image or not
            if(isset($reportItAll) && $reportItAll!=null)
            if($this->file_type == 'image' && !empty($this->file_path)):

                if($this->uploaded_to=="S3"):
                    if(isset($reportItAll->student_id) && $reportItAll->student_id > 0):
                        return Storage::disk('s3')->url('public/students/report_it/'.$reportItAll->student_id.'/'.$this->file_name);
                    else:
                        return Storage::disk('s3')->url('public/employees/report_it/'.$reportItAll->employee_id.'/'.$this->file_name);
                    endif;
                else:
                    if(isset($reportItAll->student_id) && $reportItAll->student_id > 0):
                        return Storage::disk('local')->url('public/students/report_it/'.$reportItAll->student_id.'/'.$this->file_name);
                    else:
                        return Storage::disk('local')->url('public/employees/report_it/'.$reportItAll->employee_id.'/'.$this->file_name);
                    endif;
                endif;
            endif;
    }
}
