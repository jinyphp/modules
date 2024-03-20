<x-theme theme="admin.sidebar">
    <x-theme-layout>
        <!-- Module Title Bar -->
        {{-- @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif --}}
        <!-- end -->



        <div class="btn-group mb-3">
            <button id="btn-livepopup-manual" class="btn btn-secondary" wire:click="$emit('popupManualOpen')">메뉴얼</button>
            <a href="/_admin/modules/store" class="btn btn-success">스토어</a>
            <button id="btn-livepopup-create" class="btn btn-primary" wire:click="$emit('popupFormOpen')">
                등록
            </button>
        </div>


        @push('scripts')
        <script>
            document.querySelector("#btn-livepopup-create").addEventListener("click",function(e){
                e.preventDefault();
                Livewire.emit('popupFormCreate');
            });

            document.querySelector("#btn-livepopup-manual").addEventListener("click",function(e){
                e.preventDefault();
                Livewire.emit('popupManualOpen');
            });
        </script>
        @endpush

        <!-- 모듈 목록 -->
        @livewire('ModuleList', ['actions'=>$actions])


        @livewire('WirePopupForm', ['actions'=>$actions])


        @livewire('PopupManual')



        @livewire('ModuleInstall')

        {{-- SuperAdmin Actions Setting --}}
        @if(Module::has('Actions'))
            @livewire('setActionRule', ['actions'=>$actions])
        @endif

    </x-theme-layout>
</x-theme>
