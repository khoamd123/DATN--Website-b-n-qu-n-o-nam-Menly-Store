<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Tên khoản mục: "Ăn uống", "Địa điểm"...
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable(); // Icon FontAwesome
            $table->string('color', 20)->default('#007bff'); // Màu hiển thị
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'display_order']);
        });
        
        // Thêm dữ liệu mẫu
        DB::table('expense_categories')->insert([
            [
                'name' => 'Địa điểm & Cơ sở vật chất',
                'description' => 'Thuê địa điểm, hội trường, phòng họp, trang trí',
                'icon' => 'map-marker-alt',
                'color' => '#007bff',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ăn uống',
                'description' => 'Tiệc buffet, nước uống, cà phê, đồ ăn nhẹ',
                'icon' => 'utensils',
                'color' => '#28a745',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trang thiết bị',
                'description' => 'Máy chiếu, âm thanh, laptop, camera',
                'icon' => 'laptop',
                'color' => '#17a2b8',
                'is_active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'In ấn & Văn phòng phẩm',
                'description' => 'Banner, tài liệu, bút giấy, file',
                'icon' => 'print',
                'color' => '#ffc107',
                'is_active' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quà tặng & Giải thưởng',
                'description' => 'Quà khách mời, giải nhất nhì ba, chứng nhận',
                'icon' => 'gift',
                'color' => '#e83e8c',
                'is_active' => true,
                'display_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tuyên truyền & Marketing',
                'description' => 'Thiết kế poster, quảng cáo, tờ rơi',
                'icon' => 'bullhorn',
                'color' => '#6f42c1',
                'is_active' => true,
                'display_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nhân sự',
                'description' => 'Thuê MC, diễn giả, kỹ thuật viên, bảo vệ',
                'icon' => 'user-tie',
                'color' => '#fd7e14',
                'is_active' => true,
                'display_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vận chuyển',
                'description' => 'Xe bus, vận chuyển thiết bị, gửi xe',
                'icon' => 'truck',
                'color' => '#20c997',
                'is_active' => true,
                'display_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Khác',
                'description' => 'Chi phí phát sinh, dự phòng',
                'icon' => 'ellipsis-h',
                'color' => '#6c757d',
                'is_active' => true,
                'display_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
