<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'field_id',
        'leader_id',
        'owner_id',
        'max_members',
        'status',
        'rejection_reason'
    ];

    /**
     * Get the field that owns the club
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the leader of the club
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the owner of the club
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the members of the club
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'club_members');
    }

    /**
     * Get the events for the club
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the posts for the club
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }


    /**
     * Get active members of the club
     */
    public function activeMembers()
    {
        return $this->belongsToMany(User::class, 'club_members')
                    ->wherePivot('status', 'active');
    }

    /**
     * Get all club members
     */
    public function clubMembers()
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Get the join requests for the club.
     */
    public function joinRequests()
    {
        return $this->hasMany(ClubJoinRequest::class);
    }

    /**
     * Tự động set leader nếu CLB chỉ có 1 thành viên
     * Nếu CLB chỉ còn 1 thành viên (approved/active), tự động set thành viên đó làm leader
     */
    public function ensureSingleMemberIsLeader()
    {
        // Lấy danh sách user_id duy nhất có status approved/active
        $approvedUserIds = ClubMember::where('club_id', $this->id)
            ->whereIn('status', ['approved', 'active'])
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        // Nếu chỉ có 1 thành viên duy nhất
        if (count($approvedUserIds) === 1) {
            $userId = $approvedUserIds[0];
            
            // Lấy record mới nhất của user này
            $latestMember = ClubMember::where('club_id', $this->id)
                ->where('user_id', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->orderBy('id', 'desc')
                ->first();
            
            if ($latestMember) {
                // Xóa các records cũ hơn của user này (chỉ giữ lại record mới nhất)
                ClubMember::where('club_id', $this->id)
                    ->where('user_id', $userId)
                    ->where('id', '!=', $latestMember->id)
                    ->whereIn('status', ['approved', 'active'])
                    ->forceDelete();
                
                // Update position thành leader nếu chưa phải
                if ($latestMember->position !== 'leader') {
                    $oldPosition = $latestMember->position;
                    $latestMember->update([
                        'position' => 'leader',
                        'status' => 'approved',
                    ]);
                    
                    \Log::info("Auto-set leader for single member - updated position", [
                        'club_id' => $this->id,
                        'user_id' => $userId,
                        'member_id' => $latestMember->id,
                        'old_position' => $oldPosition,
                        'new_position' => 'leader'
                    ]);
                }
                
                // Update leader_id trong bảng clubs
                if ($this->leader_id !== $userId) {
                    $this->update(['leader_id' => $userId]);
                }
                
                // Cấp tất cả quyền cho leader nếu chưa có
                $allPermissions = \App\Models\Permission::all();
                foreach ($allPermissions as $permission) {
                    $existingPermission = \DB::table('user_permissions_club')
                        ->where('user_id', $userId)
                        ->where('club_id', $this->id)
                        ->where('permission_id', $permission->id)
                        ->first();
                    
                    if (!$existingPermission) {
                        \DB::table('user_permissions_club')->insert([
                            'user_id' => $userId,
                            'club_id' => $this->id,
                            'permission_id' => $permission->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
