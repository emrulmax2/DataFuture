@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Dashboard for <strong>{{ $user->name }}</strong></h2>
        {{-- <div class="ml-auto flex justify-end">
            <a style="float: right;" href="#" data-id="{{ $user->id }}" class="btn btn-success text-white w-auto">Download Pdf</a>
            <input type="hidden" name="user_id" value="{{ $user->id }}"/>
        </div> --}}
    </div>
    <!-- BEGIN: Profile Info -->
    <div class="flex flex-col lg:flex-row intro-y m-5 justify-center items-center">
        @foreach($user->roles as $role)
            @if($role->type=="Staff")
            <a href="{{ route("useraccess.staff",[$user->id,$role->id]) }}" class="btn  shadow-lg border-0 bg-white mr-3 inline-block p-5  w-48 h-48 hover-bg-success hover-text-white">
            @elseif($role->type=="Tutor")
            <a href="{{ route("useraccess.staff",[$user->id,$role->id]) }}" class="btn  shadow-lg border-0 bg-white mr-3 inline-block p-5  w-48 h-48 hover-bg-success hover-text-white">
            @elseif($role->type=="Admin")
            <a href="{{ route("useraccess.staff",[$user->id,$role->id]) }}" class="btn  shadow-lg border-0 bg-white mr-3 inline-block p-5  w-48 h-48 hover-bg-success hover-text-white">
            @endif
                <i class="w-24 h-24 ml-6 text-slate-500 hover-text-white" data-lucide="{{  $role->icon }}"></i>
                <span class="block text-lg text-slate-500 font-semibold">{{  $role->display_name }}</span>
            </a>
        @endforeach
    </div>
@endsection