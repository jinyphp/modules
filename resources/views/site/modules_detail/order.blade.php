<div>
    <div class="row">
        <div class="col-12 col-md-9">

            <div class="row">
                    @foreach ($plan as $item)
                    <div class="col-4">


                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">
                                    {{ $item['title'] }}
                                </div>
                                <div class="card-subtitle">
                                    {{ $item['description'] }}
                                </div>
                            </div>

                            <div class="card-body">
                                {{$item['detail']}}
                                {{$item['price']}} / {{$item['unit']}}
                            </div>

                            <div class="card-footer">
                                <button class="btn btn-primary"
                                    wire:click="choose({{$item['id']}})">
                                    플렌 선택
                                </button>
                            </div>
                        </div>


                    </div>
                    @endforeach

            </div>
        </div>

        {{-- 오른쪽 영역 --}}
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">라이센스 신청</h5>
                    <p class="card-text">라이센스 신청 후 라이센스 키를 발급받으세요.</p>

                    @if($plan_id)

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">플렌 : {{ $plan[$plan_id]['title'] }}</li>
                        <li class="list-group-item">가격 : {{ $plan[$plan_id]['price'] }}</li>
                        <li class="list-group-item">단위 : {{ $plan[$plan_id]['unit'] }}</li>
                    </ul>
                    @endif

                </div>

                <div class="card-footer gap-2">


                    @if($plan_id)
                    <button class="btn btn-primary">
                        결제하기
                    </button>

                    <span class="card-text">
                        보유금액 : (충전하기)
                    </span>

                    @endif
                </div>

            </div>
        </div>
    </div>

</div>
