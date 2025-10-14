<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use App\Models\Post;
use App\Models\ClubMember;
use App\Models\PostComment;

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
            default:
                // Hiển thị tất cả
                $data['users'] = User::onlyTrashed()->limit(5)->get();
                $data['clubs'] = Club::onlyTrashed()->with('owner')->limit(5)->get();
                $data['posts'] = Post::onlyTrashed()->with(['user', 'club'])->limit(5)->get();
                $data['clubMembers'] = ClubMember::onlyTrashed()->with(['user', 'club'])->limit(5)->get();
                $data['comments'] = PostComment::onlyTrashed()->with(['user', 'post'])->limit(5)->get();
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
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
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
                default:
                    return response()->json(['success' => false, 'message' => 'Loại không hợp lệ']);
            }

            $item->restore();

            return response()->json([
                'success' => true, 
                'message' => "Đã khôi phục {$type} thành công!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
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
                    User::onlyTrashed()->forceDelete();
                    break;
                case 'clubs':
                    Club::onlyTrashed()->forceDelete();
                    break;
                case 'posts':
                    Post::onlyTrashed()->forceDelete();
                    break;
                case 'club-members':
                    ClubMember::onlyTrashed()->forceDelete();
                    break;
                case 'comments':
                    PostComment::onlyTrashed()->forceDelete();
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
