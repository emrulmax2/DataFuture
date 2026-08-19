<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A member of staff who leads a class group for one term.
 *
 * Assigned from the Class Plan tree and read back by the Group Leader
 * dashboard, which pivots on (user_id, term_declaration_id).
 */
class GroupLeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'term_declaration_id',
        'course_id',
        'group_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function term()
    {
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /** Whether this user leads any group at all — half the dashboard tile's gate. */
    public static function isLeader($userId): bool
    {
        return static::where('user_id', $userId)->exists();
    }

    /**
     * Whether the signed-in user may take one of the four group-leader actions.
     *
     * `view`   the Group Leader dashboard tile and its screen, and the
     *          assignment history on the plan tree
     * `add`    assigning staff to a group that has no leader yet
     * `edit`   changing a group's existing leaders
     * `delete` removing a leader from a group
     *
     * The parent `group_leader` switch is the umbrella: the privilege screen
     * disables the four children while it is off, and this mirrors that on the
     * server so a hand-crafted permission row cannot slip past the UI.
     */
    public static function can(string $action): bool
    {
        $user = auth()->user();
        if (empty($user)) {
            return false;
        }

        $priv = $user->priv();

        return !empty($priv['group_leader'])
            && $priv['group_leader'] != 0
            && !empty($priv['group_leader_'.$action])
            && $priv['group_leader_'.$action] != 0;
    }
}
