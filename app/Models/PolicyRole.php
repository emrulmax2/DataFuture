<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A job role as HR assigns policy assessments by it — "Lecturer",
 * "Admissions Officer", "Finance" — with the policies ticked for it. Picking
 * the role when assigning selects those policies.
 *
 * This is the Policy Assessments feature's own role. It has nothing to do
 * with App\Models\Role and the roles table of the permission system.
 *
 * A role is a shortcut for choosing policies, not a live link: an assignment
 * remembers the role it came through (policy_assignments.policy_role_id), but
 * changing a role's ticks later never changes assignments already made.
 * Archiving a role (soft delete) keeps its ticks, so restoring it brings
 * them back.
 */
class PolicyRole extends Model
{
    use HasFactory, SoftDeletes;

    /** The pivot table holding a role's ticked policies. */
    const PIVOT_TABLE = 'policy_role_documents';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Every policy ticked for this role that has not been deleted — switched
     * on or not. This is what the role's edit screen shows as ticked; what
     * the role actually assigns is activePolicies().
     */
    public function policies(){
        return $this->belongsToMany(PolicyDocument::class, self::PIVOT_TABLE, 'policy_role_id', 'policy_document_id')->withPivot('created_by')->withTimestamps();
    }

    /**
     * The policies this role assigns: ticked, switched on, not deleted, in a
     * category that is not archived — the same liveness as the policy part of
     * PolicyAssignment::scopeLive(). Ordered as HR lists them: category
     * (sort order), then policy sort order, then title.
     *
     * The liveness and the ordering are plain where / order-by clauses (no
     * join), so this also works under with(), withCount() and whereHas().
     */
    public function activePolicies(){
        return $this->belongsToMany(PolicyDocument::class, self::PIVOT_TABLE, 'policy_role_id', 'policy_document_id')
            ->withPivot('created_by')
            ->withTimestamps()
            ->where('policy_documents.is_active', 1)
            ->whereHas('category')
            ->orderBy(PolicyCategory::select('policy_categories.sort_order')->whereColumn('policy_categories.id', 'policy_documents.policy_category_id')->limit(1), 'ASC')
            ->orderBy('policy_documents.policy_category_id', 'ASC')
            ->orderBy('policy_documents.sort_order', 'ASC')
            ->orderBy('policy_documents.title', 'ASC')
            ->orderBy('policy_documents.id', 'ASC');
    }

    /** Assignments that were made through this role. */
    public function assignments(){
        return $this->hasMany(PolicyAssignment::class, 'policy_role_id', 'id');
    }

    /** Roles HR can pick when assigning: switched on (and, as ever, not archived). */
    public function scopeActive($query){
        return $query->where('policy_roles.is_active', 1);
    }
}
