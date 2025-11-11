<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use App\Models\Post;
use App\Models\ClubMember;
use App\Models\PostComment;
use App\Models\ClubResource;

class TrashController extends Controller
{
    /**
     * Hiển thị trang quản lý thùng rác
     */
    public function index(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $type = $request->get('type', 'all');
        
        $data = [
            'users' => collect(),
            'clubs' => collect(),
            'posts' => collect(),
            'clubMembers' => collect(),
            'comments' => collect(),
            'clubResources' => collect(),
        ];

        switch ($type) {
            case 'users':
                $data['users'] = User::onlyTrashed()->paginate(20);
                break;
            case 'clubs':
                $data['clubs'] = Club::onlyTrashed()->with('owner')->paginate(20);
                break;
            case 'posts':
                $data['posts'] = Post::onlyTrashed()->with(['user', 'club'])->paginate(20);
                break;
            case 'club-members':
                $data['clubMembers'] = ClubMember::onlyTrashed()->with(['user', 'club'])->paginate(20);
                break;
            case 'comments':
                $data['comments'] = PostComment::onlyTrashed()->with(['user', 'post'])->paginate(20);
                break;
            case 'club-resources':
                $data['clubResources'] = ClubResource::onlyTrashed()->with(['club', 'user'])->paginate(20);
                break;
            default:
                // Hiển thị tất cả
                $data['users'] = User::onlyTrashed()->limit(5)->get();
                $data['clubs'] = Club::onlyTrashed()->with('owner')->limit(5)->get();
                $data['posts'] = Post::onlyTrashed()->with(['user', 'club'])->limit(5)->get();
                $data['clubMembers'] = ClubMember::onlyTrashed()->with(['user', 'club'])->limit(5)->get();
                $data['comments'] = PostComment::onlyTrashed()->with(['user', 'post'])->limit(5)->get();
                $data['clubResources'] = ClubResource::onlyTrashed()->with(['club', 'user'])->limit(5)->get();
                break;
        }

        return view('admin.trash.index', compact('data', 'type'));
    }

