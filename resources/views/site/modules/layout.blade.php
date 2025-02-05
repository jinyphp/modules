<x-www-app>
    <x-www-layout>
        <x-www-main>

            <x-flex-between>
                <div class="page-title-box">
                    <x-flex class="align-items-center gap-2">
                        <h1 class="align-middle h3 d-inline">
                            모듈 및 페키지
                        </h1>
                        {{-- <x-badge-info>Admin</x-badge-info> --}}
                    </x-flex>
                    <p>
                        모듈을 선택하여 기능을 확장할 수 있습니다.
                    </p>
                </div>

                <div class="page-title-box">
                    <x-breadcrumb-item>
                        {{$actions['route']['uri']}}
                    </x-breadcrumb-item>
                </div>
            </x-flex-between>

            @livewire('jiny-license-store')

        </x-www-main>
    </x-www-layout>
</x-www-app>
