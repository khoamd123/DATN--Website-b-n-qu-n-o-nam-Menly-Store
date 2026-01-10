<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\Event;
use App\Models\Field;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the public homepage with club highlights, events, posts, and statistics.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $fieldId = $request->input('field');
        $sort = $request->input('sort', 'popular');

        // Prepare statistics for hero section
        $stats = [
            'clubs' => Club::where('status', 'active')->count(),
            'members' => ClubMember::whereIn('status', ['approved', 'active'])->count(),
            'events' => Event::where('start_time', '>=', now())->count(),
            'posts' => Post::where('status', 'published')->count(),
        ];

        // Kiểm tra user đăng nhập trước để filter events
        $isLoggedIn = session('user_id');
        $user = null;
        $latestAnnouncement = null;
        
        if ($isLoggedIn) {
            $user = User::with('clubs')->find(session('user_id'));
            
            // Lấy thông báo mới nhất từ các CLB mà user tham gia
            if ($user && $user->clubs->count() > 0) {
                $userClubIds = $user->clubs->pluck('id')->toArray();
                $latestAnnouncement = Post::with(['club', 'user'])
                    ->where('type', 'announcement')
                    ->where('status', '!=', 'deleted')
                    ->whereIn('club_id', $userClubIds)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        // Featured clubs (top by active members) - chỉ lấy 4 clubs
        $featuredClubs = Club::with(['field'])
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                },
            ])
            ->where('status', 'active')
            ->orderByDesc('active_members_count')
            ->limit(4)
            ->get();

        // Upcoming events - filter theo visibility
        $upcomingEventsQuery = Event::with('club')
            ->where('start_time', '>=', now())
            ->where('status', 'approved'); // Chỉ hiển thị events đã được duyệt
        
        // Nếu user đã đăng nhập, filter theo visibility và membership
        if ($isLoggedIn && $user) {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $upcomingEventsQuery->where(function($query) use ($userClubIds) {
                // Công khai: tất cả mọi người có thể xem
                $query->where('visibility', 'public')
                    ->orWhere(function($q) use ($userClubIds) {
                        // Chỉ nội bộ CLB: chỉ thành viên của CLB đó mới xem được
                        $q->where('visibility', 'internal')
                          ->whereIn('club_id', $userClubIds);
                    });
            });
        } else {
            // Nếu chưa đăng nhập, chỉ hiển thị events công khai
            $upcomingEventsQuery->where('visibility', 'public');
        }
        
        $upcomingEvents = $upcomingEventsQuery->orderBy('start_time')->limit(6)->get();

        // Latest public posts (chỉ lấy bài viết, không lấy thông báo)
        $recentPosts = Post::with(['club', 'user', 'attachments'])
            ->where('status', 'published')
            ->where('type', 'post') // Chỉ lấy bài viết, không lấy thông báo
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $fields = Field::orderBy('name')->get();

        // Build main clubs query with filters & sorting
        $clubsQuery = Club::with(['field'])
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                },
            ])
            ->where('status', 'active');

        if ($search !== '') {
            $clubsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($fieldId) {
            $clubsQuery->where('field_id', $fieldId);
        }

        switch ($sort) {
            case 'newest':
                $clubsQuery->orderByDesc('created_at');
                break;
            case 'name':
                $clubsQuery->orderBy('name');
                break;
            case 'popular':
            default:
                $clubsQuery->orderByDesc('active_members_count')
                    ->orderBy('name');
                break;
        }

        $clubs = $clubsQuery->paginate(8)->withQueryString();

        $viewName = $isLoggedIn ? 'home.index_student' : 'home.index';

        return view($viewName, [
            'stats' => $stats,
            'featuredClubs' => $featuredClubs,
            'upcomingEvents' => $upcomingEvents,
            'recentPosts' => $recentPosts,
            'fields' => $fields,
            'clubs' => $clubs,
            'search' => $search,
            'fieldId' => $fieldId,
            'sort' => $sort,
            'user' => $user,
            'latestAnnouncement' => $latestAnnouncement,
        ]);
    }
}

