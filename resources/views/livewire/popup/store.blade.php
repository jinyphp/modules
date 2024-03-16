<div>
    {{-- loading 화면 처리 --}}
    <x-loading-indicator/>
    store....

    <!-- 팝업 데이터 수정창 -->
    @if ($popup)
    <x-table-dialog-modal wire:model="popup" maxWidth="2xl">

        <x-slot name="title">
            {{ $item['title'] }}
        </x-slot>

        <x-slot name="content">
            <div>
                {{$item['description']}}
            </div>
            <div>
                {{$item['url']}}
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-flex-between>
                <div>
                    @if ($mode == 'uninstall')
                        <x-button danger wire:click="remove">제거</x-button>
                    @endif
                </div>
                <div>
                    <x-button secondary wire:click="popupInstallClose">닫기</x-button>
                    @if($item['installed'])
                        @if($ext == "zip")
                        <x-button primary wire:click="upgrade">Upgrade</x-button>
                        @else
                        <x-button primary wire:click="repoPull">Pull</x-button>
                        @endif
                    @else
                        @if($ext == "zip")
                        <x-button primary wire:click="download">설치</x-button>
                        @else
                        <x-button primary wire:click="repoClone">Clone</x-button>
                        @endif
                    @endif
                </div>
            </x-flex-between>

        </x-slot>
    </x-table-dialog-modal>
    @endif

</div>
