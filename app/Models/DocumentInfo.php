<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DocumentInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['download_url', 'tags_html'];

    protected $fillable = [
        'parent_id',
        'document_folder_id',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'expire_at',
        'reminder_at',
        'description',
        'file_type',
        'publish_date',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getDownloadUrlAttribute(){
        if ($this->current_file_name !== null && $this->path !== null && Storage::disk('local')->exists('public/file-manager/'.$this->path.'/'.$this->current_file_name)) {
            return Storage::disk('local')->url('public/file-manager/'.$this->path.'/'.$this->current_file_name);
        } else {
            return false;
        }
    }

    public function setExpireAtAttribute($value) {  
        $this->attributes['expire_at'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setPublishDateAttribute($value) {  
        $this->attributes['publish_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getPublishDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function permission(){
        return $this->hasMany(DocumentInfoHasEmployees::class, 'document_info_id', 'id');
    }

    public function admins(){
        return $this->hasMany(DocumentInfoHasEmployees::class, 'document_info_id', 'id')->where('document_role_and_permission_id', 1);
    }

    public function tags(){
        return $this->hasMany(DocumentInfoTag::class, 'document_info_id', 'id');
    }

    public function getTagsHtmlAttribute(){
        $html = '';
        $infoTags = DocumentInfoTag::where('document_info_id', $this->id)->get();
        if($infoTags->count() > 0){
            foreach($infoTags as $tag):
                $html .= '<div class="fileTag">';
                    $html .= '<span>'.$tag->tag->name.'</span>';
                    $html .= '<button type="button" class="removeTag"><i data-lucide="x" class="w-3 h-3"></i></button>';
                    $html .= '<input type="hidden" name="tag_ids[]" value="'.$tag->document_tag_id.'"/>';
                $html .= '</div>';
            endforeach;
        }

        return $html;
    }

    public function childrens(){
        return $this->hasMany(DocumentInfo::class, 'parent_id', 'id');
    }
}
