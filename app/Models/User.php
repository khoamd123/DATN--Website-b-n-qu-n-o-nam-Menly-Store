<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone'	,
        'address',
        'avatar',
        'role',
        'student_id'
    ];
    protected $attributes = [
        'is_admin' => 0,
        'avatar' => '/images/avatar/avatar.png',
        'role' => 'user'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'role' => 'string',
    ];

    /**
     * Get the clubs owned by the user
     */
    public function ownedClubs()
    {
        return $this->hasMany(Club::class, 'owner_id');
    }

    /**
     * Get the clubs where user is a member
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_members')
            ->withPivot('position', 'status', 'joined_at');
    }

    /**
     * Get user's club memberships
     */
    public function clubMembers()
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Get clubs where user is a leader
     */
    public function ledClubs()
    {
        return $this->hasMany(Club::class, 'leader_id');
    }

    /**
     * Get club join requests made by user
     */
    public function clubJoinRequests()
    {
        return $this->hasMany(ClubJoinRequest::class);
    }

    /**
     * Check if user is leader of a specific club
     */
    public function isLeaderOf($clubId)
    {
        return $this->clubMembers()
            ->where('club_id', $clubId)
            ->where('position', 'leader')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Check if user is officer of a specific club (vice_president or officer)
     */
    public function isOfficerOf($clubId)
    {
        return $this->clubMembers()
            ->where('club_id', $clubId)
            ->whereIn('position', ['vice_president', 'officer'])
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Check if user is member of a specific club
     */
    public function isMemberOf($clubId)
    {
        return $this->clubMembers()
            ->where('club_id', $clubId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Get user's position in a specific club
     */
    public function getPositionInClub($clubId)
    {
        $membership = $this->clubMembers()
            ->where('club_id', $clubId)
            ->where('status', 'active')
            ->first();
            
        return $membership ? $membership->position : null;
    }

    /**
     * Get user's permissions for a specific club
     */
    public function clubPermissions($clubId = null)
    {
        $query = $this->belongsToMany(Permission::class, 'user_permissions_club', 'user_id', 'permission_id')
            ->withPivot('club_id');
            
        if ($clubId) {
            $query->wherePivot('club_id', $clubId);
        }
        
        return $query;
    }

    /**
     * Check if user has a specific permission for a club
     */
    public function hasPermission($permission, $clubId = null)
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return true;
        }

        // Club Manager có quyền quản lý CLB
        if ($this->isClubManager() && $clubId) {
            $club = \App\Models\Club::find($clubId);
            if ($club && ($club->owner_id == $this->id || $club->leader_id == $this->id)) {
                return true;
            }
        }

        // Kiểm tra quyền theo position trong CLB
        if ($clubId) {
            $position = $this->getPositionInClub($clubId);
            
            // Leader có tất cả quyền trong CLB
            if ($position === 'leader') {
                return true;
            }
            
            // Vice President có hầu hết quyền
            if ($position === 'vice_president') {
                $restrictedPermissions = ['quan_ly_clb'];
                if (!in_array($permission, $restrictedPermissions)) {
                    return true;
                }
            }
            
            // Officer có quyền hạn chế
            if ($position === 'officer') {
                $allowedPermissions = ['tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                if (in_array($permission, $allowedPermissions)) {
                    return true;
                }
            }
            
            // Member có quyền cơ bản
            if ($position === 'member') {
                $basicPermissions = ['xem_bao_cao'];
                if (in_array($permission, $basicPermissions)) {
                    return true;
                }
            }
        }

        // Kiểm tra quyền chi tiết từ bảng permissions
        $query = $this->clubPermissions($clubId);
        
        if (is_string($permission)) {
            $query->where('name', $permission);
        } else {
            $query->where('id', $permission);
        }

        return $query->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin' || $this->is_admin;
    }

    /**
     * Check if user is club manager
     */
    public function isClubManager()
    {
        return $this->role === 'club_manager';
    }

    /**
     * Check if user is executive board member
     */
    public function isExecutiveBoard()
    {
        return $this->role === 'executive_board';
    }

    /**
     * Check if user is regular user
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if user can manage club members (Club Manager or Executive Board)
     */
    public function canManageClubMembers()
    {
        return $this->isAdmin() || $this->isClubManager() || $this->isExecutiveBoard();
    }

    /**
     * Check if user can manage club (create, edit, delete club)
     */
    public function canManageClub($clubId = null)
    {
        return $this->hasPermission('manage_club', $clubId);
    }

    /**
     * Check if user can manage members in a club
     */
    public function canManageMembers($clubId = null)
    {
        return $this->hasPermission('manage_members', $clubId);
    }

    /**
     * Check if user can create events
     */
    public function canCreateEvent($clubId = null)
    {
        return $this->hasPermission('create_event', $clubId);
    }

    /**
     * Check if user can post announcements
     */
    public function canPostAnnouncement($clubId = null)
    {
        return $this->hasPermission('post_announcement', $clubId);
    }

    /**
     * Check if user can evaluate members
     */
    public function canEvaluateMember($clubId = null)
    {
        return $this->hasPermission('evaluate_member', $clubId);
    }

    /**
     * Check if user can view reports
     */
    public function canViewReports($clubId = null)
    {
        return $this->hasPermission('view_reports', $clubId);
    }

    /**
     * Get all permissions for a specific club
     */
    public function getClubPermissions($clubId)
    {
        $permissions = [];
        
        if ($this->isAdmin()) {
            $permissions = \App\Models\Permission::pluck('name')->toArray();
        } else {
            // Ưu tiên lấy từ database trước
            $dbPermissions = $this->clubPermissions($clubId)->pluck('name')->toArray();
            
            if (!empty($dbPermissions)) {
                // Nếu có permissions trong database, dùng database
                $permissions = $dbPermissions;
            } else {
                // Nếu không có, dùng default theo position
                $position = $this->getPositionInClub($clubId);
                
                switch ($position) {
                    case 'leader':
                        $permissions = \App\Models\Permission::pluck('name')->toArray();
                        break;
                    case 'vice_president':
                        $permissions = ['quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                        break;
                    case 'officer':
                        $permissions = ['tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                        break;
                    case 'member':
                        $permissions = ['xem_bao_cao'];
                        break;
                    default:
                        $permissions = [];
                }
            }
        }
        
        return $permissions;
    }

    /**
     * Generate student ID from email
     * Format: khoamdph31863@fpt.edu.vn -> ph31863
     */
    public static function generateStudentIdFromEmail($email)
    {
        // Extract username part before @
        $username = substr($email, 0, strpos($email, '@'));
        
        // Check if it's a university email
        if (self::isUniversityEmail($email)) {
            // Parse email format: khoamdph31863@fpt.edu.vn
            // Find where numbers start (after name part)
            
            // Find where numbers start
            $numberStart = preg_match('/\d/', $username, $matches, PREG_OFFSET_CAPTURE);
            
            if ($numberStart && $matches[0][1] > 0) {
                $numberPosition = $matches[0][1];
                
                // Go back 2-3 characters to include letters before numbers
                // For khoamdph31863, we want "ph31863" not just "31863"
                $startPosition = max(0, $numberPosition - 2);
                $studentCode = substr($username, $startPosition);
                
                // If the result is too short, try to get more context
                if (strlen($studentCode) < 4) {
                    $startPosition = max(0, $numberPosition - 3);
                    $studentCode = substr($username, $startPosition);
                }
                
                // Return the student code part
                return strtoupper($studentCode);
            }
            
            // Fallback: use the whole username
            return strtoupper($username);
        }
        
        return null;
    }

    /**
     * Check if email is university email
     */
    public static function isUniversityEmail($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        
        // List of allowed university domains
        $allowedDomains = [
            'fpt.edu.vn',
            'hust.edu.vn', 
            'hust.edu.vn',
            'vnu.edu.vn',
            'uet.vnu.edu.vn',
            'university.edu.vn',
            'student.edu.vn'
        ];
        
        return in_array($domain, $allowedDomains) || 
               strpos($domain, 'edu.vn') !== false;
    }
    
    /**
     * Extract name from university email
     * Format: khoamdph31863@fpt.edu.vn -> Khoa Mạc Đăng
     */
    public static function extractNameFromEmail($email)
    {
        $username = substr($email, 0, strpos($email, '@'));
        
        if (self::isUniversityEmail($email)) {
            // Find where numbers start
            $numberStart = preg_match('/\d/', $username, $matches, PREG_OFFSET_CAPTURE);
            
            if ($numberStart && $matches[0][1] > 0) {
                $numberPosition = $matches[0][1];
                
                // Extract name part (before numbers)
                $namePart = substr($username, 0, $numberPosition);
                
                // Convert to readable name
                // khoamd -> Khoa Mạc Đăng (example mapping)
                return self::convertToReadableName($namePart);
            }
        }
        
        return ucfirst($username);
    }
    
    /**
     * Convert abbreviated name to readable name
     */
    private static function convertToReadableName($abbrev)
    {
        // This is a simple mapping - in real app you might want more sophisticated logic
        $mappings = [
            'khoamd' => 'Khoa Mạc Đăng',
            'nguyen' => 'Nguyễn',
            'tran' => 'Trần',
            'le' => 'Lê',
            'pham' => 'Phạm',
            'hoang' => 'Hoàng',
            'vu' => 'Vũ',
            'dang' => 'Đăng',
            'minh' => 'Minh',
            'van' => 'Văn',
            'thi' => 'Thị',
        ];
        
        // Try to find partial matches
        foreach ($mappings as $key => $value) {
            if (strpos($abbrev, $key) !== false) {
                return $value;
            }
        }
        
        return ucfirst($abbrev);
    }

    /**
     * Get clubs where user is a manager (owner or has manage_club permission)
     */
    public function managedClubs()
    {
        $ownedClubs = $this->ownedClubs;
        $managedClubs = collect();
        
        // Get clubs where user has manage_club permission
        $permissions = $this->clubPermissions()
            ->where('name', 'manage_club')
            ->get();
            
        foreach ($permissions as $permission) {
            $clubId = $permission->pivot->club_id;
            $club = \App\Models\Club::find($clubId);
            if ($club) {
                $managedClubs->push($club);
            }
        }
        
        return $ownedClubs->merge($managedClubs)->unique('id');
    }
}
