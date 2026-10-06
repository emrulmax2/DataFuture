<?php

namespace Database\Seeders;

use App\Models\PolicyCategory;
use App\Models\PolicyDocument;
use App\Models\PolicyQuestion;
use App\Models\PolicyQuestionOption;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Loads the college's policies and a drafted question bank from
 * database/data/policy-assessment/question-bank.json:
 *
 *   {"categories": [{"name", "sort_order", "policies": [{"slug", "title",
 *     "version", "pdf_url", "page_url", "thumbnail_url", "sort_order",
 *     "questions": [{"key", "level", "question", "options": [...],
 *     "correct": <0-based index>, "explanation", "source_excerpt"}]}]}]}
 *
 * "level" is beginner | intermediate | expert (missing = beginner). "key" is
 * the question's stable identity, '<policy slug>#<n>'; when missing it is
 * made from the question's position among the policy's valid questions, which
 * is also the sort_order the first version of this seeder gave it.
 *
 * Safe to run any number of times, and never touches HR's work:
 *  - a policy is matched on slug, and a category on the policies already
 *    seeded into it, else on name (deleted ones included throughout), so
 *    nothing is created twice — even after HR renames a category — and
 *    nothing HR edited is reset;
 *  - questions are added by key: a question whose key the policy already
 *    holds (deleted ones included) is left alone, so a re-run cannot
 *    duplicate a question, overwrite HR's edits, or bring back one HR
 *    deleted. New keys are added after the policy's existing questions.
 *  - questions seeded before keys existed are given theirs first (matched on
 *    sort_order), setting only seed_key and level — never the text, the
 *    active flag or who last edited them.
 *
 * Every seeded question is inactive with origin 'ai_draft': HR reviews and
 * activates them before anyone is tested on them.
 */
class PolicyQuestionBankSeeder extends Seeder
{
    const SEEDED_BY = 1;

    protected $path;

    /** What the last run() did — read by tests and printed when run from artisan. */
    public $stats = [];

    public function __construct(?string $path = null)
    {
        $this->path = ($path !== null && $path !== '' ? $path : database_path('data/policy-assessment/question-bank.json'));
        $this->stats = $this->emptyStats();
    }

    public function run()
    {
        $this->stats = $this->emptyStats();

        if(!is_file($this->path)):
            Log::warning('Policy question bank not seeded: file not found.', ['path' => $this->path]);
            return;
        endif;

        $data = json_decode((string) file_get_contents($this->path), true);
        if(!is_array($data) || !isset($data['categories']) || !is_array($data['categories'])):
            Log::warning('Policy question bank not seeded: the file is not valid JSON or has no categories.', ['path' => $this->path]);
            return;
        endif;

        foreach($data['categories'] as $categoryIndex => $categoryData):
            $name = (isset($categoryData['name']) ? trim((string) $categoryData['name']) : '');
            if($name === ''):
                continue;
            endif;

            $policies = (isset($categoryData['policies']) && is_array($categoryData['policies']) ? $categoryData['policies'] : []);

            /* The category these policies were already seeded into comes first,
               so a category HR has renamed is found again rather than
               recreated, empty, under its original name. */
            $category = $this->categoryOfSeededPolicies($policies);
            if(!$category):
                $category = PolicyCategory::withTrashed()->where('name', $name)->first();
            endif;
            if(!$category):
                $category = PolicyCategory::create([
                    'name' => $name,
                    'description' => (isset($categoryData['description']) ? $categoryData['description'] : null),
                    'sort_order' => (isset($categoryData['sort_order']) ? (int) $categoryData['sort_order'] : $categoryIndex + 1),
                    'is_active' => 1,
                    'created_by' => self::SEEDED_BY,
                ]);
                $this->stats['categories_created'] += 1;
            endif;

            foreach($policies as $policyIndex => $policyData):
                $this->seedPolicy($category, $policyData, $policyIndex);
            endforeach;
        endforeach;

        if(isset($this->command)):
            $this->command->info(
                'Policy question bank: '.$this->stats['categories_created'].' categories, '
                .$this->stats['policies_created'].' policies and '
                .$this->stats['questions_created'].' draft questions created ('.$this->levelSummary($this->stats['questions_created_by_level']).'); '
                .$this->stats['questions_backfilled'].' existing questions given a key ('.$this->levelSummary($this->stats['questions_backfilled_by_level']).'); '
                .$this->stats['questions_already_present'].' already in the bank; '
                .$this->stats['questions_invalid'].' invalid questions skipped.'
            );
        endif;
    }

    protected function emptyStats(): array
    {
        $byLevel = array_fill_keys(PolicyLevel::all(), 0);

        return [
            'categories_created' => 0,
            'policies_created' => 0,
            'questions_created' => 0,
            'questions_created_by_level' => $byLevel,
            'questions_backfilled' => 0,
            'questions_backfilled_by_level' => $byLevel,
            'questions_already_present' => 0,
            'questions_invalid' => 0,
            'policies_with_existing_questions' => 0,
        ];
    }

