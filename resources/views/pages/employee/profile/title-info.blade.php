<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    @if(isset(auth()->user()->priv()['login_as_user']) && auth()->user()->priv()['login_as_user'] == 1)
    <div class="ml-auto flex justify-end">
        <a href="{{ route('impersonate', $employee->user_id) }}" class="btn btn-success text-white w-auto mr-1 mb-0">
            Login As User <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
        </a>
    </div>
    @endif
</div>