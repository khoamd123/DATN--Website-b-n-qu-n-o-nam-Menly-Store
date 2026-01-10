<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Test - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { margin-bottom: 20px; }
        .badge { font-size: 0.8em; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">📊 Data Test Dashboard</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">👥 Users</h5>
                        <h3 class="text-primary">{{ $usersCount }}</h3>
                        <a href="/admin/users" class="btn btn-sm btn-outline-primary">Xem danh sách</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">🏛️ Clubs</h5>
                        <h3 class="text-success">{{ $clubsCount }}</h3>
                        <a href="/admin/clubs" class="btn btn-sm btn-outline-success">Xem danh sách</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">📰 Posts</h5>
                        <h3 class="text-info">{{ $postsCount }}</h3>
                        <a href="/admin/posts" class="btn btn-sm btn-outline-info">Xem danh sách</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">📅 Events</h5>
                        <h3 class="text-warning">{{ $eventsCount }}</h3>
                        <a href="/admin/events" class="btn btn-sm btn-outline-warning">Xem danh sách</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📝 Recent Posts</h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentPosts as $post)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $post->title }}</strong>
                                    <br><small class="text-muted">{{ $post->club->name ?? 'No Club' }}</small>
                                </div>
                                <span class="badge bg-{{ $post->type === 'announcement' ? 'warning' : 'primary' }}">
                                    {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted">Không có bài viết nào</p>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>🏛️ Active Clubs</h5>
                    </div>
                    <div class="card-body">
                        @forelse($activeClubs as $club)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $club->name }}</strong>
                                    <br><small class="text-muted">{{ $club->owner->name ?? 'No Owner' }}</small>
                                </div>
                                <span class="badge bg-success">{{ $club->status }}</span>
                            </div>
                        @empty
                            <p class="text-muted">Không có câu lạc bộ nào</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>💬 Recent Comments</h5>
                    </div>
                    <div class="card-body">
                        @forelse($recentComments as $comment)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $comment->user->name ?? 'Unknown User' }}</strong>
                                    <br><small>{{ $comment->content }}</small>
                                </div>
                                <span class="badge bg-secondary">{{ $comment->created_at ? $comment->created_at->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        @empty
                            <p class="text-muted">Không có bình luận nào</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="/admin/test-links" class="btn btn-primary">🔗 Test Center</a>
            <a href="/admin" class="btn btn-secondary">🏠 Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
