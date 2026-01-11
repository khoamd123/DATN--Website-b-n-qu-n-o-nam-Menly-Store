@php
    $statusColors = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'approved' => 'success',
        'ongoing' => 'info',
        'completed' => 'primary',
        'cancelled' => 'danger'
    ];
    $statusLabels = [
        'draft' => 'Bản nháp',
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'ongoing' => 'Đang diễn ra',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    
    $hasImages = $event->images && $event->images->count() > 0;
    $hasOldImage = !empty($event->image);
@endphp

<div class="col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100">
        @if($hasImages || $hasOldImage)
            <div style="height: 200px; overflow: hidden; background: #f8f9fa;">
                @if($hasImages)
                    <img src="{{ $event->images->first()->image_url }}" 
                         alt="{{ $event->title }}" 
                         class="w-100 h-100" 
                         style="object-fit: cover;">
                @elseif($hasOldImage)
                    <img src="{{ asset('storage/' . $event->image) }}" 
                         alt="{{ $event->title }}" 
                         class="w-100 h-100" 
                         style="object-fit: cover;">
                @endif
            </div>
        @endif
        
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0">
                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                        {{ $event->title }}
                    </a>
                </h5>
                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                </span>
            </div>
            
            <p class="card-text text-muted small mb-2">
                <i class="fas fa-users me-1"></i>{{ $event->club->name ?? 'Chưa xác định' }}
            </p>
            
            <p class="card-text small mb-3">
                {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
            </p>
            
            <div class="mb-3">
                <div class="small text-muted mb-1">
                    <i class="far fa-calendar me-1"></i>
                    <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                </div>
                <div class="small text-muted mb-1">
                    <i class="far fa-calendar-check me-1"></i>
                    <strong>Kết thúc:</strong> {{ $event->end_time->format('d/m/Y H:i') }}
                </div>
                @if($event->location)
                    <div class="small text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $event->location }}
                    </div>
                @endif
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                </a>
                @if(in_array($event->status, ['pending', 'approved', 'draft']))
                    <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                @endif
                @if($event->status === 'cancelled' && $event->end_time && $event->end_time->isFuture())
                    <form method="POST" action="{{ route('student.events.restore', $event->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" 
                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục sự kiện này?')">
                            <i class="fas fa-undo me-1"></i>Khôi phục
                        </button>
                    </form>
                @endif
                @php
                    $canDelete = in_array($event->status, ['pending', 'draft', 'cancelled']) 
                        && $event->start_time 
                        && $event->start_time->isFuture();
                @endphp
                @if($canDelete)
                    <form method="POST" action="{{ route('student.events.delete', $event->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" 
                                onclick="return confirm('Bạn có chắc chắn muốn xóa sự kiện này? Hành động này không thể hoàn tác!')">
                            <i class="fas fa-trash me-1"></i>Xóa
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>





