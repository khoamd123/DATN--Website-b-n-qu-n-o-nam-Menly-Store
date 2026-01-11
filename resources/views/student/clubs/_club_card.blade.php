@php
    $logoUrl = null;
    $hasLogo = false;
    if ($club->logo) {
        $logoPath = trim($club->logo);
        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            $logoUrl = $logoPath;
            $hasLogo = true;
        } else {
            $logoPath = ltrim($logoPath, '/');
            $fullPath = public_path($logoPath);
            if (file_exists($fullPath) && is_file($fullPath)) {
                $logoUrl = asset($logoPath);
                $hasLogo = true;
            }
        }
    }
    
    $membersCount = $club->active_members_count ?? $club->members_count ?? $club->members->count() ?? 0;
@endphp
<div class="col-md-6 mb-4">
    <div class="card h-100 border-0 shadow-sm club-card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="club-logo me-3">
                    @if($hasLogo && $logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $club->name }}" class="club-logo-img" 
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="club-logo-fallback" style="display: none;">{{ mb_substr($club->name, 0, 2, 'UTF-8') }}</span>
                    @else
                        <span class="club-logo-fallback">{{ mb_substr($club->name, 0, 2, 'UTF-8') }}</span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <h5 class="card-title mb-1 fw-bold">{{ $club->name }}</h5>
                    <small class="text-muted d-flex align-items-center">
                        <i class="fas fa-user-friends me-1"></i> {{ $membersCount }} thành viên
                    </small>
                </div>
            </div>
            <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8')), 100) }}</p>

            <div class="d-flex justify-content-between align-items-center">
                @if(isset($badge))
                    {!! $badge !!}
                @else
                    <span class="badge bg-success rounded-pill">
                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Đang hoạt động
                    </span>
                @endif
                @if(isset($button))
                    {!! $button !!}
                @else
                    <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