    /**
     * Khôi phục một item
     */
    public function restore(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $type = $request->input('type');
        $id = $request->input('id');

        try {
            switch ($type) {
                case 'user':
                    $item = User::onlyTrashed()->findOrFail($id);
                    break;
                case 'club':
                    $item = Club::onlyTrashed()->findOrFail($id);
                    break;
                case 'post':
                    $item = Post::onlyTrashed()->findOrFail($id);
                    break;
                case 'club-member':
                    $item = ClubMember::onlyTrashed()->findOrFail($id);
                    break;
                case 'comment':
                    $item = PostComment::onlyTrashed()->findOrFail($id);
                    break;
                case 'club-resource':
                    $item = ClubResource::onlyTrashed()->findOrFail($id);
                    break;
                default:
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => 'Loại không hợp lệ']);
                    }
                    return back()->with('error', 'Loại không hợp lệ');
            }

            $item->restore();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => "Đã khôi phục {$type} thành công!"
                ]);
            }

            // Redirect về trang chi tiết nếu là post
            if ($type === 'post') {
                return redirect()->route('admin.posts.show', $id)->with('success', 'Đã khôi phục bài viết thành công!');
            }

            return back()->with('success', 'Đã khôi phục thành công!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Lỗi: ' . $e->getMessage()
                ]);
            }
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Xóa vĩnh viễn một item
     */
    public function forceDelete(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $type = $request->input('type');
        $id = $request->input('id');

        try {
            switch ($type) {
                case 'user':
                    $item = User::onlyTrashed()->findOrFail($id);
                    // Xóa tất cả dữ liệu liên quan trước khi xóa user
                    // 1. Permissions
                    \DB::table('user_permissions_club')->where('user_id', $id)->delete();
                    // 2. Club members
                    \DB::table('club_members')->where('user_id', $id)->delete();
                    // 3. Post comments
                    \DB::table('post_comments')->where('user_id', $id)->delete();
                    // 4. Posts (xóa cả attachments và comments trước)
                    $userPosts = \App\Models\Post::where('user_id', $id)->get();
                    foreach ($userPosts as $post) {
                        \DB::table('post_attachments')->where('post_id', $post->id)->delete();
                        \DB::table('post_comments')->where('post_id', $post->id)->delete();
                    }
                    \DB::table('posts')->where('user_id', $id)->delete();
                    // 5. Event registrations
                    \DB::table('event_registrations')->where('user_id', $id)->delete();
                    // 6. Event comments
                    \DB::table('event_comments')->where('user_id', $id)->delete();
                    // 7. Event member evaluations
                    \DB::table('event_member_evaluations')->where('evaluator_id', $id)->orWhere('member_id', $id)->delete();
                    // 8. Event logs
                    \DB::table('event_logs')->where('user_id', $id)->delete();
                    // 9. Department members
                    \DB::table('department_members')->where('user_id', $id)->delete();
                    // 10. Notification reads
                    \DB::table('notification_reads')->where('user_id', $id)->delete();
                    // 11. Club join requests
                    \DB::table('club_join_requests')->where('user_id', $id)->delete();
                    // 12. Cập nhật clubs nếu user là owner hoặc leader
                    \DB::table('clubs')->where('owner_id', $id)->update(['owner_id' => null]);
                    \DB::table('clubs')->where('leader_id', $id)->update(['leader_id' => null]);
                    // 13. Cập nhật events nếu user là creator
                    \DB::table('events')->where('created_by', $id)->update(['created_by' => null]);
                    // 14. Cập nhật notifications nếu user là sender
                    \DB::table('notifications')->where('sender_id', $id)->update(['sender_id' => null]);
                    // 15. Fund transactions (approved_by sẽ set null do onDelete set null)
                    \DB::table('fund_transactions')->where('created_by', $id)->delete();
                    // 16. Fund requests (approved_by và settled_by sẽ set null)
                    \DB::table('fund_requests')->where('created_by', $id)->delete();
                    // 17. Funds
                    \DB::table('funds')->where('created_by', $id)->delete();
                    // 18. Club resources (có onDelete cascade nhưng để chắc chắn)
                    \DB::table('club_resources')->where('user_id', $id)->delete();
                    break;
                case 'club':
                    $item = Club::onlyTrashed()->findOrFail($id);
                    // Xóa tất cả club_members của club
                    \DB::table('club_members')->where('club_id', $id)->delete();
                    // Xóa tất cả permissions của club
                    \DB::table('user_permissions_club')->where('club_id', $id)->delete();
                    break;
                case 'post':
                    $item = Post::onlyTrashed()->findOrFail($id);
                    // Xóa tất cả bình luận và đính kèm trước khi xóa bài viết
                    if (method_exists($item, 'attachments')) {
                        $item->attachments()->forceDelete();
                    }
                    $item->comments()->forceDelete();
                    break;
                case 'club-member':
                    $item = ClubMember::onlyTrashed()->findOrFail($id);
                    break;
                case 'comment':
                    $item = PostComment::onlyTrashed()->findOrFail($id);
                    break;
                case 'club-resource':
                    $item = ClubResource::onlyTrashed()->findOrFail($id);
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Loại không hợp lệ']);
            }

            $item->forceDelete();

            return response()->json([
                'success' => true, 
                'message' => "Đã xóa vĩnh viễn {$type} thành công!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Khôi phục tất cả items của một loại
     */
    public function restoreAll(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $type = $request->input('type');

        try {
            switch ($type) {
                case 'users':
                    User::onlyTrashed()->restore();
                    break;
                case 'clubs':
                    Club::onlyTrashed()->restore();
                    break;
                case 'posts':
                    Post::onlyTrashed()->restore();
                    break;
                case 'club-members':
                    ClubMember::onlyTrashed()->restore();
                    break;
                case 'comments':
                    PostComment::onlyTrashed()->restore();
                    break;
                case 'club-resources':
                    ClubResource::onlyTrashed()->restore();
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Loại không hợp lệ']);
            }

            return response()->json([
                'success' => true, 
                'message' => "Đã khôi phục tất cả {$type} thành công!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xóa vĩnh viễn tất cả items của một loại
     */
    public function forceDeleteAll(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $type = $request->input('type');

        try {
            switch ($type) {
                case 'users':
                    // Xóa tất cả user đã bị xóa mềm
                    $users = User::onlyTrashed()->get();
                    foreach ($users as $user) {
                        // Xóa tất cả dữ liệu liên quan
                        \DB::table('user_permissions_club')->where('user_id', $user->id)->delete();
                        \DB::table('club_members')->where('user_id', $user->id)->delete();
                        \DB::table('post_comments')->where('user_id', $user->id)->delete();
                        $userPosts = \App\Models\Post::where('user_id', $user->id)->get();
                        foreach ($userPosts as $post) {
                            \DB::table('post_attachments')->where('post_id', $post->id)->delete();
                            \DB::table('post_comments')->where('post_id', $post->id)->delete();
                        }
                        \DB::table('posts')->where('user_id', $user->id)->delete();
                        \DB::table('event_registrations')->where('user_id', $user->id)->delete();
                        \DB::table('event_comments')->where('user_id', $user->id)->delete();
                        \DB::table('event_member_evaluations')->where('evaluator_id', $user->id)->orWhere('member_id', $user->id)->delete();
                        \DB::table('event_logs')->where('user_id', $user->id)->delete();
                        \DB::table('department_members')->where('user_id', $user->id)->delete();
                        \DB::table('notification_reads')->where('user_id', $user->id)->delete();
                        \DB::table('club_join_requests')->where('user_id', $user->id)->delete();
                        \DB::table('clubs')->where('owner_id', $user->id)->update(['owner_id' => null]);
                        \DB::table('clubs')->where('leader_id', $user->id)->update(['leader_id' => null]);
                        \DB::table('events')->where('created_by', $user->id)->update(['created_by' => null]);
                        \DB::table('notifications')->where('sender_id', $user->id)->update(['sender_id' => null]);
                        \DB::table('fund_transactions')->where('created_by', $user->id)->delete();
                        \DB::table('fund_requests')->where('created_by', $user->id)->delete();
                        \DB::table('funds')->where('created_by', $user->id)->delete();
                        \DB::table('club_resources')->where('user_id', $user->id)->delete();
                        $user->forceDelete();
                    }
                    break;
                case 'clubs':
                    // Xóa tất cả club đã bị xóa mềm
                    $clubs = Club::onlyTrashed()->get();
                    foreach ($clubs as $club) {
                        // Xóa club_members và permissions của club này
                        \DB::table('club_members')->where('club_id', $club->id)->delete();
                        \DB::table('user_permissions_club')->where('club_id', $club->id)->delete();
                        $club->forceDelete();
                    }
                    break;
                case 'posts':
                    // Xóa tất cả attachments và bình luận trước, sau đó xóa bài viết
                    $posts = Post::onlyTrashed()->get();
                    foreach ($posts as $post) {
                        if (method_exists($post, 'attachments')) {
                            $post->attachments()->forceDelete();
                        }
                        $post->comments()->forceDelete();
                        // Xóa bài viết
                        $post->forceDelete();
                    }
                    break;
                case 'club-members':
                    ClubMember::onlyTrashed()->forceDelete();
                    break;
                case 'comments':
                    PostComment::onlyTrashed()->forceDelete();
                    break;
                case 'club-resources':
                    ClubResource::onlyTrashed()->forceDelete();
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Loại không hợp lệ']);
            }

            return response()->json([
                'success' => true, 
                'message' => "Đã xóa vĩnh viễn tất cả {$type} thành công!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }
}
