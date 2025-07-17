<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">COURSE</div>
        <div class="col-span-8 font-medium">{{ $course->name }}</div>
    </div>
    @if($df_course_fields->count() > 0)
        @foreach($df_course_fields as $dfld)
            @php 
                $type = (isset($dfld->field->type) && !empty($dfld->field->type) ? $dfld->field->type : 'text');
                $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');
            @endphp

            <div class="grid-column">
                <div class="col-span-4 text-slate-500 uppercase">{{ (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : 'ID: '.$dfld->datafuture_field_id) }}</div>
                <div class="col-span-8 font-medium">{{ (!empty($value) ? $value : '---') }}</div>
            </div>
        @endforeach
    @endif
</div>

{{--<div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-5 gap-4 gap-y-2">
    <div class="grid-column">
        <label class="form-label uppercase">Course <span class="text-danger">*</span></label>
        <select name="COURSEID" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($courses->count() > 0)
                @foreach($courses as $crs)
                    <option {{ ($course_id == $crs->id ? 'Selected' : '') }} value="{{ $crs->id }}">{{ $crs->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
    @if($df_course_fields->count() > 0)
        @foreach($df_course_fields as $dfld)
            @php 
                $type = (isset($dfld->field->type) && !empty($dfld->field->type) ? $dfld->field->type : 'text');
                $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : null);
            @endphp
            <div class="grid-column">
                <label class="form-label uppercase">{{ (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : 'ID: '.$dfld->datafuture_field_id) }}</label>
                <input type="{{ ($type == 'number' ? 'number' : 'text') }}" 
                    name="df[course][$student->id][]" 
                    value="{{ $value }}" 
                    class="w-full form-control {{ ($type == 'date' ? 'datepicker' : '') }}"  
                    {{ ($type == 'number' ? "step=any" : '') }} 
                    {{ ($type == 'date' ? "data-format=DD-MM-YYYY data-single-mode=true" : '') }} 
                />
            </div>
        @endforeach
    @else 
        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not found for course. Please add fields under course.
        </div>
    @endif
</div>--}}