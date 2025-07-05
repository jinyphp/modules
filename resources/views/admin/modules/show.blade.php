@extends('layouts.admin')

@section('title', $module['title'] . ' - 모듈 상세')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.modules.index') }}">모듈 관리</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $module['title'] }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.modules.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cube me-2"></i>{{ $module['title'] }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">패키지명:</th>
                                    <td><code>{{ $module['name'] }}</code></td>
                                </tr>
                                <tr>
                                    <th>버전:</th>
                                    <td><span class="badge bg-primary">{{ $module['version'] }}</span></td>
                                </tr>
                                <tr>
                                    <th>네임스페이스:</th>
                                    <td><code>{{ $module['namespace'] }}</code></td>
                                </tr>
                                @if($module['author'])
                                <tr>
                                    <th>작성자:</th>
                                    <td>{{ $module['author'] }}</td>
                                </tr>
                                @endif
                                @if($module['license'])
                                <tr>
                                    <th>라이선스:</th>
                                    <td>{{ $module['license'] }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">경로:</th>
                                    <td><code>{{ $module['path'] }}</code></td>
                                </tr>
                                <tr>
                                    <th>ServiceProvider:</th>
                                    <td><code>{{ basename($module['serviceProvider']) }}</code></td>
                                </tr>
                                @if(!empty($module['dependencies']))
                                <tr>
                                    <th>의존성:</th>
                                    <td>
                                        @foreach($module['dependencies'] as $dep)
                                            <span class="badge bg-info me-1">{{ $dep }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($module['description'])
                    <div class="mt-4">
                        <h6>설명</h6>
                        <p class="text-muted">{{ $module['description'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">모듈 상태</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input module-toggle" type="checkbox"
                               id="module-toggle-{{ $module['vendor'] }}-{{ $module['package'] }}"
                               data-vendor="{{ $module['vendor'] }}"
                               data-package="{{ $module['package'] }}"
                               checked>
                        <label class="form-check-label" for="module-toggle-{{ $module['vendor'] }}-{{ $module['package'] }}">
                            모듈 활성화
                        </label>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            모듈을 비활성화하면 해당 모듈의 기능이 사용되지 않습니다.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">빠른 작업</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i> 업데이트 확인
                        </button>
                        <button class="btn btn-outline-info btn-sm">
                            <i class="fas fa-cog me-1"></i> 설정
                        </button>
                        <button class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-file-code me-1"></i> 문서 보기
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 모듈 토글 기능
    const toggle = document.querySelector('.module-toggle');
    if (toggle) {
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
                body: JSON.stringify({
                    active: isActive
                })
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
                showAlert('danger', '오류가 발생했습니다.');
            });
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);

        // 3초 후 자동 제거
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
});
</script>
@endpush
@endsection
