<div id="df-accordion-module" class="lcc-accordion lcc-accordion-boxed">
    @php 
        $m = 1;
    @endphp
    @foreach($df_modules_fields as $module)
        <div class="lcc-accordion-item">
            <div id="df-accr-module-content-{{$m}}" class="lcc-accordion-header">
                <button class="lcc-accordion-button lcc-collapsed bg_color_2" type="button">
                    {{ $module->name.' - '.($m < 10 ? '0'.$m : $m)}}
                    <span class="accordionCollaps"></span>
                </button>
            </div>
            <div id="df-accr-module-collapse-{{$m}}" class="lcc-accordion-collapse lcc-collapse" style="display: none;">
                <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                    @if(isset($module->df) && $module->df->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                            @foreach($module->df as $dfld)
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
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not found for the Module. Please add fields under Course Module.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @php $m++; @endphp
    @endforeach
</div>