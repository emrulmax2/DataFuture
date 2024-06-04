<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['folder_admins', 'folder_permission'];

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'path',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected static function boot() {
        parent::boot();

        static::creating(function ($folder) {
            $slug = Str::slug($folder->name);
            $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            $folder->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }

    public function permission(){
        return $this->hasMany(DocumentFolderPermission::class, 'document_folder_id', 'id');
    }

    public function getFolderPermissionAttribute(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $folderPermission = DocumentFolderPermission::where('document_folder_id', $this->id)->where('employee_id', $employee->id)->get()->first();
        if(isset($folderPermission->id) && $folderPermission->id > 0):
            return $folderPermission->role;
        else:
            return false;
        endif;
    }

    public function getFolderAdminsAttribute(){
        $res = [];
        $folderPermission = DocumentFolderPermission::where('document_folder_id', $this->id)->where('document_role_and_permission_id', 1)->get();
        if(!empty($folderPermission)):
            foreach($folderPermission as $permission):
                $res[$permission->id]['full_name'] = (isset($permission->employee->full_name) && !empty($permission->employee->full_name) ? $permission->employee->full_name : 'Unknown');
                $res[$permission->id]['photo_url'] = (isset($permission->employee->photo_url) && !empty($permission->employee->photo_url) ? $permission->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'));
            endforeach;
        endif;

        return $res;
    }
}
