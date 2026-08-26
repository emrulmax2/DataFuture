@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $gradeMeta = [
            'P' => ['color' => '#3E6B34', 'bg' => '#E9F0E6'],
            'M' => ['color' => '#8A6420', 'bg' => '#EFE7DA'],
            'D' => ['color' => '#232528', 'bg' => '#EDEDEA'],
            'R' => ['color' => '#9A5226', 'bg' => '#F5E9DC'],
            'U' => ['color' => '#9A5226', 'bg' => '#F5E9DC'],
            'A' => ['color' => '#9A5226', 'bg' => '#F5E9DC'],
        ];

        $statusClasses = [
            'core' => 'spf-status--core',
            'specialist' => 'spf-status--specialist',
            'optional' => 'spf-status--optional',
        ];

        $completedCount = 0;
        $outstandingCount = 0;
        $totalCount = 0;
        $creditsAchieved = 0;
        $termGroups = [];

        if (!empty($dataSet)) {
            foreach ($dataSet as $moduleName => $resultSet) {
                $totalCount++;
                $current = $resultSet[0];
                $code = isset($current->grade->code) ? trim($current->grade->code) : '';
                $passed = in_array($code, ['P', 'M', 'D'], true);

                if ($passed) {
                    $completedCount++;
                } else {
                    $outstandingCount++;
                }

                $termObject = !empty($current->term_declaration_id) ? $current->term : $current->plan->attenTerm;
                $termId = isset($termObject->id) ? $termObject->id : 0;

                if (!isset($termGroups[$termId])) {
                    $termGroups[$termId] = [
                        'name' => $termObject->name ?? '—',
                        'credits' => 0,
                        'modules' => [],
                    ];
                }

                $credit = (int) ($current->plan->creations->credit_value ?? 0);
                $termGroups[$termId]['credits'] += $credit;
                $termGroups[$termId]['modules'][$moduleName] = $resultSet;

                if ($passed) {
                    $creditsAchieved += $credit;
                }
            }

            krsort($termGroups);
        }
    @endphp


    <div class="spf-page-head spf-page-head--inline">
        <h1 class="spf-h1">Results</h1>
        <span class="spf-chip spf-chip--green spf-chip--lg"><span class="spf-chip__dot spf-chip__dot--sage"></span>Completed {{ $completedCount }}</span>
        <span class="spf-chip spf-chip--cream spf-chip--lg"><span class="spf-chip__dot spf-chip__dot--bronze"></span>Outstanding {{ $outstandingCount }}</span>
        <span class="spf-chip spf-chip--outline spf-chip--lg">Total {{ $totalCount }}</span>
        <div class="spf-spacer"></div>
        <span class="spf-term__score">Total credits achieved: <strong>{{ $creditsAchieved }}</strong></span>
    </div>

    <div class="spf-rtable-wrap">
        <div class="spf-rtable">
            <div class="spf-rtable__head">
                <div>Module</div>
                <div>Credit</div>
                <div>Level</div>
                <div>Status</div>
                <div>Grade</div>
                <div>Attempts</div>
            </div>

            @forelse($termGroups as $termGroup)
                <div class="spf-rtable__termrow">
                    <span class="spf-rtable__termname">{{ $termGroup['name'] }}</span>
                    <span class="spf-rtable__termcount">{{ count($termGroup['modules']) }} {{ count($termGroup['modules']) === 1 ? 'module' : 'modules' }}</span>
                    <div class="spf-spacer"></div>
                    <span class="spf-rtable__termcredits">{{ $termGroup['credits'] }} credits</span>
                </div>

                @foreach($termGroup['modules'] as $moduleName => $resultSet)
                    @php
                        $current = $resultSet[0];
                        $code = isset($current->grade->code) ? trim($current->grade->code) : '';
                        $meta = $gradeMeta[$code] ?? ['color' => '#5E6165', 'bg' => '#EDEDEA'];
                        $attempts = count($resultSet);
                        $unitStatus = strtolower(trim($current->plan->creations->status ?? ''));
                        $statusClass = $statusClasses[$unitStatus] ?? 'spf-status--none';
                    @endphp
                    <div class="spf-rtable__row">
                        <div>
                            <div class="spf-mod__name">{{ $current->plan->creations->module_name }}</div>
                            <div class="spf-mod__meta">
                                {{ $current->plan->creations->code }}
                                &middot; {{ $current->plan->course->body->name ?? 'Unknown' }}
                                &middot; #{{ $current->id }}
                            </div>
                        </div>
                        <div class="spf-cell--credit">{{ $current->plan->creations->credit_value }}</div>
                        <div class="spf-cell--muted">{{ $current->plan->creations->unit_value ?? ($current->plan->creations->level->name ?? '—') }}</div>
                        <div>
                            @if($unitStatus !== '')
                                <span class="spf-status {{ $statusClass }}">{{ ucfirst($unitStatus) }}</span>
                            @else
                                <span class="spf-status spf-status--none">&mdash;</span>
                            @endif
                        </div>
                        <div>
                            <span class="spf-grade" style="color:{{ $meta['color'] }};background:{{ $meta['bg'] }}">{{ $code }} &middot; {{ $current->grade->name }}</span>
                        </div>
                        <div>
                            <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $current->id }}" class="spf-attempts {{ $attempts > 1 ? 'is-multi' : '' }}" title="Attempt history">{{ $attempts }}</a>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="spf-rtable__row" style="grid-template-columns:1fr">
                    <div class="spf-cell--muted">No results have been published yet.</div>
                </div>
            @endforelse

            <div class="spf-rtable__foot">
                Showing <strong>{{ $totalCount }}</strong> of <strong>{{ $totalCount }}</strong> results
            </div>
        </div>
    </div>

    @if($prev_result_count > 0)
        <section class="spf-section" style="margin-top:26px">
            <div class="spf-section__head">
                <h2 class="spf-h2">Previous results</h2>
                <span class="spf-section__note">Results carried over from an earlier enrolment</span>
            </div>
            <div class="spf-panel">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm-AN" class="xl:flex sm:mr-auto">
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                            <input id="query-AN" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0" placeholder="Search...">
                        </div>
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                            <select id="status-AN" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto">
                                <option selected value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go-AN" type="button" class="btn btn-primary w-full sm:w-16">Go</button>
                            <button id="tabulator-html-filter-reset-AN" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1">Reset</button>
                        </div>
                    </form>
                    <div class="flex mt-5 sm:mt-0">
                        <button id="tabulator-print-AN" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <div class="dropdown w-1/2 sm:w-auto">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="studentNotesListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </section>
    @endif

    <!-- BEGIN: Attempt history modals -->
    @if($dataSet)
        @foreach($dataSet as $moduleName => $resultSet)
            <div id="callLockModal{{ $resultSet[0]->id }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="font-medium text-base mr-auto">Attempt history</h2>
                            <a data-tw-dismiss="modal" href="javascript:;">
                                <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                            </a>
                        </div>
                        <div class="modal-body overflow-x-auto">
                            <div class="mb-4">
                                <div class="spf-field__label">Module</div>
                                <div class="spf-field__value" style="font-weight:600">{{ $resultSet[0]->plan->creations->module_name }}</div>
                                <div class="spf-field__value spf-field__value--muted">Level {{ $resultSet[0]->plan->creations->level->name }}</div>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Term</th>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Code</th>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Created</th>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Published</th>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Grade</th>
                                        <th class="font-medium px-5 py-3 border-b-2 border-l border-r border-t whitespace-nowrap">Outcome</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultSet as $result)
                                        @php
                                            $termName = !empty($result->term_declaration_id)
                                                ? $result->term->name
                                                : $result->plan->attenTerm->name;
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ $termName }}</td>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ $result->module_code ?: $result->plan->creations->code }}</td>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ date('d M Y, h:i a', strtotime($result->created_at)) }}</td>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ date('d M Y, h:i a', strtotime($result->published_at)) }}</td>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ $result->grade->code }}</td>
                                            <td class="px-3 py-3 border-b border-l border-r border-t">{{ $result->grade->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    <!-- END: Attempt history modals -->
@endsection

@section('script')
    @vite('resources/js/student-results-frontend.js')
@endsection
