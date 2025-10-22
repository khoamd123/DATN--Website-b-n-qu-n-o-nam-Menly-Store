<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\EventComment;
use App\Models\EventRegistration;
use App\Models\Notification;

class UserAnalyticsService
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Lấy thống kê sự kiện
     */
    public function getEventStats()
    {
        try {
            return [
                'created' => Event::where('created_by', $this->user->id)->count(),
                'participated' => EventRegistration::where('user_id', $this->user->id)->count(),
                'upcoming' => EventRegistration::where('user_id', $this->user->id)
                    ->whereHas('event', function($query) {
                        $query->where('start_time', '>', now());
                    })->count()
            ];
        } catch (\Exception $e) {
            return [
                'created' => 0,
                'participated' => 0,
                'upcoming' => 0
            ];
        }
    }

    /**
     * Lấy thời gian online gần đây
     */
    public function getLastOnline()
    {
        return $this->user->last_online ?? now()->subDays(30); // Nếu null thì trả về 30 ngày trước
    }

    /**
     * Lấy tần suất hoạt động
     */
    public function getActivityFrequency()
    {
        try {
            $baseQuery = function($model, $days) {
                return $model::where('user_id', $this->user->id)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->count();
            };

            return [
                'daily' => $baseQuery(Post::class, 1) + $baseQuery(PostComment::class, 1) + $baseQuery(EventComment::class, 1),
                'weekly' => $baseQuery(Post::class, 7) + $baseQuery(PostComment::class, 7) + $baseQuery(EventComment::class, 7),
                'monthly' => $baseQuery(Post::class, 30) + $baseQuery(PostComment::class, 30) + $baseQuery(EventComment::class, 30),
            ];
        } catch (\Exception $e) {
            return [
                'daily' => 0,
                'weekly' => 0,
                'monthly' => 0
            ];
        }
    }

    /**
     * Lấy thông báo chưa đọc
     */
    public function getUnreadNotifications()
    {
        try {
            return Notification::whereHas('targets', function($query) {
                $query->where('user_id', $this->user->id);
            })->whereDoesntHave('reads', function($query) {
                $query->where('user_id', $this->user->id);
            })->count();
        } catch (\Exception $e) {
            // Nếu có lỗi với notification system, return 0
            return 0;
        }
    }

    /**
     * Lấy báo cáo vi phạm
     */
    public function getReports()
    {
        // Tạm thời return 0, sẽ implement sau
        return [
            'total' => 0,
            'resolved' => 0,
            'pending' => 0
        ];
    }

    /**
     * Lấy tất cả analytics
     */
    public function getAllAnalytics()
    {
        try {
            return [
                'events' => $this->getEventStats(),
                'last_online' => $this->getLastOnline(),
                'activity' => $this->getActivityFrequency(),
                'unread_notifications' => $this->getUnreadNotifications(),
                'reports' => $this->getReports()
            ];
        } catch (\Exception $e) {
            // Return default values if any error occurs
            return [
                'events' => ['created' => 0, 'participated' => 0, 'upcoming' => 0],
                'last_online' => $this->user->updated_at,
                'activity' => ['daily' => 0, 'weekly' => 0, 'monthly' => 0],
                'unread_notifications' => 0,
                'reports' => ['total' => 0, 'resolved' => 0, 'pending' => 0]
            ];
        }
    }
}
