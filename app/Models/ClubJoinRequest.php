<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubJoinRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_id',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who made the request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club that the user wants to join
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who reviewed the request
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute()
    {
        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Approve the request
     */
    public function approve($reviewedBy)
    {
        // Trước khi approve, cần xử lý các request đã approved trước đó của cùng user_id và club_id
        // Để tránh vi phạm unique constraint (user_id, club_id, status)
        // Xóa các request đã approved trước đó (vì không còn cần thiết khi có request mới được approve)
        static::where('user_id', $this->user_id)
            ->where('club_id', $this->club_id)
            ->where('status', 'approved')
            ->where('id', '!=', $this->id)
            ->delete();

        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now()
        ]);

        // Kiểm tra xem user đã là thành viên chưa (có thể đã join trước đó và rời đi)
        $existingMember = ClubMember::withTrashed()
            ->where('user_id', $this->user_id)
            ->where('club_id', $this->club_id)
            ->first();

        if ($existingMember) {
            // Nếu đã có membership nhưng bị soft delete (đã rời), khôi phục lại
            if ($existingMember->trashed()) {
                $existingMember->restore();
                $existingMember->update([
                    'status' => 'active',
                    'joined_at' => now()
                ]);
            } else {
                // Nếu đã là thành viên đang hoạt động, không cần tạo mới
                // Chỉ cần đảm bảo status là active
                if (!in_array($existingMember->status, ['active', 'approved'])) {
                    $existingMember->update([
                        'status' => 'active'
                    ]);
                }
            }
        } else {
            // Chưa có membership, tạo mới
            ClubMember::create([
                'user_id' => $this->user_id,
                'club_id' => $this->club_id,
                'position' => 'member',
                'status' => 'active',
                'joined_at' => now()
            ]);
        }

        // Tự động gán quyền "xem_bao_cao" cho thành viên mới
        $viewReportPermission = \App\Models\Permission::where('name', 'xem_bao_cao')->first();
        if ($viewReportPermission) {
            // Kiểm tra xem đã có quyền này chưa
            $existingPermission = \DB::table('user_permissions_club')
                ->where('user_id', $this->user_id)
                ->where('club_id', $this->club_id)
                ->where('permission_id', $viewReportPermission->id)
                ->first();
            
            if (!$existingPermission) {
                \DB::table('user_permissions_club')->insert([
                    'user_id' => $this->user_id,
                    'club_id' => $this->club_id,
                    'permission_id' => $viewReportPermission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reject the request
     */
    public function reject($reviewedBy, $rejectionReason = null)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'rejection_reason' => $rejectionReason
        ]);
    }
}

