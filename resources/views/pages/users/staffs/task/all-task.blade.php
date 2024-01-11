@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 mt-8">
            <div class="intro-y flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">All Pending Tasks</h2>
                <a href="{{ route('task.manager') }}" class="ml-auto btn btn-primary text-white">
                    Back to Task Manager
                </a>
            </div>
            <div class="grid grid-cols-12 gap-6 mt-8">
                @if(!empty($processTasks))
                    <div class="col-span-12">
                        <div id="userPendingTaskAccordion" class="accordion mb-8">
                            @foreach($processTasks as $process_id => $process)
                                @if($process['outstanding_tasks'] > 0)
                                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                                        <div id="userPendingTaskAccordion-{{ $loop->index }}" class="accordion-header">
                                            <button class="accordion-button collapsed relative w-full text-lg font-semibold px-5 flex items-center" type="button" data-tw-toggle="collapse" data-tw-target="#userPendingTaskAccordion-collapse-{{ $loop->index }}" aria-expanded="false" aria-controls="userPendingTaskAccordion-collapse-{{ $loop->index }}">
                                                {{ $process['name'] }} 
                                                <span class="w-10 h-10 justify-center items-center inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-auto relative">{{ $process['outstanding_tasks'] }}</span>
                                            </button>
                                        </div>
                                        <div id="userPendingTaskAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="userPendingTaskAccordion-{{ $loop->index }}" data-tw-parent="#userPendingTaskAccordion">
                                            <div class="accordion-body px-5 border-t pt-5">
                                                <div class="grid grid-cols-12 gap-6">
                                                    @if(isset($process['tasks']) && !empty($process['tasks']))
                                                        @foreach($process['tasks'] as $task_id => $pts)
                                                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                                                                <a href="{{ route('task.manager.show', $task_id) }}" class="intro-x block">
                                                                    <div class="box px-5 py-3 mb-3 flex items-center zoom-in bg-success-soft-1">
                                                                        <div class="mr-auto">
                                                                            <div class="font-medium">{{ $pts->name }}</div>
                                                                            @if(!empty($pts->short_description))
                                                                                <div class="text-slate-500 text-xs mt-0.5">{{ $pts->short_description }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="w-10 h-10 rounded-full bg-primary text-white text-danger inline-flex justify-center items-center font-medium">{{ $pts->pending_task }}</div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach 
                        </div>
                    </div>
                @else 
                    <div class="col-span-12">
                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> There are no pending Process Task found.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
