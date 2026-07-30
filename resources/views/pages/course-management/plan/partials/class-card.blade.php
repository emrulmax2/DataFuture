{{--
    One planned class in the builder.

    This is the single definition of the card. It is rendered server-side both
    into the page (existing plans) and from `class.plan.get.box` (a new, empty
    card), which is why it takes everything it needs as `$card` rather than
    reading anything from the surrounding view. The legacy code built this
    markup twice, by string concatenation, in two methods that had already
    drifted apart.

    The save contract lives here: every `[data-cm-field]` becomes a key in the
    `routineData` payload `PlanController::store()` reads, so the names below
    are the ones it looks for — `course`, `group`, `module`, `tutor`,
    `personal_tutor`, `class_type`, `time`, `submission`, `virtual_room`,
    `note` and `existing_id`. Renaming one silently drops that value on save.

    Required:
      $card     [
                  'day' => 1..7, 'venue_id' => …, 'room_id' => …,
                  'existing_id' => 0, 'course_id' => …, 'group_id' => …,
                  'module_id' => …,
                  'class_type' => '', 'tutor_id' => …, 'personal_tutor_id' => …,
                  'time' => '', 'submission' => '', 'virtual_room' => '', 'note' => '',
                ]
      $cardModules  Module creations for this instance term
      $cardUsers    Active users, for both tutor fields
--}}
@php
    $cardTypes = ['Theory', 'Practical', 'Seminar'];
    $cardType = $card['class_type'] ?? '';

    /**
     * Options for one select, guaranteeing the saved value is among them.
     *
     * The tutor lists only carry active users, but a plan can name someone who
     * has since been deactivated — 2029 of them do. Without this, the select
     * falls back to "Select", and the next save writes that empty value over a
     * real assignment. The legacy markup kept the value in a `data-id`
     * attribute that was independent of the list, so it never lost it.
     */
    $cardOptions = function ($rows, $valueKey, $labelKey, $current, $currentLabel) {
        $current = (string) ($current ?? '');
        $out = [];
        $found = false;

        foreach ($rows as $row) {
            $value = (string) $row->{$valueKey};
            $out[] = ['value' => $value, 'label' => $row->{$labelKey}];
            if ($value === $current) {
                $found = true;
            }
        }

        if ($current !== '' && $current !== '0' && !$found) {
            array_unshift($out, [
                'value' => $current,
                'label' => ($currentLabel !== '' && $currentLabel !== null ? $currentLabel : 'ID '.$current).' (not in list)',
            ]);
        }

        return $out;
    };

    $cardModuleOptions = $cardOptions($cardModules, 'id', 'module_name', $card['module_id'] ?? '', $card['module_name'] ?? '');
    $cardTutorOptions = $cardOptions($cardUsers, 'id', 'name', $card['tutor_id'] ?? '', $card['tutor_name'] ?? '');
    $cardPTutorOptions = $cardOptions($cardUsers, 'id', 'name', $card['personal_tutor_id'] ?? '', $card['personal_tutor_name'] ?? '');

    $cardModuleName = '';
    foreach ($cardModuleOptions as $cardOption) {
        if ($cardOption['value'] === (string) ($card['module_id'] ?? '')) {
            $cardModuleName = $cardOption['label'];
        }
    }

    $cardSummary = $cardModuleName !== '' ? $cardModuleName : 'No module';
@endphp

