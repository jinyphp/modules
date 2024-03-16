<div>
    <div class="row">
        <div class="col-6">

            <x-navtab class="mb-3 nav-bordered">

                <!-- formTab -->
                <x-navtab-item class="show active" >

                    <x-navtab-link class="rounded-0 active">
                        <span class="d-none d-md-block">스토어</span>
                    </x-navtab-link>

                    <form class="form-horizontal">
                        <div class="row mb-3">
                            <label for="inputEmail3" class="col-3 col-form-label">스토어URL</label>
                            <div class="col-9">
                                <input type="text"
                                class="form-control"
                                id="inputEmail3"
                                placeholder="스토어 서버의 url을 입력해 주세요."
                                wire:model.defer="forms.url"
                                >
                            </div>
                        </div>
                    </form>

                </x-navtab-item>
                <!-- tab end -->

            </x-navtab>

        </div>
    </div>

</div>
