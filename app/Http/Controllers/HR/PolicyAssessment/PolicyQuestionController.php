<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyAssessment\PolicyQuestionRequest;
use App\Models\PolicyDocument;
use App\Models\PolicyQuestion;
use App\Models\PolicyQuestionOption;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Policy Assessments › Question Bank › one policy's questions.
 *
 * Seeded questions arrive as drafts (inactive, origin ai_draft). HR reads
 * each one as a card — question, options with the right one marked, the
 * explanation and the policy excerpt it came from — and switches it on.
 * A question can only be active when a test could draw it: at least two
 * options and exactly one correct.
 *
 * Every question has a level (Beginner / Intermediate / Expert). An exam
 * never draws from one level alone: it takes a set share of each level
 * (PolicyLevel::MIX) of the policy's questions per test. So the header shows
 * whether each of the three exams is ready — what it needs from every level
 * against what is active — next to each level's own counts, and the list and
 * "Activate all drafts" can be narrowed to one level.
 */
class PolicyQuestionController extends Controller
{
    /** How many questions HR aims to have in each policy's bank. */
    const BANK_TARGET = 50;

    /** Client sort field => SQL column. Anything else is ignored. */
    const SORTABLE = [
        'id' => 'id',
        'sort_order' => 'sort_order',
        'question' => 'question',
        'is_active' => 'is_active',
        'origin' => 'origin',
        'updated_at' => 'updated_at',
    ];

    const INCOMPLETE_MESSAGE = 'This question needs at least two options and exactly one correct answer before it can be made active. Edit it first.';

