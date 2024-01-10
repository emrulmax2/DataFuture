@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 mt-8">
            <div class="intro-y flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Task Manager</h2>
                <a href="{{ route('dashboard') }}" class="ml-auto btn btn-primary text-white">
                    Back to Dashboard
                </a>
            </div>
            <div class="grid grid-cols-12 gap-6 mt-5">
                @if(!empty($mytasks))
                    @foreach($mytasks as $task_id => $task)
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('task.manager.show', $task_id) }}" class="intro-x block">
                                <div class="box px-5 py-3 mb-3 flex items-center zoom-in">
                                    <div class="mr-auto">
                                        <div class="font-medium">{{ $task->name }}</div>
                                        @if(!empty($task->short_description))
                                            <div class="text-slate-500 text-xs mt-0.5">{{ $task->short_description }}</div>
                                        @endif
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-primary text-white text-danger inline-flex justify-center items-center font-medium">{{ $task->pending_task }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else 
                    <div class="col-span-12">
                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> There are no pending task found.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
