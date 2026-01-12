<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClubResource;
use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Str;

class WebsiteProgrammingClubResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tìm CLB LẬP TRÌNH WEBSITE
        $club = Club::where('name', 'LIKE', '%LẬP TRÌNH WEBSITE%')
            ->orWhere('name', 'LIKE', '%LẬP TRÌNH%')
            ->orWhere('name', 'LIKE', '%WEBSITE%')
            ->first();

        if (!$club) {
            $this->command->warn('Không tìm thấy CLB LẬP TRÌNH WEBSITE. Vui lòng tạo CLB trước.');
            return;
        }

        // Tìm user để làm created_by (ưu tiên owner, sau đó là leader)
        $createdBy = $club->owner_id;
        if (!$createdBy) {
            $leader = \App\Models\ClubMember::where('club_id', $club->id)
                ->where('position', 'leader')
                ->first();
            if ($leader) {
                $createdBy = $leader->user_id;
            }
        }

        if (!$createdBy) {
            // Nếu không có owner hoặc leader, lấy user đầu tiên
            $createdBy = User::first()->id ?? 1;
        }

        $resources = [
            [
                'title' => 'Tài liệu HTML5 & CSS3 Cơ bản',
                'description' => '<h3>Hướng dẫn HTML5 & CSS3 từ cơ bản đến nâng cao</h3>
<p>Tài liệu này cung cấp kiến thức toàn diện về HTML5 và CSS3, phù hợp cho người mới bắt đầu học lập trình web.</p>
<h4>Nội dung bao gồm:</h4>
<ul>
    <li>Cấu trúc HTML5 và các thẻ mới</li>
    <li>CSS3: Flexbox, Grid Layout</li>
    <li>Responsive Design</li>
    <li>CSS Animations & Transitions</li>
    <li>Best Practices và Tips</li>
</ul>
<p><strong>Định dạng:</strong> PDF, 120 trang</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/html5-css3-guide.pdf',
                'file_name' => 'html5-css3-guide.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 2560000, // 2.5MB
                'external_link' => null,
                'tags' => ['html5', 'css3', 'web', 'frontend'],
                'view_count' => 0,
                'download_count' => 0,
            ],
            [
                'title' => 'JavaScript ES6+ - Tài liệu tham khảo',
                'description' => '<h3>JavaScript ES6+ - Tài liệu tham khảo đầy đủ</h3>
<p>Bộ tài liệu chi tiết về các tính năng mới trong JavaScript ES6+ và các phiên bản sau đó.</p>
<h4>Nội dung chính:</h4>
<ul>
    <li><strong>Arrow Functions:</strong> Cú pháp và cách sử dụng</li>
    <li><strong>Template Literals:</strong> String interpolation</li>
    <li><strong>Destructuring:</strong> Array và Object destructuring</li>
    <li><strong>Classes & Inheritance:</strong> OOP trong JavaScript</li>
    <li><strong>Modules:</strong> Import/Export</li>
    <li><strong>Promises & Async/Await:</strong> Xử lý bất đồng bộ</li>
    <li><strong>Spread & Rest Operators</strong></li>
</ul>
<p>📚 <strong>Định dạng:</strong> PDF, 180 trang</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/javascript-es6-reference.pdf',
                'file_name' => 'javascript-es6-reference.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 3840000, // 3.75MB
                'external_link' => null,
                'tags' => ['javascript', 'es6', 'reference', 'documentation'],
                'view_count' => 0,
                'download_count' => 0,
            ],
            [
                'title' => 'React.js - Hướng dẫn từ Zero to Hero',
                'description' => '<h3>React.js - Học từ cơ bản đến nâng cao</h3>
<p>Khóa học React.js hoàn chỉnh với các ví dụ thực tế và dự án minh họa.</p>
<h4>Chương trình học:</h4>
<ol>
    <li>Giới thiệu React và JSX</li>
    <li>Components: Functional & Class</li>
    <li>State & Props</li>
    <li>Hooks: useState, useEffect, useCallback, useMemo</li>
    <li>Context API & State Management</li>
    <li>React Router - Điều hướng</li>
    <li>Custom Hooks</li>
    <li>Performance Optimization</li>
    <li>Testing với Jest & React Testing Library</li>
    <li>Deployment & Best Practices</li>
</ol>
<p>🎯 <strong>Định dạng:</strong> PDF + Code Examples</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/react-zero-to-hero.pdf',
                'file_name' => 'react-zero-to-hero.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 5120000, // 5MB
                'external_link' => null,
                'tags' => ['react', 'javascript', 'frontend', 'framework'],
                'view_count' => 0,
                'download_count' => 0,
            ],
            [
                'title' => 'Node.js & Express - Backend Development Guide',
                'description' => '<h3>Xây dựng Backend với Node.js & Express</h3>
<p>Tài liệu hướng dẫn xây dựng RESTful API và ứng dụng backend với Node.js và Express framework.</p>
<h4>Nội dung bao gồm:</h4>
<ul>
    <li>Setup môi trường Node.js</li>
    <li>Express.js Fundamentals</li>
    <li>Routing & Middleware</li>
    <li>Database Integration (MongoDB, MySQL)</li>
    <li>Authentication & Authorization (JWT)</li>
    <li>File Upload & Handling</li>
    <li>Error Handling & Validation</li>
    <li>API Documentation với Swagger</li>
    <li>Testing với Jest & Supertest</li>
    <li>Deployment & Production Tips</li>
</ul>
<p>🚀 <strong>Định dạng:</strong> PDF, 200 trang</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/nodejs-express-guide.pdf',
                'file_name' => 'nodejs-express-guide.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 4500000, // 4.5MB
                'external_link' => null,
                'tags' => ['nodejs', 'express', 'backend', 'api'],
                'view_count' => 0,
                'download_count' => 0,
            ],
            [
                'title' => 'Git & GitHub - Hướng dẫn sử dụng',
                'description' => '<h3>Git & GitHub - Quản lý phiên bản code hiệu quả</h3>
<p>Tài liệu hướng dẫn sử dụng Git và GitHub từ cơ bản đến nâng cao, phù hợp cho mọi lập trình viên.</p>
<h4>Nội dung chính:</h4>
<ul>
    <li><strong>Git Basics:</strong> init, add, commit, status</li>
    <li><strong>Branching & Merging:</strong> Tạo và quản lý nhánh</li>
    <li><strong>Remote Repositories:</strong> GitHub, GitLab</li>
    <li><strong>Collaboration:</strong> Pull Requests, Code Review</li>
    <li><strong>Git Workflow:</strong> Git Flow, GitHub Flow</li>
    <li><strong>Advanced Topics:</strong> Rebase, Cherry-pick, Stash</li>
    <li><strong>Best Practices:</strong> Commit messages, .gitignore</li>
    <li><strong>Troubleshooting:</strong> Xử lý conflict, undo changes</li>
</ul>
<p>📖 <strong>Định dạng:</strong> PDF, 100 trang</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/git-github-guide.pdf',
                'file_name' => 'git-github-guide.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 2048000, // 2MB
                'external_link' => null,
                'tags' => ['git', 'github', 'version-control', 'tools'],
                'view_count' => 0,
                'download_count' => 0,
            ],
            [
                'title' => 'Full Stack Web Development - Roadmap 2026',
                'description' => '<h3>Lộ trình học Full Stack Web Development 2026</h3>
<p>Tài liệu tổng hợp lộ trình học Full Stack Web Development với các công nghệ mới nhất và xu hướng trong năm 2026.</p>
<h4>Lộ trình học:</h4>
<ol>
    <li><strong>Frontend Fundamentals:</strong> HTML, CSS, JavaScript</li>
    <li><strong>Frontend Framework:</strong> React, Vue, hoặc Angular</li>
    <li><strong>Backend Development:</strong> Node.js, Python (Django/Flask), hoặc PHP (Laravel)</li>
    <li><strong>Database:</strong> MySQL, PostgreSQL, MongoDB</li>
    <li><strong>DevOps & Tools:</strong> Docker, CI/CD, Cloud (AWS, Azure)</li>
    <li><strong>Advanced Topics:</strong> Microservices, GraphQL, WebSockets</li>
</ol>
<h4>Tài nguyên bổ sung:</h4>
<ul>
    <li>Danh sách khóa học miễn phí</li>
    <li>Dự án thực hành đề xuất</li>
    <li>Checklist kỹ năng cần có</li>
    <li>Tips phỏng vấn xin việc</li>
</ul>
<p>🗺️ <strong>Định dạng:</strong> PDF, 80 trang</p>',
                'resource_type' => 'document',
                'file_path' => 'club-resources/fullstack-roadmap-2026.pdf',
                'file_name' => 'fullstack-roadmap-2026.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 3072000, // 3MB
                'external_link' => null,
                'tags' => ['fullstack', 'roadmap', 'web-development', 'guide'],
                'view_count' => 0,
                'download_count' => 0,
            ],
        ];

        foreach ($resources as $resourceData) {
            // Kiểm tra xem tài nguyên đã tồn tại chưa (tránh duplicate)
            $existingResource = ClubResource::where('club_id', $club->id)
                ->where('title', $resourceData['title'])
                ->first();

            if ($existingResource) {
                $this->command->info("Tài nguyên '{$resourceData['title']}' đã tồn tại. Bỏ qua.");
                continue;
            }

            ClubResource::create([
                'club_id' => $club->id,
                'user_id' => $createdBy,
                'title' => $resourceData['title'],
                'slug' => Str::slug($resourceData['title']) . '-' . Str::random(6),
                'description' => $resourceData['description'],
                'resource_type' => $resourceData['resource_type'],
                'file_path' => $resourceData['file_path'],
                'file_name' => $resourceData['file_name'],
                'file_type' => $resourceData['file_type'],
                'file_size' => $resourceData['file_size'],
                'external_link' => $resourceData['external_link'] ?? null,
                'tags' => $resourceData['tags'] ?? [],
                'status' => 'active',
                'view_count' => $resourceData['view_count'] ?? 0,
                'download_count' => $resourceData['download_count'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("Đã tạo tài nguyên: {$resourceData['title']}");
        }

        $this->command->info("Hoàn thành! Đã tạo " . count($resources) . " tài nguyên cho CLB: {$club->name}");
    }
}

