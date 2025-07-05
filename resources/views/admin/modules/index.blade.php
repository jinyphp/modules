@extends('layouts.admin')

@section('title', '모듈 관리')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">모듈 관리</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.modules.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt"></i> 새로고침
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-start">
        @forelse($modules as $module)
            <div class="col d-flex align-items-stretch">
                <div class="card rounded bg-white h-100 w-100 overflow-hidden" style="border: 1px solid #dee2e6;">
                    @if(!empty($module['info']['image']))
                        <img src="{{ asset($module['info']['image']) }}"
                             class="card-img-top object-fit-cover"
                             style="height:180px; object-fit:cover;"
                             alt="{{ $module['title'] }}">
                    @else
                        <div style="height:180px; background-color: #868e96; display: flex; align-items: center; justify-content: center;">
                            <span style="color: #f1f3f5; font-size: 1.5rem; font-weight: 600; text-align: center; width: 90%; word-break: break-all;">
                                {{ $module['title'] }}
                            </span>
                        </div>
                    @endif
                    <div class="card-body p-4" style="min-height: 180px; max-height: 260px; overflow-y: auto;">
                        <h5 class="card-title text-primary">{{ $module['title'] }}</h5>
                        <span class="badge bg-primary mb-2">v{{ $module['version'] }}</span>
                        <p class="card-text text-muted mb-3">
                            {{ Str::limit($module['description'], 100) ?: '설명이 없습니다.' }}
                        </p>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-cube me-1"></i> {{ $module['name'] }}
                        </div>
                        @if($module['author'])
                            <div class="small text-muted mb-2">
                                <i class="fas fa-user me-1"></i> {{ $module['author'] }}
                            </div>
                        @endif
                        @if($module['license'])
                            <div class="small text-muted">
                                <i class="fas fa-certificate me-1"></i> {{ $module['license'] }}
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.modules.show', ['vendor' => $module['vendor'], 'package' => $module['package']]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-info-circle"></i> 상세보기
                            </a>
                            <div class="form-check form-switch">
                                <input class="form-check-input module-toggle" type="checkbox"
                                       id="module-{{ $module['vendor'] }}-{{ $module['package'] }}"
                                       data-vendor="{{ $module['vendor'] }}"
                                       data-package="{{ $module['package'] }}"
                                       checked>
                                <label class="form-check-label" for="module-{{ $module['vendor'] }}-{{ $module['package'] }}">
                                    활성화
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">설치된 모듈이 없습니다</h4>
                    <p class="text-muted">modules 디렉토리에 모듈을 추가해주세요.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.module-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const vendor = this.dataset.vendor;
            const package = this.dataset.package;
            const isActive = this.checked;

            fetch(`/admin/modules/${vendor}/${package}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ active: isActive })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    this.checked = !isActive;
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isActive;
                showAlert('danger', '처리 중 오류가 발생했습니다.');
            });
        });
    });

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => alertDiv.remove(), 3000);
    }
});
</script>
@endpush
@endsection
