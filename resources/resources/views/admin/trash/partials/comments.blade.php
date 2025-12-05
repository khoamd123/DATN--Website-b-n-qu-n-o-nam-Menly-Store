@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bình luận</th>
                    <th>Người viết</th>
                    <th>Bài viết</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>
                            <div>
                                <p class="mb-1">{{ substr($comment->content, 0, 100) }}{{ strlen($comment->content) > 100 ? '...' : '' }}</p>
                                <small class="text-muted">
                                    @if($comment->parent_id)
                                        <i class="fas fa-reply"></i> Trả lời bình luận #{{ $comment->parent_id }}
                                    @else
                                        <i class="fas fa-comment"></i> Bình luận gốc
                                    @endif
                                </small>
                            </div>
                        </td>
                        <td>
                            @if($comment->user)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-fixed me-2">
                                        {{ substr($comment->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <br><small class="text-muted">{{ $comment->user->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($comment->post)
                                <div>
                                    <strong>{{ $comment->post->title }}</strong>
                                    <br><small class="text-muted">{{ $comment->post->slug }}</small>
                                </div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($comment->deleted_at)
                                {{ $comment->deleted_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success text-white" 
                                        onclick="restore('comment', {{ $comment->id }})" 
                                        title="Khôi phục"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-danger text-white" 
                                        onclick="forceDelete('comment', {{ $comment->id }})" 
                                        title="Xóa vĩnh viễn"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                    <i class="fas fa-trash"></i>
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
        <i class="fas fa-comments fa-3x mb-3"></i>
        <p>Không có bình luận nào trong thùng rác</p>
    </div>
@endif
