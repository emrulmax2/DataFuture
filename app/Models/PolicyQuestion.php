<?php

namespace App\Models;

use App\Support\PolicyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A multiple-choice question in a policy's bank.
 *
 * Seeded questions arrive with origin 'ai_draft' and is_active 0; HR reviews
 * and activates them. Only active questions with at least two options and
 * exactly one correct option are ever drawn into a test. explanation and
 * source_excerpt are for HR's review only.
 *
 * Every question has one level (PolicyLevel: beginner, intermediate,
 * expert); a test draws a fixed share from each level (PolicyLevel::MIX),
 * so a level's questions appear in all three exams. seed_key is
 * the stable identity of a seeded question ('<policy slug>#<n>') so the
 * seeder can add new questions without duplicating or reviving old ones.
 */
class PolicyQuestion extends Model
{
    use HasFactory, SoftDeletes;

    const ORIGIN_MANUAL = 'manual';
    const ORIGIN_AI_DRAFT = 'ai_draft';

    protected $dates = ['deleted_at'];

    /** Matches the column default. */
    protected $attributes = [
        'level' => PolicyLevel::BEGINNER,
    ];

    protected $fillable = [
        'policy_document_id',
        'question',
        'explanation',
        'source_excerpt',
        'origin',
        'level',
        'seed_key',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function policy(){
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id');
    }

    public function options(){
        return $this->hasMany(PolicyQuestionOption::class, 'policy_question_id', 'id')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
    }

    public function correctOption(){
        return $this->hasOne(PolicyQuestionOption::class, 'policy_question_id', 'id')->where('is_correct', 1);
    }

    /**
     * Whether this question could go into a test once active: at least two
     * options and exactly one marked correct. Uses the loaded options when
     * present. HR cannot activate a question that fails this.
     */
    public function isWellFormed(): bool
    {
        $options = $this->relationLoaded('options') ? $this->options : $this->options()->get();

        return $options->count() >= 2 && $options->where('is_correct', true)->count() === 1;
    }

    /**
     * Questions that are structurally sound: at least two (non-deleted)
     * options and exactly one of them correct. "Activate all drafts" uses
     * this so it only switches on questions that could actually be drawn.
     */
    public function scopeWellFormed($query){
        return $query->whereHas('options', null, '>=', 2)
            ->whereHas('options', function ($q) {
                $q->where('is_correct', 1);
            }, '=', 1);
    }

    /**
     * The questions a test can draw from: active and well formed. Kept here so
     * the bank page, the policy counts and the draw all agree.
     */
    public function scopeDrawable($query){
        return $query->where('is_active', 1)->wellFormed();
    }

    /** Questions at one level; an unknown level matches nothing. */
    public function scopeOfLevel($query, ?string $level){
        return $query->where('policy_questions.level', (PolicyLevel::isValid($level) ? $level : '__none__'));
    }

    public function getLevelLabelAttribute(){
        return PolicyLevel::label($this->level);
    }
}
