@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>CLB</th>
                    <th>Tác giả</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $post)
                    <tr>
                        <td>{{ $post->id }}</td>
                        <td>
                            <div>
                                <strong>{{ $post->title }}</strong>
                                @if($post->content)
                                    @php
                                        $cleanContent = strip_tags($post->content);
                                        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');
                                        $cleanContent = trim($cleanContent);
                                    @endphp
                                    <br><small class="text-muted">{{ Str::limit($cleanContent, 50) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($post->club)
                                <span class="badge bg-info">{{ $post->club->name }}</span>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($post->user)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-fixed me-2">
                                        {{ substr($post->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $post->user->name }}</strong>
                                        <br><small class="text-muted">{{ $post->user->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($post->deleted_at)
                                {{ $post->deleted_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td style="min-width: 120px; width: 120px;">
                            <div class="d-flex flex-column gap-1">
                                <button class="btn btn-sm btn-success w-100 text-white" 
                                        onclick="restore('post', {{ $post->id }})" 
                                        title="Khôi phục">
                                    <i class="fas fa-undo"></i> Khôi phục
                                </button>
                                <button class="btn btn-sm btn-danger w-100 text-white" 
                                        onclick="forceDelete('post', {{ $post->id }})" 
                                        title="Xóa vĩnh viễn">
                                    <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted py-4">
        <i class="fas fa-newspaper fa-3x mb-3"></i>
        <p>Không có bài viết nào trong thùng rác</p>
    </div>
@endif
