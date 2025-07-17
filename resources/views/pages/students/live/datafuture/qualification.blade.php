@if($df_qualification_fields->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
        @foreach($df_qualification_fields as $dfld)
            @php 
                $type = (isset($dfld->field->type) && !empty($dfld->field->type) ? $dfld->field->type : 'text');
                $value = (isset($dfld->field_value) && !empty($dfld->field_value) ? trim($dfld->field_value) : '');
            @endphp
            <div class="grid-column">
                <div class="col-span-4 text-slate-500 uppercase">{{ (isset($dfld->field->name) && !empty($dfld->field->name) ? $dfld->field->name : 'ID: '.$dfld->datafuture_field_id) }}</div>
                <div class="col-span-8 font-medium">{{ (!empty($value) ? $value : '---') }}</div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not found for qualifications. Please add fields under course.
    </div>
@endif
