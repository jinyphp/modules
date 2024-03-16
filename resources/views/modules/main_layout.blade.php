<div>
    <x-loading-indicator/>

    {{-- 필터를 적용시 filter.blade.php 를 읽어 옵니다. --}}
    @if (isset($actions['view']['filter']))
        @includeIf($actions['view']['filter'])
    @endif

    @if (session()->has('message'))
        <div class="alert alert-success">{{session('message')}}</div>
    @endif

    <div class="row">
        @if (isset($actions['view']['list']))
            @includeIf($actions['view']['list'])
        @endif
    </div>


    {{-- 선택삭제 --}}
    @include("jinytable::livewire.popup.delete")

    {{-- 퍼미션 알람--}}
    @include("jinytable::error.popup.permit")

</div>