    public function index(PolicyDocument $policy)
    {
        $this->guard();

        $policy->load(['category' => function ($q) {
            $q->withTrashed();
        }]);

        return view('pages.hr.policy-assessment.questions', [
            'title' => 'Policy Questions - London Churchill College',
            'subtitle' => 'Policy Questions',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Question Bank', 'href' => route('policy.assessment.policy')],
                ['label' => $policy->title, 'href' => 'javascript:void(0);'],
            ],
            'policy' => $policy,
            'counts' => $this->counts($policy),
        ]);
    }

    public function list(Request $request, PolicyDocument $policy)
    {
        $this->guard();

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? trim($request->querystr) : '');
        $status = (isset($request->status) && in_array($request->status, ['all', 'active', 'drafts', 'archived'], true) ? $request->status : 'all');
        $level = $this->levelFilter($request->input('level'));

        $query = PolicyQuestion::where('policy_document_id', $policy->id);
        if($level !== ''):
            $query->ofLevel($level);
        endif;
        if($queryStr !== ''):
            $query->where(function ($q) use ($queryStr) {
                $q->where('question', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('explanation', 'LIKE', '%'.$queryStr.'%')
                    ->orWhereHas('options', function ($o) use ($queryStr) {
                        $o->where('option_text', 'LIKE', '%'.$queryStr.'%');
                    });
            });
        endif;
        if($status == 'archived'):
            $query->onlyTrashed();
        elseif($status == 'active'):
            $query->where('is_active', 1);
        elseif($status == 'drafts'):
            $query->where('is_active', 0);
        endif;

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 25));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query->with('options');
        $this->applySorters($query, $request);

        $Query = $query->skip($offset)->take($perpage)->get();

        $data = [];
        if(!empty($Query)):
            $i = $offset + 1;
            foreach($Query as $list):
                $data[] = $this->row($list, $i);
                $i++;
            endforeach;
        endif;

        return response()->json([
            'last_page' => $last_page,
            'total_rows' => $total_rows,
            'data' => $data,
            'counts' => $this->counts($policy),
        ]);
    }

    public function store(PolicyQuestionRequest $request, PolicyDocument $policy)
    {
        $this->guard();

        $userId = auth()->user()->id;
        $question = DB::transaction(function () use ($request, $policy, $userId) {
            $question = PolicyQuestion::create([
                'policy_document_id' => $policy->id,
                'question' => trim($request->question),
                'explanation' => $this->blankToNull($request->explanation),
                'source_excerpt' => $this->blankToNull($request->source_excerpt),
                'origin' => PolicyQuestion::ORIGIN_MANUAL,
                'level' => (string) $request->input('level'),
                'sort_order' => (int) PolicyQuestion::withTrashed()->where('policy_document_id', $policy->id)->max('sort_order') + 1,
                'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
                'created_by' => $userId,
            ]);

            $this->syncOptions($question, $request->input('options', []), (string) $request->input('correct'), $userId);

            return $question;
        });

        return response()->json([
            'message' => 'Question added.',
            'id' => $question->id,
            'counts' => $this->counts($policy),
        ], 200);
    }

    public function edit(PolicyQuestion $question)
    {
        $this->guard();

        $question->load('options');
        $options = [];
        foreach($question->options as $option):
            $options[] = [
                'id' => $option->id,
                'text' => $option->option_text,
                'is_correct' => ($option->is_correct ? 1 : 0),
            ];
        endforeach;

        return response()->json([
            'id' => $question->id,
            'policy_document_id' => $question->policy_document_id,
            'question' => $question->question,
            'explanation' => $question->explanation,
            'source_excerpt' => $question->source_excerpt,
            'level' => $this->levelKey($question->level),
            'level_label' => PolicyLevel::label($this->levelKey($question->level)),
            'is_active' => ($question->is_active ? 1 : 0),
            'options' => $options,
        ]);
    }

    /**
     * Options keep their ids: rows sent with an id of this question are
     * updated, rows without one are created, and options no longer on the
     * form are soft-deleted. Attempts already drawn keep their own snapshot.
     */
    public function update(PolicyQuestionRequest $request, PolicyQuestion $question)
    {
        $this->guard();

        $userId = auth()->user()->id;
        DB::transaction(function () use ($request, $question, $userId) {
            $question->fill([
                'question' => trim($request->question),
                'explanation' => $this->blankToNull($request->explanation),
                'source_excerpt' => $this->blankToNull($request->source_excerpt),
                'level' => (string) $request->input('level'),
                'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
            ]);
            $optionsChanged = $this->syncOptions($question, $request->input('options', []), (string) $request->input('correct'), $userId);
            if($question->isDirty() || $optionsChanged):
                $question->updated_by = $userId;
                $question->touch();
            endif;
        });

        $question->unsetRelation('options');
        $question->load('options');

        return response()->json([
            'message' => 'Question updated.',
            'row' => $this->row($question, 0),
            'counts' => $this->counts($question->policy),
        ], 200);
    }

    /**
     * Switch a question on or off. Pass is_active=0|1 to set it explicitly
     * (a double click cannot flip it twice); without it, it flips. Switching
     * on an incomplete question is refused with a 422.
     */
    public function toggleStatus(Request $request, PolicyQuestion $question)
    {
        $this->guard();

        $target = ($request->has('is_active') ? ($request->input('is_active') == 1) : !$question->is_active);
        if($target && !$question->isWellFormed()):
            return response()->json([
                'message' => self::INCOMPLETE_MESSAGE,
                'errors' => ['is_active' => [self::INCOMPLETE_MESSAGE]],
            ], 422);
        endif;

        if($question->is_active != $target):
            $question->is_active = $target;
            $question->updated_by = auth()->user()->id;
            $question->save();
        endif;

        $question->load('options');

        return response()->json([
            'message' => ($question->is_active ? 'Question is active.' : 'Question moved back to drafts.'),
            'is_active' => ($question->is_active ? 1 : 0),
            'row' => $this->row($question, 0),
            'counts' => $this->counts($question->policy),
        ], 200);
    }

    /**
     * Switch on every draft of this policy that a test could draw. Incomplete
     * drafts stay as drafts and are reported as skipped. With level (the
     * page's Level filter) only that level's drafts are touched; blank means
     * every level. Any other value is refused with a 422.
     */
    public function activateAll(Request $request, PolicyDocument $policy)
    {
        $this->guard();

        $level = $request->input('level');
        $level = (is_string($level) ? trim($level) : ($level === null ? '' : false));
        if($level === false || ($level !== '' && !PolicyLevel::isValid($level))):
            $message = 'Choose Beginner, Intermediate or Expert, or leave the level blank to activate drafts at every level.';

            return response()->json(['message' => $message, 'errors' => ['level' => [$message]]], 422);
        endif;

        $userId = auth()->user()->id;
        $result = DB::transaction(function () use ($policy, $userId, $level) {
            $drafts = PolicyQuestion::where('policy_document_id', $policy->id)->where('is_active', 0);
            if($level !== ''):
                $drafts->ofLevel($level);
            endif;
            $draftTotal = (clone $drafts)->count();
            $ids = (clone $drafts)->wellFormed()->lockForUpdate()->pluck('id')->all();
            if(!empty($ids)):
                PolicyQuestion::whereIn('id', $ids)->update(['is_active' => 1, 'updated_by' => $userId]);
            endif;

            return ['activated' => count($ids), 'skipped' => max(0, $draftTotal - count($ids))];
        });

        /* "3 Expert questions activated." — the level word only when narrowed. */
        $levelWord = ($level !== '' ? PolicyLevel::label($level).' ' : '');
        if($result['activated'] == 0 && $result['skipped'] == 0):
            $message = 'There were no '.$levelWord.'drafts to activate.';
        else:
            $message = $result['activated'].' '.$levelWord.($result['activated'] == 1 ? 'question' : 'questions').' activated.';
            if($result['skipped'] > 0):
                $message .= ' '.$result['skipped'].' incomplete '.$levelWord.($result['skipped'] == 1 ? 'draft was' : 'drafts were').' skipped — each needs at least two options and exactly one correct answer.';
            endif;
        endif;

        return response()->json([
            'activated' => $result['activated'],
            'skipped' => $result['skipped'],
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'message' => $message,
            'counts' => $this->counts($policy),
        ], 200);
    }

    public function destroy($id)
    {
        $this->guard();

        $question = PolicyQuestion::findOrFail($id);
        $question->updated_by = auth()->user()->id;
        $question->save();
        $question->delete();

        return response()->json(['message' => 'Question archived.', 'counts' => $this->counts(PolicyDocument::withTrashed()->find($question->policy_document_id))], 200);
    }

    public function restore($id)
    {
        $this->guard();

        $question = PolicyQuestion::onlyTrashed()->findOrFail($id);
        $question->restore();

        return response()->json(['message' => 'Question restored.', 'counts' => $this->counts(PolicyDocument::withTrashed()->find($question->policy_document_id))], 200);
    }

    /**
     * The policy's question bank as a PDF handout: every question as the
     * card HR sees on screen, with the correct option marked, why it is
     * right and the policy excerpt it came from. It is the answer key, so it
     * is HR-only like everything else here.
     *
     * level (beginner|intermediate|expert) and status (active|draft) narrow
     * it to what the page is showing; anything else means every level / both
     * statuses. Archived questions are never included. inline=1 opens it in
     * the browser instead of downloading it.
     */
    public function pdf(Request $request, PolicyDocument $policy)
    {
        $this->guard();
        $this->pdfMemory();
        $this->pdfTimeLimit();

        $level = $this->levelFilter($request->query('level'));
        $status = (in_array($request->query('status'), ['active', 'draft'], true) ? $request->query('status') : 'all');

        $policy->load(['category' => function ($q) {
            $q->withTrashed();
        }]);
        $sections = $this->pdfSections($policy, $level, $status);

        $total = 0;
        $active = 0;
        foreach($sections as $section):
            $total += $section['count'];
            $active += $section['active'];
        endforeach;

        $filter = [];
        if($level !== ''):
            $filter[] = PolicyLevel::label($level).($status == 'all' ? ' only' : '');
        endif;
        if($status != 'all'):
            $filter[] = ($status == 'active' ? 'Active only' : 'Drafts only');
        endif;

        $pdf = Pdf::setPaper('a4', 'portrait');

        $html = view('pages.hr.policy-assessment.pdf.questions', [
            'policy' => $policy,
            'footerTitle' => $this->pdfFooterTitle($pdf, (string) $policy->title),
            'sections' => $sections,
            'total' => $total,
            'active' => $active,
            'drafts' => $total - $active,
            'filterLabel' => (empty($filter) ? 'All questions' : implode(' · ', $filter)),
            'generatedAt' => now()->format('j F Y, H:i'),
            'generatedBy' => trim((string) auth()->user()->full_name),
        ])->render();

        /*
         * The space between tags is dropped: dompdf would lay every stretch of
         * it out as a box of its own. HR's own line breaks are safe: the view
         * never leaves one between two tags.
         */
        $compact = preg_replace('/>\s+</', '><', $html);

        $pdf->loadHTML($compact !== null ? $compact : $html);
        $pdf->render();
        $this->pdfPageNumbers($pdf);

        $fileName = $this->pdfFileName($policy, $level, $status);

        return ($request->query('inline') == 1 ? $pdf->stream($fileName) : $pdf->download($fileName));
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }

    /**
     * The export's questions as sections, Beginner then Intermediate then
     * Expert, each in sort_order then id; a level with nothing to show is left
     * out. Numbering (Q1…Qn) runs through the whole document. Every card is
     * the same data the on-screen card is drawn from (row()).
     *
     * [[level, label, cards, count, active, first, last], ...] — first / last
     * are the numbers of the section's first and last question.
     */
    private function pdfSections(PolicyDocument $policy, string $level, string $status): array
    {
        $query = PolicyQuestion::where('policy_document_id', $policy->id)->with('options');
        if($level !== ''):
            $query->ofLevel($level);
        endif;
        if($status == 'active'):
            $query->where('is_active', 1);
        elseif($status == 'draft'):
            $query->where('is_active', 0);
        endif;
        $questions = $query->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->get();

        $byLevel = [];
        foreach(PolicyLevel::all() as $key):
            $byLevel[$key] = [];
        endforeach;
        foreach($questions as $question):
            $byLevel[$this->levelKey($question->level)][] = $question;
        endforeach;

        $sections = [];
        $number = 0;
        foreach($byLevel as $key => $rows):
            if(empty($rows)):
                continue;
            endif;

            $cards = [];
            $active = 0;
            foreach($rows as $question):
                $number += 1;
                $cards[] = $this->row($question, $number);
                $active += ($question->is_active ? 1 : 0);
            endforeach;

            $sections[] = [
                'level' => $key,
                'label' => PolicyLevel::label($key),
                'cards' => $cards,
                'count' => count($cards),
                'active' => $active,
                'first' => $number - count($cards) + 1,
                'last' => $number,
            ];
        endforeach;

        return $sections;
    }

    /**
     * dompdf keeps every box of the document in memory: about 60 MB for 75
     * questions, on top of what the request already uses, which leaves little
     * to spare under PHP's usual 128 MB. Give it room, without ever lowering a
     * limit that is already higher.
     */
    private function pdfMemory(): void
    {
        $limit = trim((string) ini_get('memory_limit'));
        $units = ['k' => 1024, 'm' => 1048576, 'g' => 1073741824];
        $unit = strtolower(substr($limit, -1));
        $bytes = (float) $limit * (isset($units[$unit]) ? $units[$unit] : 1);
        if($limit !== '' && $bytes > 0 && $bytes < 268435456):
            ini_set('memory_limit', '256M');
        endif;
    }

    /**
     * Laying the cards out takes about 5 seconds for 75 questions and grows
     * with the bank: 300 questions would pass PHP's usual 30 seconds. Allow
     * two minutes, without ever shortening a longer limit or adding one where
     * there is none (0).
     */
    private function pdfTimeLimit(): void
    {
        $limit = (int) ini_get('max_execution_time');
        if($limit > 0 && $limit < 120):
            set_time_limit(120);
        endif;
    }

    /**
     * The policy title as the footer prints it: whole when it fits the
     * footer's left cell on one line, otherwise cut short with an ellipsis.
     * dompdf cannot do that itself, and a title left to wrap puts a second
     * line where most office printers cannot print. It is measured in the
     * face the footer sets it in; until dompdf has registered that face (the
     * first export on a server) a character limit stands in.
     */
    private function pdfFooterTitle(\Barryvdh\DomPDF\PDF $pdf, string $title): string
    {
        $oneLine = preg_replace('/\s+/u', ' ', $title);
        $title = trim($oneLine !== null ? $oneLine : $title);

        $metrics = $pdf->getDomPDF()->getFontMetrics();
        $font = $metrics->getFont('Plus Jakarta Sans', '800');
        if(empty($font) || !is_file($font.'.ufm')):
            return Str::limit($title, 60, '…');
        endif;

        /*
         * Points. The cell is 46% of the 533pt between the side margins; the
         * rest is air before the centre mark. Keep in step with .foot and the
         * body's word-spacing in pdf/questions.blade.php.
         */
        $room = 238;
        $width = function ($text) use ($metrics, $font) {
            return $metrics->getTextWidth($text, $font, 7.5, 0.55);
        };
        if($width($title) <= $room):
            return $title;
        endif;

        $cut = rtrim(mb_substr($title, 0, -1));
        while($cut !== '' && $width($cut.'…') > $room):
            $cut = rtrim(mb_substr($cut, 0, -1));
        endwhile;

        return $cut.'…';
    }

    /**
     * "Page 3 of 12" at the right of the footer the view draws on every page.
     * The page count is only known once the document is laid out, so it is
     * written onto the finished pages.
     */
    private function pdfPageNumbers(\Barryvdh\DomPDF\PDF $pdf): void
    {
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font = $metrics->getFont('Plus Jakarta Sans', '600');
        if(empty($font)):
            $font = $metrics->getFont('DejaVu Sans', 'normal');
        endif;

        /* Points. Keep in step with @page, .foot and the body's word-spacing in pdf/questions.blade.php. */
        $size = 7.5;
        $spacing = 0.55;
        $right = $canvas->get_width() - 31.18;
        $top = $canvas->get_height() - 33.1;
        $colour = [0.416, 0.459, 0.525];

        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font, $size, $spacing, $right, $top, $colour) {
            $text = 'Page '.$pageNumber.' of '.$pageCount;
            $canvas->text($right - $fontMetrics->getTextWidth($text, $font, $size, $spacing), $top, $text, $font, $size, $colour, $spacing);
        });
    }

    /** <policy-slug>-question-bank[-<level>][-active|-draft].pdf — letters, digits and hyphens only. */
    private function pdfFileName(PolicyDocument $policy, string $level, string $status): string
    {
        $slug = trim(substr(Str::slug((string) $policy->title), 0, 80), '-');

        return ($slug !== '' ? $slug : 'policy-'.$policy->id).'-question-bank'
            .($level !== '' ? '-'.$level : '')
            .($status != 'all' ? '-'.$status : '')
            .'.pdf';
    }

    /**
     * Write the submitted option rows. $options is [key => ['id'?, 'text']],
     * $correctKey the key of the right one; rows are stored in form order.
     */
    private function syncOptions(PolicyQuestion $question, array $options, string $correctKey, int $userId): bool
    {
        $existing = PolicyQuestionOption::where('policy_question_id', $question->id)->get()->keyBy('id');
        $kept = [];
        $changed = false;
        $position = 0;

        foreach($options as $key => $row):
            if(!is_array($row)):
                continue;
            endif;
            $position += 1;
            $text = trim((string) (isset($row['text']) ? $row['text'] : ''));
            $isCorrect = ((string) $key === $correctKey ? 1 : 0);
            $optionId = (isset($row['id']) && ctype_digit((string) $row['id']) ? (int) $row['id'] : 0);

            if($optionId > 0 && isset($existing[$optionId])):
                $option = $existing[$optionId];
                $option->fill(['option_text' => $text, 'is_correct' => $isCorrect, 'sort_order' => $position]);
                if($option->isDirty()):
                    $option->updated_by = $userId;
                    $option->save();
                    $changed = true;
                endif;
                $kept[] = $optionId;
            else:
                $option = PolicyQuestionOption::create([
                    'policy_question_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $isCorrect,
                    'sort_order' => $position,
                    'created_by' => $userId,
                ]);
                $kept[] = $option->id;
                $changed = true;
            endif;
        endforeach;

        foreach($existing as $id => $option):
            if(!in_array($id, $kept, true)):
                $option->updated_by = $userId;
                $option->save();
                $option->delete();
                $changed = true;
            endif;
        endforeach;

        return $changed;
    }

    /** One review card's data. HR-only: carries the correct answer, explanation and excerpt. */
    private function row(PolicyQuestion $question, int $sl): array
    {
        $options = [];
        $plain = [];
        $correctText = '';
        $correctCount = 0;
        $letter = 0;
        foreach($question->options as $option):
            $label = chr(65 + ($letter % 26));
            $options[] = [
                'id' => $option->id,
                'letter' => $label,
                'text' => $option->option_text,
                'is_correct' => ($option->is_correct ? 1 : 0),
            ];
            $plain[] = $label.') '.$option->option_text;
            if($option->is_correct):
                $correctCount += 1;
                $correctText = $label.') '.$option->option_text;
            endif;
            $letter += 1;
        endforeach;

        $issue = '';
        if(count($options) < 2):
            $issue = 'Needs at least two options';
        elseif($correctCount == 0):
            $issue = 'No correct answer marked';
        elseif($correctCount > 1):
            $issue = 'More than one correct answer';
        endif;

        return [
            'id' => $question->id,
            'sl' => $sl,
            'question' => $question->question,
            'explanation' => $question->explanation,
            'source_excerpt' => $question->source_excerpt,
            'level' => $this->levelKey($question->level),
            'level_label' => PolicyLevel::label($this->levelKey($question->level)),
            'is_active' => ($question->is_active ? 1 : 0),
            'status_label' => ($question->trashed() ? 'Archived' : ($question->is_active ? 'Active' : 'Draft')),
            'well_formed' => ($issue === '' ? 1 : 0),
            'issue' => $issue,
            'options' => $options,
            'options_plain' => implode(' | ', $plain),
            'correct_text' => $correctText,
            'sort_order' => (int) $question->sort_order,
            'updated_at' => ($question->updated_at ? $question->updated_at->format('d M Y H:i') : ''),
            'deleted_at' => $question->deleted_at,
        ];
    }

    /**
     * Header numbers for the policy: what is left to review and what the
     * exams can draw right now, for the whole bank, for each level and for
     * each exam.
     *
     * exams => [beginner => [ready, state, state_label, needed, available,
     * short, note, summary, ...], ...] — see PolicyDocumentController::examReadiness().
     * levels => [beginner => [active, drafts, archived, total, drawable,
     * needed_max (the most any exam draws from the level), holds_back (the
     * exams it leaves short), state (ready|short|blocked), state_label,
     * summary, note], ...].
     * pattern => the question mix and the counts it gives for this policy's
     * questions per test — see PolicyDocumentController::examPattern().
     *
     * The policy is read afresh with its drawable counts preloaded, so the
     * numbers follow a question that was just switched on or off, and a
     * change to "Questions per test", without a query per exam.
     */
    private function counts(?PolicyDocument $policy): array
    {
        $levels = [];
        foreach(PolicyLevel::all() as $level):
            $levels[$level] = ['active' => 0, 'drafts' => 0, 'archived' => 0];
        endforeach;

        $policy = ($policy ? PolicyDocument::withTrashed()->withDrawableCounts()->find($policy->id) : null);
        if(!$policy):
            return [
                'active' => 0, 'drafts' => 0, 'archived' => 0, 'total' => 0, 'drawable' => 0, 'questions_per_attempt' => 0,
                'bank_target' => self::BANK_TARGET,
                'time_limit_minutes' => null,
                'time_limit_label' => PolicyDocumentController::timeLimitLabel(null),
                'levels' => $this->levelReadiness($levels, [], [], 0),
                'exams' => [],
                'exams_ready' => 0,
                'pattern' => PolicyDocumentController::examPattern(0),
            ];
        endif;

        $rows = PolicyQuestion::withTrashed()
            ->where('policy_document_id', $policy->id)
            ->selectRaw('level AS lvl')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NULL AND is_active = 1 THEN 1 ELSE 0 END) AS active_count')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NULL AND is_active = 0 THEN 1 ELSE 0 END) AS draft_count')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS archived_count')
            ->groupBy('level')
            ->get();

        $active = 0;
        $drafts = 0;
        $archived = 0;
        foreach($rows as $row):
            $rowActive = (int) (isset($row->active_count) ? $row->active_count : 0);
            $rowDrafts = (int) (isset($row->draft_count) ? $row->draft_count : 0);
            $rowArchived = (int) (isset($row->archived_count) ? $row->archived_count : 0);
            $active += $rowActive;
            $drafts += $rowDrafts;
            $archived += $rowArchived;
            if(isset($row->lvl) && isset($levels[$row->lvl])):
                $levels[$row->lvl] = ['active' => $rowActive, 'drafts' => $rowDrafts, 'archived' => $rowArchived];
            endif;
        endforeach;

        $perTest = max(1, (int) $policy->questions_per_attempt);
        $levelDrafts = [];
        foreach($levels as $level => $row):
            $levelDrafts[$level] = (int) $row['drafts'];
        endforeach;
        $exams = PolicyDocumentController::examReadiness($policy, $levelDrafts);
        $examsReady = 0;
        foreach($exams as $exam):
            $examsReady += ($exam['ready'] ? 1 : 0);
        endforeach;
        $timeLimit = ($policy->isTimed() ? (int) $policy->time_limit_minutes : null);

        return [
            'active' => $active,
            'drafts' => $drafts,
            'archived' => $archived,
            'total' => $active + $drafts,
            'drawable' => $policy->drawableQuestionCount(),
            'questions_per_attempt' => (int) $policy->questions_per_attempt,
            'bank_target' => self::BANK_TARGET,
            'time_limit_minutes' => $timeLimit,
            'time_limit_label' => PolicyDocumentController::timeLimitLabel($timeLimit),
            'levels' => $this->levelReadiness($levels, $policy->drawableCountsByLevel(), $exams, $perTest),
            'exams' => $exams,
            'exams_ready' => $examsReady,
            'pattern' => PolicyDocumentController::examPattern($perTest),
        ];
    }

    /**
     * Each level's own numbers and whether it holds an exam back, worded once
     * here so the page (Blade) and the live refresh (JS) say the same thing:
     * "18 active of 20 · enough". Every exam draws from every level, so a
     * level has enough when it covers the largest share any exam takes from
     * it; otherwise the exams it leaves short are named.
     */
    private function levelReadiness(array $levels, array $drawable, array $exams, int $perTest): array
    {
        $out = [];
        foreach(PolicyLevel::all() as $level):
            $active = (int) $levels[$level]['active'];
            $drafts = (int) $levels[$level]['drafts'];
            $total = $active + $drafts;
            $canDraw = (int) (isset($drawable[$level]) ? $drawable[$level] : 0);

            /* The most any exam draws from this level, and the exams it cannot supply. */
            $neededMax = 0;
            $holdsBack = [];
            foreach($exams as $examKey => $exam):
                $need = (int) (isset($exam['needed'][$level]) ? $exam['needed'][$level] : 0);
                $neededMax = max($neededMax, $need);
                if($need > $canDraw):
                    $holdsBack[] = $examKey;
                endif;
            endforeach;
            $missing = max(0, $neededMax - $canDraw);

            if($neededMax <= 0):
                $state = 'ready';
                $stateLabel = 'Enough';
                $note = 'No exam draws from this level at '.$perTest.' '.($perTest == 1 ? 'question' : 'questions').' per test.';
            elseif($canDraw >= $neededMax):
                $state = 'ready';
                $stateLabel = 'Enough';
                $note = 'Enough for every exam — the most one draws is '.$neededMax.'.';
            elseif($canDraw <= 0):
                $state = 'blocked';
                $stateLabel = ($active <= 0 ? 'None active' : 'None usable');
                if($total <= 0):
                    $note = 'No questions at this level yet. Exams draw up to '.$neededMax.'.';
                elseif($drafts > 0):
                    $note = 'Switch on its drafts — exams draw up to '.$neededMax.'.';
                else:
                    $note = 'Its active questions are incomplete. Edit them first.';
                endif;
            else:
                $state = 'short';
                $stateLabel = 'Too few';
                if(count($holdsBack) >= count($exams)):
                    $who = 'exams draw';
                else:
                    $names = [];
                    foreach($holdsBack as $examKey):
                        $names[] = PolicyLevel::label($examKey);
                    endforeach;
                    $who = 'the '.PolicyDocumentController::joinList($names).' '.(count($names) == 1 ? 'exam draws' : 'exams draw');
                endif;
                $note = $missing.' more needed — '.$who.' up to '.$neededMax.'.';
            endif;

            $out[$level] = [
                'level' => $level,
                'label' => PolicyLevel::label($level),
                'active' => $active,
                'drafts' => $drafts,
                'archived' => (int) $levels[$level]['archived'],
                'total' => $total,
                'drawable' => $canDraw,
                'needed_max' => $neededMax,
                'holds_back' => $holdsBack,
                'state' => $state,
                'state_label' => $stateLabel,
                'summary' => $active.' active of '.$total.' · '.strtolower($stateLabel),
                'note' => $note,
            ];
        endforeach;

        return $out;
    }

    /** A stored level, read safely: rows that predate levels (or hold junk) are Beginner. */
    private function levelKey($level): string
    {
        return (is_string($level) && PolicyLevel::isValid($level) ? $level : PolicyLevel::BEGINNER);
    }

    /** The list's Level filter: a valid level, else '' (every level). */
    private function levelFilter($level): string
    {
        return (is_string($level) && PolicyLevel::isValid($level) ? $level : '');
    }

    private function blankToNull($value): ?string
    {
        return (isset($value) && trim((string) $value) !== '' ? trim((string) $value) : null);
    }

    /** Tabulator sorters, whitelisted: unknown fields are dropped, dir is asc|desc only. */
    private function applySorters($query, Request $request): void
    {
        $applied = false;
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) && is_string($sort['field']) ? $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'desc' ? 'DESC' : 'ASC');
            if(isset(self::SORTABLE[$field])):
                $query->orderBy(self::SORTABLE[$field], $dir);
                $applied = true;
            endif;
        endforeach;

        if(!$applied):
            $query->orderBy('sort_order', 'ASC');
        endif;
        $query->orderBy('id', 'ASC');
    }
}
