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

        // Featured clubs (top by active members)
        $featuredClubs = Club::with(['field'])
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                },
            ])
            ->where('status', 'active')
            ->orderByDesc('active_members_count')
            ->limit(6)
            ->get();

        // Upcoming events
        $upcomingEvents = Event::with('club')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        // Latest public posts
        $recentPosts = Post::with(['club', 'user'])
            ->where('status', 'published')
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

        $isLoggedIn = session('user_id');
        $viewName = $isLoggedIn ? 'home.index_student' : 'home.index';
        $user = null;
        if ($isLoggedIn) {
            $user = User::with('clubs')->find(session('user_id'));
        }

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
        ]);
    }
}

