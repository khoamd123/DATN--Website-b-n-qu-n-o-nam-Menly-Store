@if($otherClubs->count() > 0)
    <div class="row">
        @foreach($otherClubs as $club)
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm club-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="club-logo me-3">
                            {{ substr($club->name, 0, 2) }}
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1 fw-bold">{{ $club->name }}</h5>
                            <small class="text-muted d-flex align-items-center">
                                <i class="fas fa-user-friends me-1"></i> {{ $club->members_count }} thành viên
                            </small>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags($club->description ?? ''), 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Đang hoạt động
                        </span>
                        <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-primary btn-sm rounded-pill">
                            <i class="fas fa-eye me-1"></i> Xem & Tham gia
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4" id="pagination-links">
        {{ $otherClubs->withPath(route('student.clubs.ajax_search'))->appends(request()->query())->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Không tìm thấy câu lạc bộ nào phù hợp</h5>
        <p class="text-muted">Vui lòng thử lại với từ khóa khác.</p>
    </div>
@endif