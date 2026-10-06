<?php

namespace App\Models;

use App\Support\PolicyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One college policy (a PDF on the website) and the rules for its test:
 * pass mark, how many questions are drawn per attempt, how many attempts a
 * member of staff gets (NULL = unlimited) and how long each attempt may take
 * (time_limit_minutes, NULL = untimed).
 */
class PolicyDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'policy_category_id',
        'title',
        'slug',
        'version',
        'description',
        'pdf_url',
        'page_url',
        'thumbnail_url',
        'pass_mark',
        'questions_per_attempt',
        'max_attempts',
        'time_limit_minutes',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pass_mark' => 'integer',
        'questions_per_attempt' => 'integer',
        'max_attempts' => 'integer',
        'time_limit_minutes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function category(){
        return $this->belongsTo(PolicyCategory::class, 'policy_category_id');
    }

    public function questions(){
        return $this->hasMany(PolicyQuestion::class, 'policy_document_id', 'id');
    }

    public function activeQuestions(){
        return $this->hasMany(PolicyQuestion::class, 'policy_document_id', 'id')->where('is_active', 1);
    }

    public function assignments(){
        return $this->hasMany(PolicyAssignment::class, 'policy_document_id', 'id');
    }

    public function badges(){
        return $this->hasMany(PolicyBadge::class, 'policy_document_id', 'id');
    }

    /** The policy roles this policy is ticked for (archived roles excluded). */
    public function roles(){
        return $this->belongsToMany(PolicyRole::class, PolicyRole::PIVOT_TABLE, 'policy_document_id', 'policy_role_id')->withPivot('created_by')->withTimestamps();
    }

    /**
     * How many questions a test on this policy could actually draw from:
     * active, with at least two options and exactly one correct. Zero means
     * the policy cannot be tested yet. With a level, only that level's
     * questions count. Whether a test can run is examReady()'s call, since a
     * test draws from all three levels. An unknown level counts as zero.
     *
     * A list query can preload these (see scopeWithDrawableCounts) to avoid
     * a query per row: drawable_questions_count for the total and
     * drawable_<level>_count per level. A preloaded total is never used to
     * answer for a level.
     */
    public function drawableQuestionCount(?string $level = null): int
    {
        if($level === null):
            if(array_key_exists('drawable_questions_count', $this->attributes)):
                return (int) $this->attributes['drawable_questions_count'];
            endif;

            return (int) $this->questions()->drawable()->count();
        endif;

        if(!PolicyLevel::isValid($level)):
            return 0;
        endif;

        $preloaded = self::drawableCountAttribute($level);
        if(array_key_exists($preloaded, $this->attributes)):
            return (int) $this->attributes[$preloaded];
        endif;

        return (int) $this->questions()->drawable()->ofLevel($level)->count();
    }

    /**
     * Drawable questions per level, easiest first: ['beginner' => n,
     * 'intermediate' => n, 'expert' => n]. Uses preloaded counts when every
     * level has one, else a single grouped query.
     */
    public function drawableCountsByLevel(): array
    {
        $counts = [];
        $allPreloaded = true;
        foreach(PolicyLevel::all() as $level):
            $attribute = self::drawableCountAttribute($level);
            if(array_key_exists($attribute, $this->attributes)):
                $counts[$level] = (int) $this->attributes[$attribute];
            else:
                $allPreloaded = false;
            endif;
        endforeach;
        if($allPreloaded):
            return $counts;
        endif;

        $grouped = $this->questions()->drawable()
            ->selectRaw('policy_questions.level as lvl, COUNT(*) as aggregate')
            ->groupBy('policy_questions.level')
            ->pluck('aggregate', 'lvl')
            ->all();

        $counts = [];
        foreach(PolicyLevel::all() as $level):
            $counts[$level] = (isset($grouped[$level]) ? (int) $grouped[$level] : 0);
        endforeach;

        return $counts;
    }

    /**
     * What a test at $examLevel needs from each question level, against what
     * the bank can supply: [question level => ['needed' => n, 'available' => n,
     * 'short' => n]], easiest first. 'needed' is the level's share of
     * questions_per_attempt under PolicyLevel::MIX; 'short' is how many more
     * active questions of that level HR has to switch on.
     */
    public function examRequirements(string $examLevel): array
    {
        $quota = PolicyLevel::quota($examLevel, max(1, (int) $this->questions_per_attempt));
        $available = $this->drawableCountsByLevel();

        $rows = [];
        foreach($quota as $level => $needed):
            $have = (isset($available[$level]) ? (int) $available[$level] : 0);
            $rows[$level] = ['needed' => (int) $needed, 'available' => $have, 'short' => max(0, (int) $needed - $have)];
        endforeach;

        return $rows;
    }

    /**
     * Whether a test at $examLevel can be drawn in full: every level has
     * enough active questions for its share. A test is never topped up from
     * another level — that would break the pattern — so one level running
     * short makes the whole test unavailable until HR activates more.
     */
    public function examReady(string $examLevel): bool
    {
        if(!PolicyLevel::isValid($examLevel)):
            return false;
        endif;

        foreach($this->examRequirements($examLevel) as $row):
            if($row['short'] > 0):
                return false;
            endif;
        endforeach;

        return true;
    }

    /** Whether attempts on this policy are timed. */
    public function isTimed(): bool
    {
        return ($this->time_limit_minutes !== null && (int) $this->time_limit_minutes > 0);
    }

    /** The attribute a preloaded per-level count lands in: drawable_beginner_count, … */
    public static function drawableCountAttribute(string $level): string
    {
        return 'drawable_'.$level.'_count';
    }

    /**
     * Preloads drawable_questions_count plus drawable_<level>_count for every
     * level, so drawableQuestionCount($level), drawableCountsByLevel() and
     * PolicyAssignment::canAttempt() need no query per row. Works inside an
     * eager load too: with(['policy' => fn ($q) => $q->withDrawableCounts()]).
     */
    public function scopeWithDrawableCounts($query){
        $counts = ['questions as drawable_questions_count' => function ($q) {
            $q->drawable();
        }];
        foreach(PolicyLevel::all() as $level):
            $counts['questions as '.self::drawableCountAttribute($level)] = function ($q) use ($level) {
                $q->drawable()->ofLevel($level);
            };
        endforeach;

        return $query->withCount($counts);
    }
}