<div class="cm-classcard"
     data-cm-card
     data-day="{{ $card['day'] }}"
     data-venue="{{ $card['venue_id'] }}"
     data-room="{{ $card['room_id'] }}"
     data-cm-tint="{{ ($card['tint'] ?? 0) % 6 }}"
     data-cm-tutor="{{ $cardType === 'Theory' ? 'tutor' : 'ptutor' }}">

    <div class="cm-classcard__head">
        <button type="button" class="cm-classcard__summary" data-cm-card-toggle>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            <span data-cm-card-summary>{{ $cardSummary }}</span>
        </button>

        <button type="button" class="cm-classcard__act" data-cm-card-copy title="Copy this class">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </button>
        <button type="button" class="cm-classcard__act" data-cm-card-duplicate title="Duplicate here">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5-9 5-9-5z"></path><path d="M3 12l9 5 9-5M3 17l9 5 9-5"></path></svg>
        </button>
        <button type="button" class="cm-classcard__act cm-classcard__act--danger" data-cm-card-remove title="Remove this class">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
        </button>
    </div>

    <div class="cm-classcard__body">
        {{-- Course and group are fixed for the whole sheet and named in the page
             header, so they are not repeated on every card. They still travel
             with it: the hidden inputs at the foot carry the ids `store()`
             writes against. --}}
        <div class="cm-classcard__grid">
            <div class="cm-classfield cm-classfield--wide cm-classfield--module">
                <span>Module</span>
                <select data-cm-field="module" class="cm-classinput">
                    <option value="">Select</option>
                    @foreach($cardModuleOptions as $cardOption)
                        <option value="{{ $cardOption['value'] }}" @selected($cardOption['value'] === (string) ($card['module_id'] ?? ''))>{{ $cardOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cm-classfield cm-classfield--wide">
                <span>Class Type</span>
                <select data-cm-field="class_type" class="cm-classinput">
                    <option value="">Select</option>
                    @foreach($cardTypes as $cardTypeOption)
                        <option value="{{ $cardTypeOption }}" @selected($cardTypeOption === $cardType)>{{ $cardTypeOption }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Theory classes are led by the tutor, everything else by the
                 personal tutor, so only one of these is on screen at a time.
                 Both stay in the DOM: the plans table keeps them in separate
                 columns, and the serialiser reads whatever is there — hiding a
                 field must never clear a value that was already saved. --}}
            <div class="cm-classfield cm-classfield--tutor">
                <span>Tutor</span>
                <select data-cm-field="tutor" class="cm-classinput">
                    <option value="">Select</option>
                    @foreach($cardTutorOptions as $cardOption)
                        <option value="{{ $cardOption['value'] }}" @selected($cardOption['value'] === (string) ($card['tutor_id'] ?? ''))>{{ $cardOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="cm-classfield cm-classfield--ptutor">
                <span>Personal Tutor</span>
                <select data-cm-field="personal_tutor" class="cm-classinput">
                    <option value="">Select</option>
                    @foreach($cardPTutorOptions as $cardOption)
                        <option value="{{ $cardOption['value'] }}" @selected($cardOption['value'] === (string) ($card['personal_tutor_id'] ?? ''))>{{ $cardOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- `store()` splits this on " - " into start and end time. --}}
            <div class="cm-classfield">
                <span>Time</span>
                <input type="text" data-cm-field="time" class="cm-classinput cm-classtime" placeholder="10:15 - 11:15" value="{{ $card['time'] }}">
            </div>

            <div class="cm-classfield">
                <span>Submission</span>
                <input type="text" data-cm-field="submission" class="cm-classinput cm-classdate" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY" value="{{ $card['submission'] }}" readonly>
            </div>

            <div class="cm-classfield">
                <span>Virtual Room</span>
                <input type="text" data-cm-field="virtual_room" class="cm-classinput" placeholder="Meeting link" value="{{ $card['virtual_room'] }}">
            </div>

            <div class="cm-classfield">
                <span>Note</span>
                <input type="text" data-cm-field="note" class="cm-classinput" placeholder="Optional note" value="{{ $card['note'] }}">
            </div>
        </div>
    </div>

    {{-- 0 for a card that has never been saved; `store()` inserts those and
         updates the rest. --}}
    <input type="hidden" data-cm-field="existing_id" value="{{ $card['existing_id'] ?? 0 }}">
    <input type="hidden" data-cm-field="course" value="{{ $card['course_id'] }}">
    <input type="hidden" data-cm-field="group" value="{{ $card['group_id'] }}">
</div>
