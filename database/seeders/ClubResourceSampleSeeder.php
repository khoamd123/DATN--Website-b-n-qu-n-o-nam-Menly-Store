<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClubResource;
use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Str;

class ClubResourceSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy club và user đầu tiên
        $club = Club::first();
        $user = User::first();
        
        if (!$club || !$user) {
            $this->command->error('Không tìm thấy club hoặc user. Vui lòng tạo dữ liệu mẫu trước.');
            return;
        }

        $this->command->info('Đang tạo 5 tài nguyên CLB mẫu...');

        // Tài nguyên 1: Tài liệu hướng dẫn lập trình
        ClubResource::create([
            'title' => 'Hướng dẫn lập trình Python cơ bản',
            'slug' => 'huong-dan-lap-trinh-python-co-ban',
            'description' => '<h3>Giới thiệu về Python</h3><p>Python là một ngôn ngữ lập trình cấp cao, dễ học và mạnh mẽ. Tài liệu này sẽ hướng dẫn bạn từ cơ bản đến nâng cao.</p><h4>Nội dung bao gồm:</h4><ul><li>Cú pháp cơ bản</li><li>Biến và kiểu dữ liệu</li><li>Cấu trúc điều khiển</li><li>Hàm và module</li><li>Xử lý file</li></ul>',
            'resource_type' => 'document',
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => 'club-resources/python-guide.pdf',
            'file_name' => 'python-guide.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048576, // 2MB
            'external_link' => null,
            'tags' => ['python', 'lập trình', 'cơ bản', 'hướng dẫn'],
            'status' => 'active',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        // Tài nguyên 2: Video bài giảng
        ClubResource::create([
            'title' => 'Video bài giảng JavaScript ES6+',
            'slug' => 'video-bai-giang-javascript-es6',
            'description' => '<h3>JavaScript ES6+ - Từ cơ bản đến nâng cao</h3><p>Khóa học video hoàn chỉnh về JavaScript ES6+ với các tính năng mới nhất.</p><h4>Chương trình học:</h4><ol><li>Arrow Functions</li><li>Template Literals</li><li>Destructuring</li><li>Classes</li><li>Modules</li><li>Promises & Async/Await</li></ol><p><strong>Thời lượng:</strong> 8 giờ</p>',
            'resource_type' => 'video',
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => 'club-resources/javascript-es6-course.mp4',
            'file_name' => 'javascript-es6-course.mp4',
            'file_type' => 'video/mp4',
            'file_size' => 52428800, // 50MB
            'external_link' => null,
            'tags' => ['javascript', 'es6', 'video', 'khóa học'],
            'status' => 'active',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        // Tài nguyên 3: Template thiết kế
        ClubResource::create([
            'title' => 'Template thiết kế UI/UX - Figma',
            'slug' => 'template-thiet-ke-ui-ux-figma',
            'description' => '<h3>Bộ template thiết kế UI/UX chuyên nghiệp</h3><p>Bộ sưu tập các template thiết kế UI/UX được tạo bằng Figma, phù hợp cho các dự án web và mobile.</p><h4>Bao gồm:</h4><ul><li>Dashboard templates</li><li>Landing page designs</li><li>Mobile app mockups</li><li>Icon sets</li><li>Color palettes</li><li>Typography guides</li></ul><p><em>File Figma có thể chỉnh sửa và tùy chỉnh theo nhu cầu.</em></p>',
            'resource_type' => 'other',
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => 'club-resources/ui-ux-templates.fig',
            'file_name' => 'ui-ux-templates.fig',
            'file_type' => 'application/octet-stream',
            'file_size' => 10485760, // 10MB
            'external_link' => null,
            'tags' => ['ui', 'ux', 'figma', 'template', 'thiết kế'],
            'status' => 'active',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        // Tài nguyên 4: Tài liệu tham khảo
        ClubResource::create([
            'title' => 'Tài liệu tham khảo React.js',
            'slug' => 'tai-lieu-tham-khao-react-js',
            'description' => '<h3>React.js - Tài liệu tham khảo đầy đủ</h3><p>Bộ tài liệu tham khảo toàn diện về React.js, bao gồm các khái niệm cốt lõi và best practices.</p><h4>Nội dung chính:</h4><ul><li><strong>Components:</strong> Functional & Class components</li><li><strong>State Management:</strong> useState, useReducer, Context API</li><li><strong>Hooks:</strong> useEffect, useCallback, useMemo</li><li><strong>Routing:</strong> React Router</li><li><strong>Testing:</strong> Jest, React Testing Library</li><li><strong>Performance:</strong> Optimization techniques</li></ul><p>📚 <strong>Định dạng:</strong> PDF, 150 trang</p>',
            'resource_type' => 'document',
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => 'club-resources/react-reference.pdf',
            'file_name' => 'react-reference.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 3145728, // 3MB
            'external_link' => null,
            'tags' => ['react', 'javascript', 'tham khảo', 'tài liệu'],
            'status' => 'active',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        // Tài nguyên 5: Link tài nguyên ngoài
        ClubResource::create([
            'title' => 'Khóa học miễn phí trên Coursera',
            'slug' => 'khoa-hoc-mien-phi-tren-coursera',
            'description' => '<h3>Danh sách khóa học miễn phí chất lượng cao</h3><p>Tổng hợp các khóa học lập trình miễn phí trên Coursera với chứng chỉ từ các trường đại học hàng đầu.</p><h4>Khóa học được đề xuất:</h4><ol><li><strong>CS50 Introduction to Computer Science</strong> - Harvard University</li><li><strong>Machine Learning</strong> - Stanford University</li><li><strong>Python for Everybody</strong> - University of Michigan</li><li><strong>Web Development</strong> - Johns Hopkins University</li><li><strong>Data Science</strong> - Johns Hopkins University</li></ol><p>🔗 <strong>Link truy cập:</strong> <a href="https://www.coursera.org" target="_blank">https://www.coursera.org</a></p>',
            'resource_type' => 'other',
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => null,
            'file_name' => null,
            'file_type' => null,
            'file_size' => null,
            'external_link' => 'https://www.coursera.org',
            'tags' => ['coursera', 'khóa học', 'miễn phí', 'chứng chỉ'],
            'status' => 'active',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        $this->command->info('✅ Đã tạo thành công 5 tài nguyên CLB mẫu!');
        $this->command->info('📁 Các tài nguyên bao gồm:');
        $this->command->info('   1. Hướng dẫn lập trình Python cơ bản');
        $this->command->info('   2. Video bài giảng JavaScript ES6+');
        $this->command->info('   3. Template thiết kế UI/UX - Figma');
        $this->command->info('   4. Tài liệu tham khảo React.js');
        $this->command->info('   5. Khóa học miễn phí trên Coursera');
    }
}