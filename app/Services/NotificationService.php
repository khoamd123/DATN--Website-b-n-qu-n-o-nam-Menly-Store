<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationTarget;
use App\Models\NotificationRead;
use App\Models\User;
use App\Models\ClubMember;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to specific targets
     * 
     * @param int $senderId ID của người gửi
     * @param string $type Loại thông báo (fund_transaction, fund_request, club, etc)
     * @param string $title Tiêu đề thông báo
     * @param string $message Nội dung thông báo
     * @param array $targets Mảng các target [['type' => 'user', 'id' => 1], ['type' => 'club', 'id' => 2]]
     * @param int|null $relatedId ID của đối tượng liên quan (optional)
     * @param string|null $relatedType Class name của đối tượng liên quan (optional)
     * @return Notification|null
     */
    public static function send(
        int $senderId,
        string $type,
        string $title,
        string $message,
        array $targets,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        try {
            $notification = Notification::create([
                'sender_id' => $senderId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'related_id' => $relatedId,
                'related_type' => $relatedType,
            ]);
            
            foreach ($targets as $target) {
                NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => $target['type'],
                    'target_id' => $target['id'] ?? null,
                ]);
                
                // Nếu target là user cụ thể, tạo notification_read record
                if ($target['type'] === 'user' && isset($target['id'])) {
                    NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id' => $target['id'],
                        'is_read' => false,
                    ]);
                }
            }
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send notification to all admins
     * 
     * @param int $senderId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int|null $relatedId
     * @param string|null $relatedType
     * @return Notification|null
     */
    public static function sendToAdmins(
        int $senderId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        $admins = User::where('is_admin', true)->get();
        
        if ($admins->isEmpty()) {
            return null;
        }
        
        $targets = $admins->map(fn($admin) => ['type' => 'user', 'id' => $admin->id])->toArray();
        
        return self::send($senderId, $type, $title, $message, $targets, $relatedId, $relatedType);
    }
    
    /**
     * Send notification to club leaders (leader, vice_president, treasurer)
     * 
     * @param int $clubId
     * @param int $senderId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int|null $relatedId
     * @param string|null $relatedType
     * @return Notification|null
     */
    public static function sendToClubLeaders(
        int $clubId,
        int $senderId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        $leaders = ClubMember::where('club_id', $clubId)
            ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
            ->where('status', 'approved')
            ->with('user')
            ->get();
        
        if ($leaders->isEmpty()) {
            return null;
        }
        
        $targets = $leaders->filter(fn($member) => $member->user !== null)
            ->map(fn($member) => ['type' => 'user', 'id' => $member->user_id])
            ->toArray();
        
        return self::send($senderId, $type, $title, $message, $targets, $relatedId, $relatedType);
    }
    
    /**
     * Send notification to specific user
     * 
     * @param int $userId
     * @param int $senderId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int|null $relatedId
     * @param string|null $relatedType
     * @return Notification|null
     */
    public static function sendToUser(
        int $userId,
        int $senderId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        $targets = [['type' => 'user', 'id' => $userId]];
        
        return self::send($senderId, $type, $title, $message, $targets, $relatedId, $relatedType);
    }
    
    /**
     * Send notification to all members of a club
     * 
     * @param int $clubId
     * @param int $senderId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int|null $relatedId
     * @param string|null $relatedType
     * @return Notification|null
     */
    public static function sendToClub(
        int $clubId,
        int $senderId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        $targets = [['type' => 'club', 'id' => $clubId]];
        
        return self::send($senderId, $type, $title, $message, $targets, $relatedId, $relatedType);
    }
    
    /**
     * Send notification to everyone in the system
     * 
     * @param int $senderId
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int|null $relatedId
     * @param string|null $relatedType
     * @return Notification|null
     */
    public static function sendToAll(
        int $senderId,
        string $type,
        string $title,
        string $message,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): ?Notification {
        $targets = [['type' => 'all']];
        
        return self::send($senderId, $type, $title, $message, $targets, $relatedId, $relatedType);
    }
}

