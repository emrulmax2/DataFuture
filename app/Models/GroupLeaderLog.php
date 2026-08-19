<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of the group leader audit trail.
 *
 * Append-only: nothing edits or deletes these rows, which is why the model has
 * no soft deletes and the table has no foreign keys. Names are denormalised on
 * write so the history stays readable after a rename or a disabled account.
 */
class GroupLeaderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'term_declaration_id',
        'course_id',
        'group_id',
        'action',
        'user_name',
        'group_name',
        'performed_by',
        'performed_by_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Records one assignment change.
     *
     * Written against the group node the action was taken on, not against every
     * same-name duplicate, so one click leaves one line in the history.
     */
    public static function record(string $action, User $leader, Group $group, array $scope): void
    {
        $actor = auth()->user();

        static::create([
            'user_id' => $leader->id,
            'academic_year_id' => $scope['academic_year_id'] ?? null,
            'term_declaration_id' => $scope['term_declaration_id'] ?? 0,
            'course_id' => $scope['course_id'] ?? null,
            'group_id' => $group->id,
            'action' => $action,
            'user_name' => $leader->full_name,
            'group_name' => $group->name,
            'performed_by' => $actor->id ?? null,
            'performed_by_name' => $actor->full_name ?? null,
        ]);
    }
}
