<x-theme theme="admin.sidebar">
    <x-theme-layout>
        <!-- Module Title Bar -->
        {{-- @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif --}}
        <!-- end -->

        <div class="relative">
            <div class="absolute right-0 bottom-4">
                <div class="btn-group">
                    <x-button id="btn-livepopup-manual" secondary wire:click="$emit('popupManualOpen')">메뉴얼</x-button>
                    <a href="/_admin/modules/store" class="btn btn-success">스토어</a>
                    <x-button id="btn-livepopup-create" primary wire:click="$emit('popupFormOpen')">
                        등록
                    </x-button>
                </div>
            </div>
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

        <!-- 목록 출력 -->
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
