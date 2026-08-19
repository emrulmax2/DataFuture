<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One logged conversation with a student about their attendance.
 *
 * Append-only, like the assignment log: a leader records what was said and
 * why the student was away, and the worklist reads the newest row to decide
 * whether the student still counts as "not contacted".
 */
class GroupLeaderContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'term_declaration_id',
        'method',
        'reason',
        'note',
        'follow_up_date',
        'logged_by',
        'logged_by_name',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    /** The methods and reasons the drawer offers, and the only ones accepted. */
    public const METHODS = ['Phone call', 'Email', 'In person', 'SMS', 'No response'];

    public const REASONS = [
        'Illness',
        'Work commitment',
        'Childcare / family',
        'Transport',
        'Personal / wellbeing',
        'Unauthorised - no reason',
        'Other',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
