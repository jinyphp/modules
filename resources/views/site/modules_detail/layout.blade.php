<x-www-app>
    <x-www-layout>
        <x-www-main>

        <x-flex-between>
            <div class="page-title-box">
                <x-flex class="align-items-center gap-2">
                    <h1 class="align-middle h3 d-inline">
                        모듈 라이센스
                    </h1>
                </x-flex>
                <p>
                    라이센스를 구매합니다.
                </p>
            </div>

            <div class="page-title-box">
                <x-breadcrumb-item>
                    {{$actions['route']['uri']}}
                </x-breadcrumb-item>

            </div>
        </x-flex-between>



        @livewire('jiny-license-store-detail',[
            'code'=>$code
        ])



        </x-www-main>

    </x-www-layout>
</x-www-app>