    protected function levelSummary(array $counts): string
    {
        $parts = [];
        foreach(PolicyLevel::labels() as $level => $label):
            $parts[] = $label.' '.(isset($counts[$level]) ? (int) $counts[$level] : 0);
        endforeach;

        return implode(', ', $parts);
    }

    protected function seedPolicy(PolicyCategory $category, $policyData, int $policyIndex): void
    {
        if(!is_array($policyData)):
            return;
        endif;

        $title = (isset($policyData['title']) ? trim((string) $policyData['title']) : '');
        if($title === ''):
            return;
        endif;
        $slug = $this->policySlug($policyData);

        $questions = $this->validQuestions(isset($policyData['questions']) && is_array($policyData['questions']) ? $policyData['questions'] : [], $slug);

        DB::transaction(function () use ($category, $policyData, $policyIndex, $title, $slug, $questions) {
            $policy = PolicyDocument::withTrashed()->where('slug', $slug)->first();
            if(!$policy):
                $policy = PolicyDocument::create([
                    'policy_category_id' => $category->id,
                    'title' => $title,
                    'slug' => $slug,
                    'version' => $this->text($policyData, 'version', 50),
                    'description' => $this->text($policyData, 'description'),
                    'pdf_url' => $this->text($policyData, 'pdf_url', 500),
                    'page_url' => $this->text($policyData, 'page_url', 500),
                    'thumbnail_url' => $this->text($policyData, 'thumbnail_url', 500),
                    'pass_mark' => 80,
                    'questions_per_attempt' => (count($questions) > 0 ? min(10, count($questions)) : 10),
                    'max_attempts' => null,
                    'sort_order' => (isset($policyData['sort_order']) ? (int) $policyData['sort_order'] : $policyIndex + 1),
                    'is_active' => 1,
                    'created_by' => self::SEEDED_BY,
                ]);
                $this->stats['policies_created'] += 1;
            endif;

            /* Deleted questions included throughout: a key HR deleted stays taken. */
            $existing = PolicyQuestion::withTrashed()
                ->where('policy_document_id', $policy->id)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get(['id', 'origin', 'level', 'seed_key', 'sort_order']);
            if($existing->isNotEmpty()):
                $this->stats['policies_with_existing_questions'] += 1;
            endif;

            $byKey = [];
            foreach($questions as $questionData):
                $byKey[$questionData['key']] = $questionData;
            endforeach;

            $taken = [];
            foreach($existing as $row):
                if($row->seed_key !== null && $row->seed_key !== ''):
                    $taken[$row->seed_key] = true;
                endif;
            endforeach;

            /* (a) Drafts seeded before keys existed: their key is their
               position, which the first seeder stored as sort_order. Only
               seed_key and level are written, straight to the table, so the
               text, the active flag, updated_by and updated_at stay as HR
               left them. */
            foreach($existing as $row):
                if($row->origin !== PolicyQuestion::ORIGIN_AI_DRAFT || ($row->seed_key !== null && $row->seed_key !== '') || (int) $row->sort_order < 1):
                    continue;
                endif;
                $key = $slug.'#'.(int) $row->sort_order;
                if(isset($taken[$key])):
                    continue;
                endif;

                $update = ['seed_key' => $key];
                $level = (PolicyLevel::isValid($row->level) ? $row->level : PolicyLevel::BEGINNER);
                /* Only a row still on the column default gets the bank's level:
                   any other value was chosen by HR in the question editor. */
                if(isset($byKey[$key]) && $level === PolicyLevel::BEGINNER):
                    $level = $byKey[$key]['level'];
                    $update['level'] = $level;
                endif;
                DB::table('policy_questions')->where('id', $row->id)->update($update);

                $taken[$key] = true;
                $this->stats['questions_backfilled'] += 1;
                $this->stats['questions_backfilled_by_level'][$level] += 1;
            endforeach;

            /* (b) Every question whose key the policy does not hold yet, as a
               draft after the policy's existing questions. */
            $sortOrder = (int) PolicyQuestion::withTrashed()->where('policy_document_id', $policy->id)->max('sort_order');
            $now = Carbon::now();
            foreach($questions as $questionData):
                if(isset($taken[$questionData['key']])):
                    $this->stats['questions_already_present'] += 1;
                    continue;
                endif;

                $sortOrder += 1;
                $question = PolicyQuestion::create([
                    'policy_document_id' => $policy->id,
                    'question' => $questionData['question'],
                    'explanation' => $questionData['explanation'],
                    'source_excerpt' => $questionData['source_excerpt'],
                    'origin' => PolicyQuestion::ORIGIN_AI_DRAFT,
                    'level' => $questionData['level'],
                    'seed_key' => $questionData['key'],
                    'sort_order' => $sortOrder,
                    'is_active' => 0,
                    'created_by' => self::SEEDED_BY,
                ]);

                $options = [];
                foreach($questionData['options'] as $optionIndex => $optionText):
                    $options[] = [
                        'policy_question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => ($optionIndex === $questionData['correct'] ? 1 : 0),
                        'sort_order' => $optionIndex + 1,
                        'created_by' => self::SEEDED_BY,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                endforeach;
                PolicyQuestionOption::insert($options);

                $taken[$questionData['key']] = true;
                $this->stats['questions_created'] += 1;
                $this->stats['questions_created_by_level'][$questionData['level']] += 1;
            endforeach;
        });
    }

    /**
     * The category (archived ones included) that most of this JSON category's
     * policies already sit in, matched by slug — NULL on a first run.
     */
    protected function categoryOfSeededPolicies(array $policies): ?PolicyCategory
    {
        $slugs = [];
        foreach($policies as $policyData):
            $slug = (is_array($policyData) ? $this->policySlug($policyData) : '');
            if($slug !== ''):
                $slugs[] = $slug;
            endif;
        endforeach;
        if(empty($slugs)):
            return null;
        endif;

        $categoryId = PolicyDocument::withTrashed()
            ->whereIn('slug', array_unique($slugs))
            ->select('policy_category_id')
            ->groupBy('policy_category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('policy_category_id', 'ASC')
            ->value('policy_category_id');

        return ($categoryId ? PolicyCategory::withTrashed()->find($categoryId) : null);
    }

    /** The JSON slug, else one made from the title ('' when there is neither). */
    protected function policySlug(array $policyData): string
    {
        if(isset($policyData['slug']) && trim((string) $policyData['slug']) !== ''):
            return trim((string) $policyData['slug']);
        endif;

        return Str::slug(isset($policyData['title']) ? trim((string) $policyData['title']) : '');
    }

    /**
     * Keeps only questions a test could use: text, 2–6 non-empty options, a
     * correct index that points at one of them, a known level (missing means
     * beginner) and a key of at most 100 characters that no earlier question
     * of this policy uses. Anything else is counted and dropped rather than
     * seeded broken.
     *
     * A question without a key gets '<slug>#<n>', n being its position among
     * the policy's structurally valid questions — the sort_order the first
     * version of this seeder stored, so pre-key rows are matched again.
     */
    protected function validQuestions(array $questions, string $slug): array
    {
        $valid = [];
        $seenKeys = [];
        $position = 0;
        foreach($questions as $questionData):
            $text = (is_array($questionData) && isset($questionData['question']) ? trim((string) $questionData['question']) : '');
            $options = [];
            if(is_array($questionData) && isset($questionData['options']) && is_array($questionData['options'])):
                foreach(array_values($questionData['options']) as $option):
                    $optionText = (is_array($option) ? (isset($option['text']) ? $option['text'] : '') : $option);
                    $options[] = trim((string) $optionText);
                endforeach;
            endif;
            $correct = (is_array($questionData) && isset($questionData['correct']) && is_numeric($questionData['correct']) ? (int) $questionData['correct'] : -1);

            $ok = ($text !== ''
                && count($options) >= 2
                && count($options) <= 6
                && !in_array('', $options, true)
                && $correct >= 0
                && $correct < count($options));
            if(!$ok):
                $this->stats['questions_invalid'] += 1;
                continue;
            endif;
            $position += 1;

            $level = PolicyLevel::BEGINNER;
            if(isset($questionData['level']) && trim((string) $questionData['level']) !== ''):
                $level = strtolower(trim((string) $questionData['level']));
            endif;
            $key = (isset($questionData['key']) && trim((string) $questionData['key']) !== '' ? trim((string) $questionData['key']) : $slug.'#'.$position);
            if(!PolicyLevel::isValid($level) || mb_strlen($key) > 100 || isset($seenKeys[$key])):
                $this->stats['questions_invalid'] += 1;
                continue;
            endif;
            $seenKeys[$key] = true;

            $valid[] = [
                'key' => $key,
                'level' => $level,
                'question' => $text,
                'options' => $options,
                'correct' => $correct,
                'explanation' => (isset($questionData['explanation']) && trim((string) $questionData['explanation']) !== '' ? trim((string) $questionData['explanation']) : null),
                'source_excerpt' => (isset($questionData['source_excerpt']) && trim((string) $questionData['source_excerpt']) !== '' ? trim((string) $questionData['source_excerpt']) : null),
            ];
        endforeach;

        return $valid;
    }

    protected function text(array $data, string $key, ?int $max = null): ?string
    {
        if(!isset($data[$key]) || trim((string) $data[$key]) === ''):
            return null;
        endif;

        $value = trim((string) $data[$key]);

        return ($max !== null ? mb_substr($value, 0, $max) : $value);
    }
}
