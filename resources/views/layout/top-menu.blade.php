@extends('../layout/main')

@section('head')
    @yield('subhead')
@endsection

@section('content')
    @include('../layout/components/global-header')


    <div class="content content--top-nav">
        @yield('subcontent')
    </div>
@endsection
