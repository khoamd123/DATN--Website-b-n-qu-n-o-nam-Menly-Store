@if($otherClubs->count() > 0)
    <div class="row">
        @foreach($otherClubs as $club)
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="club-logo me-3">
                            {{ substr($club->name, 0, 2) }}
                        </div>
                        <div>
                            <h5 class="card-title mb-1">{{ $club->name }}</h5>
                            <small class="text-muted">
                                <i class="fas fa-user-friends"></i> {{ $club->members_count }} thành viên
                            </small>
                        </div>
                    </div>
                    <p class="card-text">{{ Str::limit($club->description, 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">Đang hoạt động</span>
                        <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-primary btn-sm">Xem & Tham gia</a>
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