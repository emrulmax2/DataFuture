@if($stuloads->count() > 0)
    @foreach($stuloads as $stu)
        <fieldset class="modInstSet mb-{{ !$loop->last ? '5' : '0' }}">
            <legend class="font-medium">Session Year {{ ($loop->index < 9 ? '0'.($loop->index + 1) : ($loop->index + 1)) }}</legend>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium uppercase">SESSIONYEARID</div>
                        <div class="col-span-8 font-medium text-end md:text-left">{{ $stu->course_creation_instance_id }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium uppercase">OWNSESSIONID</div>
                        <div class="col-span-8 font-medium text-end md:text-left">{{ (isset($stu->instance->firstTerm->termDeclaration->name) && !empty($stu->instance->firstTerm->termDeclaration->name) ? $stu->instance->firstTerm->termDeclaration->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium uppercase">SYENDDATE</div>
                        <div class="col-span-8 font-medium text-end md:text-left">{{ (isset($stu->enddate) && !empty($stu->enddate) ? date('Y-m-d', strtotime($stu->enddate)) : (isset($stu->instance->end_date) && !empty($stu->instance->end_date) ? date('Y-m-d', strtotime($stu->instance->end_date)) : '')) }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium uppercase">SYSTARTDATE</div>
                        <div class="col-span-8 font-medium text-end md:text-left">{{ (isset($stu->periodstart) && !empty($stu->periodstart) ? date('Y-m-d', strtotime($stu->periodstart)) : '') }}</div>
                    </div>
                </div>
            </div>
        </fieldset>
    @endforeach
@else 
    <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Session year not found. Please add instances first.
    </div>
@endif