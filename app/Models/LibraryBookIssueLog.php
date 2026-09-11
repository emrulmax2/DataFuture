<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing that happened to a library issue.
 *
 * Append-only: rows are never edited or removed, so the trail stays trustworthy
 * even when the issue itself is later cancelled or deleted.
 */
class LibraryBookIssueLog extends Model
{
    protected $table = 'library_book_issue_logs';

    protected $fillable = [
        'library_book_issue_id', 'action', 'from_status', 'to_status',
        'performed_by', 'performed_by_type', 'performed_by_name', 'note',
    ];

    public function issue()
    {
        return $this->belongsTo(LibraryBookIssue::class, 'library_book_issue_id');
    }
}
