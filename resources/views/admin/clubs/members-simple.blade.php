<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thành viên CLB - {{ $club->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Thành viên CLB: {{ $club->name }}</h1>
            <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">Quay lại</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ $club->name }}</h5>
                <p>{{ $club->description }}</p>
                <p><strong>Tổng thành viên:</strong> {{ $members->count() }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Danh sách thành viên</h5>
            </div>
            <div class="card-body">
                @forelse($members as $member)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                        <div>
                            <strong>{{ $member->user->name ?? 'Không xác định' }}</strong>
                            <br><small class="text-muted">{{ $member->user->student_id ?? 'N/A' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $member->position === 'leader' ? 'danger' : ($member->position === 'officer' ? 'info' : 'secondary') }}">
                                {{ $member->position === 'leader' ? 'Trưởng CLB' : ($member->position === 'officer' ? 'Cán sự' : 'Thành viên') }}
                            </span>
                            <form action="{{ route('admin.clubs.members.remove', ['club' => $club->id, 'member' => $member->id]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên {{ $member->user->name ?? 'này' }} khỏi CLB này?')"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">CLB này chưa có thành viên nào</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
