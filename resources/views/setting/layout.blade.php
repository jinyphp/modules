<x-theme theme="admin.sidebar">
    <x-theme-layout>

        <!-- Module Title Bar -->
        @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif
        <!-- end -->

        @livewire('WireConfigPHP', ['actions'=>$actions])

        {{-- SuperAdmin Actions Setting --}}
        @livewire('setActionRule', ['actions'=>$actions])

    </x-theme-layout>
</x-theme>
