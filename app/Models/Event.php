<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'club_id',
        'created_by',
        'title',
        'slug',
        'description',
        'image',
        'start_time',
        'end_time',
        'mode',
        'location',
        'max_participants',
        'status',
        'visibility',
        'cancellation_reason',
        'cancelled_at',
        'registration_deadline',
        'main_organizer',
        'organizing_team',
        'co_organizers',
        'contact_info',
        'proposal_file',
        'poster_file',
        'permit_file',
        'guests',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'contact_info' => 'array', // Lưu dạng JSON: {"phone": "...", "email": "..."}
        'guests' => 'array', // Lưu dạng JSON: {"types": ["lecturer", "student", "sponsor", "other"], "other_info": "..."}
    ];

    /**
     * Get the club that owns the event
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the event
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the images for the event
     */
    public function images()
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order');
    }

    /**
     * Get the main image for the event (first image or the old image field)
     */
    public function getMainImageAttribute()
    {
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_url;
        }
        
        // Fallback to old image field
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return null;
    }

    /**
     * Tự động cập nhật status dựa trên thời gian
     * Chỉ cập nhật nếu status là approved hoặc ongoing (không cập nhật nếu cancelled, completed, pending)
     */
    public function updateStatusBasedOnTime()
    {
        // Chỉ cập nhật nếu status là approved hoặc ongoing
        if (!in_array($this->status, ['approved', 'ongoing'])) {
            return false;
        }

        $now = now();
        $updated = false;

        // Nếu sự kiện đã bắt đầu nhưng chưa kết thúc -> ongoing
        if ($this->start_time && $this->start_time->isPast() 
            && $this->end_time && $this->end_time->isFuture() 
            && $this->status !== 'ongoing') {
            $this->status = 'ongoing';
            $updated = true;
        }
        
        // Nếu sự kiện đã kết thúc -> completed
        if ($this->end_time && $this->end_time->isPast() 
            && $this->status !== 'completed' 
            && $this->status !== 'cancelled') {
            $this->status = 'completed';
            $updated = true;
        }

        if ($updated) {
            $this->save();
        }

        return $updated;
    }

    /**
     * Boot method để tự động cập nhật status khi load model
     * Chỉ cập nhật tự động khi không phải đang edit (tránh conflict khi admin chỉnh sửa)
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động cập nhật status khi load model
        // Bỏ qua nếu đang trong request update hoặc edit (để admin có thể tự do thay đổi status)
        static::retrieved(function ($event) {
            $request = request();
            if (!$request) {
                return;
            }
            
            // Bỏ qua tự động cập nhật nếu:
            // 1. Đang trong request PUT/PATCH (update)
            // 2. Đang trong route edit (GET request đến edit form)
            // 3. Đang trong route update (POST/PUT/PATCH request đến update)
            $routeName = $request->route() ? $request->route()->getName() : null;
            $isEditRoute = $routeName && str_contains($routeName, 'events.edit');
            $isUpdateRoute = $routeName && str_contains($routeName, 'events.update');
            $isUpdateRequest = in_array($request->method(), ['PUT', 'PATCH', 'POST']);
            
            // Không tự động cập nhật nếu đang edit/update hoặc nếu status là completed/cancelled
            // (vì completed/cancelled không nằm trong logic tự động cập nhật)
            if (!$isUpdateRequest && !$isEditRoute && !$isUpdateRoute) {
                // Chỉ tự động cập nhật nếu status là approved hoặc ongoing
                // (updateStatusBasedOnTime() đã có check này, nhưng check thêm ở đây để chắc chắn)
                if (in_array($event->status, ['approved', 'ongoing'])) {
                    $event->updateStatusBasedOnTime();
                }
            }
        });
    }
}
