<!-- 검색 필터 -->
<div class="row">
    <div class="col-12 col-md-6">
        <x-form-hor>
            <x-form-label>코드</x-form-label>
            <x-form-item>
                {!! xInputText()
                    ->setWire('model.defer',"filter.code")
                    ->setWidth("small")
                !!}
            </x-form-item>
        </x-form-hor>
    </div>
    <div class="col-12 col-md-6">

    </div>
</div>
