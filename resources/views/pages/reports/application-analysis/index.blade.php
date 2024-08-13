@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Application Analysis Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form action="{{ route('report.application.analysis') }}" method="post">
            @csrf
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Semester</label>
                    <select id="semesters" name="semesters" class="w-full tom-selects">
                        <option value="">Please Select</option>
                        @if(!empty($semesters))
                            @foreach($semesters as $sem)
                                <option {{ ($searched_semesters == $sem->id ? 'Selected' : '') }} value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-9 pt-7">
                    <button type="submit" class="btn btn-success w-auto text-white px-5" >Generate</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden pt-5">
            @if($reports)
                @php 
                    $no_of_applicants = (isset($reports['no_of_applicants']) && $reports['no_of_applicants'] > 0 ? $reports['no_of_applicants'] : 0);
                    $personal_data = (isset($reports['personal_data']) && !empty($reports['personal_data']) ? $reports['personal_data'] : []);
                    $gender = (isset($personal_data['gender']) && !empty($personal_data['gender']) ? $personal_data['gender'] : []);
                    $age = (isset($personal_data['age']) && !empty($personal_data['age']) ? $personal_data['age'] : []);
                    $avg_age = (isset($personal_data['avg_age']) && $personal_data['avg_age'] > 0 ? $personal_data['avg_age'] : 0);
                    $nationality = (isset($personal_data['nationality']) && !empty($personal_data['nationality']) ? $personal_data['nationality'] : []);
                    
                    $course_entry = (isset($reports['course_entry']) && !empty($reports['course_entry']) ? $reports['course_entry'] : []);
                    $course_data = (isset($reports['course_data']) && !empty($reports['course_data']) ? $reports['course_data'] : []);
                    $courses = (isset($course_data['courses']) && !empty($course_data['courses']) ? $course_data['courses'] : []);

                    $evening_weekends = (isset($course_data['evening_weekends']) && $course_data['evening_weekends'] > 0 ? $course_data['evening_weekends'] : 0);
                    $weekdays = (isset($course_data['weekdays']) && $course_data['weekdays'] > 0 ? $course_data['weekdays'] : 0);
                @endphp
                <div class="grid grid-cols-12 gap-x-6 gap-y-4">
                    <div class="col-span-12 sm:col-span-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium uppercase">Personal Data Analysis</h3>
                            <a href="{{ route('report.application.analysis.print.pd', $searched_semesters) }}" class="btn btn-outline-secondary w-auto btn-sm"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Download PDF</a>
                        </div>
                        <table class="table table-sm table-bordered mb-2">
                            <tr>
                                <th colspan="3" class="text-left">Gender</th>
                            </tr>
                            <tr>
                                <td class="text-left">Male Applicants</td>
                                <td class="w-1/6">{{ (isset($gender['male']) && $gender['male'] > 0 ? $gender['male'] : 0) }}</td>
                                <td class="w-1/6">{{ (isset($gender['male']) && $gender['male'] > 0 && $no_of_applicants > 0 ? number_format(($gender['male'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                            </tr>
                            <tr>
                                <td class="text-left">Female Applicants</td>
                                <td class="w-1/6">{{ (isset($gender['female']) && $gender['female'] > 0 ? $gender['female'] : 0) }}</td>
                                <td class="w-1/6">{{ (isset($gender['female']) && $gender['female'] > 0 && $no_of_applicants > 0 ? number_format(($gender['female'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                            </tr>
                            <tr>
                                <td class="text-left">Other Applicants</td>
                                <td class="w-1/6">{{ (isset($gender['other']) && $gender['other'] > 0 ? $gender['other'] : 0) }}</td>
                                <td class="w-1/6">{{ (isset($gender['other']) && $gender['other'] > 0 && $no_of_applicants > 0 ? number_format(($gender['other'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                            </tr>
                        </table>

                        @if(!empty($nationality))
                        <table class="table table-sm table-bordered mb-2">
                            <tr>
                                <th colspan="3" class="text-left">Nationality</th>
                            </tr>
                            @foreach($nationality as $nation)
                                <tr>
                                    <td class="text-left">{{ (isset($nation['name']) && !empty($nation['name']) ? $nation['name'] : '') }}</td>
                                    <td class="w-1/6">{{ (isset($nation['applicants']) && $nation['applicants'] > 0 ? $nation['applicants'] : 0) }}</td>
                                    <td class="w-1/6">{{ (isset($nation['applicants']) && $nation['applicants'] > 0 && $no_of_applicants > 0 ? number_format(($nation['applicants'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                </tr>
                            @endforeach
                        </table>
                        @endif

                        <table class="table table-sm table-bordered mb-2">
                            <tr>
                                <th colspan="3" class="text-left">Age</th>
                            </tr>
                            @if(!empty($age))
                                @foreach($age as $label => $ag)
                                    <tr>
                                        <td class="text-left">Applicants Aged {{ $label }}</td>
                                        <td class="w-1/6">{{ $ag }}</td>
                                        <td class="w-1/6">{{ ($ag > 0 && $no_of_applicants > 0 ? number_format(($ag / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr>
                                <td class="text-left">Mean Application Age</td>
                                <td class="w-1/6">{{ ($avg_age > 0 ? $avg_age : '') }}</td>
                                <td class="w-1/6">&nbsp;</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        @if(!empty($course_entry))
                            <h3 class="font-medium mb-3 uppercase">Course Entry Validation Analysis</h3>
                            <table class="table table-sm table-bordered mb-5">
                                <tr>
                                    <td class="text-left">Academic Entry</td>
                                    <td class="w-1/6">{{ (isset($course_entry['academic_entry']) && $course_entry['academic_entry'] > 0 ? $course_entry['academic_entry'] : 0) }}</td>
                                    <td class="w-1/6">{{ (isset($course_entry['academic_entry']) && $course_entry['academic_entry'] > 0 && $no_of_applicants > 0 ? number_format(($course_entry['academic_entry'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                </tr>
                                <tr>
                                    <td class="text-left">Mature Entry</td>
                                    <td class="w-1/6">{{ (isset($course_entry['mature_entry']) && $course_entry['mature_entry'] > 0 ? $course_entry['mature_entry'] : 0) }}</td>
                                    <td class="w-1/6">{{ (isset($course_entry['mature_entry']) && $course_entry['mature_entry'] > 0 && $no_of_applicants > 0 ? number_format(($course_entry['mature_entry'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                </tr>
                            </table>
                        @endif

                        <h3 class="font-medium mb-3 uppercase">Course Data Analysis</h3>
                        @if(!empty($courses))
                            <table class="table table-sm table-bordered mb-5">
                                @foreach($courses as $crs)
                                <tr>
                                    <td class="text-left">{{ (isset($crs['name']) && !empty($crs['name']) ? $crs['name'] : '') }}</td>
                                    <td class="w-1/6">{{ (isset($crs['applicants']) && $crs['applicants'] > 0 ? $crs['applicants'] : 0) }}</td>
                                    <td class="w-1/6">{{ (isset($crs['applicants']) && $crs['applicants'] > 0 && $no_of_applicants > 0 ? number_format(($crs['applicants'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                </tr>
                                @endforeach
                            </table>
                        @endif
                        
                        <table class="table table-sm table-bordered mb-5">
                            <tr>
                                <td class="text-left">All Applicantions For Weekdays</td>
                                <td class="w-1/6">{{ ($weekdays > 0 ? $weekdays : 0) }}</td>
                                <td class="w-1/6">{{ ($weekdays > 0 && $no_of_applicants > 0 ? number_format(($weekdays / $no_of_applicants) * 100, 2) : 0) }}%</td>
                            </tr>
                            <tr>
                                <td class="text-left">All Applicantions For Evening & Weekends</td>
                                <td class="w-1/6">{{ ($evening_weekends > 0 ? $evening_weekends : 0) }}</td>
                                <td class="w-1/6">{{ ($evening_weekends > 0 && $no_of_applicants > 0 ? number_format(($evening_weekends / $no_of_applicants) * 100, 2) : 0) }}%</td>
                            </tr>
                        </table>

                        @if(!empty($courses))
                            @foreach($courses as $crs)
                                <table class="table table-sm table-bordered mb-5">
                                    <tr>
                                        <td class="text-left">{{ (isset($crs['name']) && !empty($crs['name']) ? $crs['name'] : '') }} (Weekdays)</td>
                                        <td class="w-1/6">{{ (isset($crs['weekdays']) && $crs['weekdays'] > 0 ? $crs['weekdays'] : 0) }}</td>
                                        <td class="w-1/6">{{ (isset($crs['weekdays']) && $crs['weekdays'] > 0 && $no_of_applicants > 0 ? number_format(($crs['weekdays'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="text-left">{{ (isset($crs['name']) && !empty($crs['name']) ? $crs['name'] : '') }} (Evening & Weekends)</td>
                                        <td class="w-1/6">{{ (isset($crs['evening_weekends']) && $crs['evening_weekends'] > 0 ? $crs['evening_weekends'] : 0) }}</td>
                                        <td class="w-1/6">{{ (isset($crs['evening_weekends']) && $crs['evening_weekends'] > 0 && $no_of_applicants > 0 ? number_format(($crs['evening_weekends'] / $no_of_applicants) * 100, 2) : 0) }}%</td>
                                    </tr>
                                </table>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
            {{--<pre>
                <?php print_r($reports) ?>
            </pre>--}}
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/application-analysis.js')
@endsection