@if($otherClubs->count() > 0)
    <div class="mb-3">
        <p class="text-muted mb-0">
            <i class="fas fa-info-circle me-1"></i> 
            Hiển thị <strong>{{ $otherClubs->count() }}</strong> câu lạc bộ
        </p>
    </div>
    <div class="row">
        @foreach($otherClubs as $club)
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm club-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @php
                            $logoUrl = null;
                            $hasLogo = false;
                            if ($club->logo) {
                                $logoPath = $club->logo;
                                if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                                    $logoUrl = $logoPath;
                                    $hasLogo = true;
                                } else {
                                    $fullPath = public_path($logoPath);
                                    if (file_exists($fullPath)) {
                                        $logoUrl = asset($logoPath);
                                        $hasLogo = true;
                                    }
                                }
                            }
                        @endphp
                        <div class="club-logo me-3">
                            @if($hasLogo && $logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $club->name }}" class="club-logo-img" 
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span class="club-logo-fallback" style="display: none;">{{ substr($club->name, 0, 2) }}</span>
                            @else
                                <span class="club-logo-fallback">{{ substr($club->name, 0, 2) }}</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1 fw-bold">{{ $club->name }}</h5>
                            <small class="text-muted d-flex align-items-center">
                                <i class="fas fa-user-friends me-1"></i> {{ $club->members_count }} thành viên
                            </small>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8')), 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Đang hoạt động
                        </span>
                        <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> Xem & Tham gia
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Không tìm thấy câu lạc bộ nào phù hợp</h5>
        <p class="text-muted">Vui lòng thử lại với từ khóa khác.</p>
    </div>
@endif