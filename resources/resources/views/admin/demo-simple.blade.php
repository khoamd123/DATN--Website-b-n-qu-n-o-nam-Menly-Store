<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Đơn Giản - UniClubs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Demo Tự Động Thay Đổi Vai Trò</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Users ({{ count($users) }})</h3>
                @if(isset($users) && count($users) > 0)
                    @foreach($users as $user)
                        @if(is_object($user) && isset($user->id))
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5>{{ $user->name }}</h5>
                                    <p>ID: {{ $user->id }}</p>
                                    <p>Position: {{ $user->getPositionInClub(1) }}</p>
                                    <p>Permissions: {{ implode(', ', $user->getClubPermissions(1)) }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p>Không có users</p>
                @endif
            </div>
            
            <div class="col-md-6">
                <h3>Permissions ({{ count($permissions) }})</h3>
                @if(isset($permissions) && count($permissions) > 0)
                    @foreach($permissions as $permission)
                        @if(is_object($permission) && isset($permission->id))
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5>{{ $permission->name }}</h5>
                                    <p>ID: {{ $permission->id }}</p>
                                    <p>Description: {{ $permission->description }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p>Không có permissions</p>
                @endif
            </div>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('admin.permissions.detailed') }}" class="btn btn-primary">Quay lại Phân Quyền Chi Tiết</a>
        </div>
    </div>
</body>
</html>
