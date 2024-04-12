@if(count($modules)>0)
    <div class="row">
    @foreach ($modules as $item)
    <div class="col-md-3">
        <div class="card  ">
            <div class="position-relative">
                <div class="position-absolute top-3 end-0">
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="">
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">
                                View full
                            </a>
                            <!-- item 업그레이드-->
                            @if($item->installed)
                                @if($item->ext == "zip")
                                <a href="javascript:void(0);" class="dropdown-item"
                                    wire:click="$emit('install','{{$item->code}}')">
                                    Upgrade
                                </a>
                                @else
                                <a href="javascript:void(0);" class="dropdown-item"
                                    wire:click="$emit('install','{{$item->code}}')">
                                    Pull
                                </a>
                                @endif
                            @endif

                            <!-- item-->
                            @if($item->installed)
                            <a href="javascript:void(0);" class="dropdown-item text-red-700"
                                wire:click="$emit('uninstall','{{$item->code}}')">
                                제거
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <div class="card-header">
                <h5 class="card-title mb-0">{{$item->title}}</h5>
                {{--
                <h5 class="card-title ">{!! $popupEdit($item, $item->title) !!}</h5>
                --}}
                <p>{{$item->description}}</p>


            </div>
            <div class="card-body">

                @if($item->git)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    style="display: inline-block"
                    class="bi bi-github"
                    viewBox="0 0 16 16">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                </svg>
                @endif

                @if($item->version)
                <span>Version : {{$item->version}}</span>
                @endif

            </div> <!-- end card-body-->
            <div class="card-footer">
                @if($item->installed)
                    @if($item->enable)
                        <button class="btn btn-danger my-2 cursor-pointer"
                            wire:click="$emit('enable','{{$item->code}}')">비활성화
                        </button>
                    @else
                        <button class="btn btn-primary cursor-pointer"
                        wire:click="$emit('disable','{{$item->code}}')">활성화
                        </button>
                    @endif
                    <span>모듈이 설치되어 있습니다.</span>
                @else
                    @if($item->ext == "zip")
                        <button class="btn btn-primary"
                            wire:click="$emit('install','{{$item->code}}')">설치</button>
                    @else
                        <button class="btn mb-1 btn-github"
                            wire:click="$emit('install','{{$item->code}}')">
                                <div class="flex justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                                    </svg>
                                    <span class="ml-2">복제</span>
                                </div>
                        </button>
                    @endif
                    <span>모듈이 아직 설치가 되어 있지 않습니다.</span>
                @endif
            </div>
        </div> <!-- end card-->
    </div>
    @endforeach
    </div>
@else
    <div class="alert alert-primary" role="alert">
        등록된 <strong>모듈</strong>이 없습니다.
        직접 다운로드 경로를 입력하거나, 스토어를 통하여 다운로드 받으세요.
    </div>
@endif

