-- Script SQL để thêm các cột mới vào bảng events
-- Chạy script này trong phpMyAdmin hoặc MySQL client

-- Hạn chót đăng ký tham gia
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS registration_deadline DATETIME NULL AFTER end_time;

-- Người phụ trách chính
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS main_organizer VARCHAR(255) NULL AFTER registration_deadline;

-- Ban tổ chức / đội ngũ thực hiện
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS organizing_team TEXT NULL AFTER main_organizer;

-- Đơn vị phối hợp hoặc đồng tổ chức
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS co_organizers TEXT NULL AFTER organizing_team;

-- Liên hệ / thông tin người chịu trách nhiệm (JSON)
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS contact_info TEXT NULL AFTER co_organizers;

-- Kế hoạch chi tiết (Proposal / Plan file)
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS proposal_file VARCHAR(500) NULL AFTER contact_info;

-- Poster / ấn phẩm truyền thông
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS poster_file VARCHAR(500) NULL AFTER proposal_file;

-- Giấy phép / công văn xin tổ chức
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS permit_file VARCHAR(500) NULL AFTER poster_file;

-- Các khách mời
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS guests TEXT NULL AFTER permit_file;





























