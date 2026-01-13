@extends('layouts.student')

@section('title', 'Liên hệ - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-phone text-teal"></i> Thông tin liên hệ
                    </h2>
                    <p class="text-muted mb-0">Liên hệ với các phòng ban và bộ phận hỗ trợ</p>
                </div>
                <div class="contact-logo">
                    <span class="logo-fpt">FPT</span>
                    <span class="logo-education">Education</span>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="content-card">
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-section">
                        <h4 class="section-title">
                            <i class="fas fa-map-marker-alt text-teal me-2"></i> Địa chỉ
                        </h4>
                        <div class="address-card">
                            <div class="brand-name-large">UNICLUBS</div>
                            <div class="address-detail">
                                <i class="fas fa-building me-2"></i>
                                Tòa nhà FPT Polytechnic, Phố Trịnh Văn Bô, Nam Từ Liêm, Hà Nội
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="contact-section">
                        <h4 class="section-title">
                            <i class="fas fa-phone text-teal me-2"></i> Điện thoại
                        </h4>
                        <div class="phone-card">
                            <div class="phone-item">
                                <i class="fas fa-headset me-2"></i>
                                <span>Số điện thoại liên hệ giải đáp ý kiến sinh viên:</span>
                            </div>
                            <div class="phone-number">1900996686</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Contacts -->
        <div class="content-card">
            <h4 class="section-title mb-4">
                <i class="fas fa-envelope text-teal me-2"></i> Địa chỉ email các phòng ban
            </h4>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="department-card">
                        <div class="department-header">
                            <i class="fas fa-users department-icon"></i>
                            <h5 class="department-name">Phòng dịch vụ sinh viên</h5>
                        </div>
                        <div class="department-email">dvsvpoly.hn@poly.edu.vn</div>
                        <div class="department-description">
                            Hỗ trợ các dịch vụ và giải đáp thắc mắc của sinh viên
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="department-card">
                        <div class="department-header">
                            <i class="fas fa-graduation-cap department-icon"></i>
                            <h5 class="department-name">Phòng Tổ chức và quản lý đào tạo</h5>
                        </div>
                        <div class="sub-department-list">
                            <div class="sub-department-item">
                                <strong>Đào tạo:</strong>
                                <span class="sub-email">daotaopoly.hn@fe.edu.vn</span>
                            </div>
                            <div class="sub-department-item">
                                <strong>Khảo thí:</strong>
                                <span class="sub-email">khaothipolyhn@fe.edu.vn</span>
                            </div>
                        </div>
                        <div class="department-description">
                            Quản lý chương trình đào tạo và tổ chức thi cử
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="department-card">
                        <div class="department-header">
                            <i class="fas fa-file-alt department-icon"></i>
                            <h5 class="department-name">Phòng hành chính</h5>
                        </div>
                        <div class="department-email">hanhchinhfplhn@fe.edu.vn</div>
                        <div class="department-description">
                            Xử lý các thủ tục hành chính và giấy tờ
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="department-card">
                        <div class="department-header">
                            <i class="fas fa-handshake department-icon"></i>
                            <h5 class="department-name">Phòng quan hệ doanh nghiệp</h5>
                        </div>
                        <div class="department-email">qhdn.poly@fpt.edu.vn</div>
                        <div class="department-description">
                            Kết nối sinh viên với các cơ hội thực tập và việc làm
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="content-card">
            <h4 class="section-title mb-4">
                <i class="fas fa-comment-dots text-teal me-2"></i> Ý kiến đóng góp
            </h4>
            
            <div class="feedback-card">
                <div class="feedback-content">
                    <p class="feedback-text">
                        <i class="fas fa-info-circle me-2"></i>
                        Ý kiến đóng góp chung gửi về 
                        <strong class="feedback-email">admin@university.edu.vn</strong>
                        bằng email @university.edu.vn
                    </p>
                    <div class="feedback-note">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            Vui lòng sử dụng email @university.edu.vn để gửi ý kiến đóng góp
                        </small>
                    </div>
                </div>
                <div class="feedback-action">
                    <a href="mailto:admin@university.edu.vn" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i> Gửi ý kiến
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-clock"></i> Giờ làm việc
            </h5>
            <div class="working-hours">
                <div class="hours-item">
                    <strong>Thứ 2 - Thứ 6:</strong>
                    <span>8:00 - 17:00</span>
                </div>
                <div class="hours-item">
                    <strong>Thứ 7:</strong>
                    <span>8:00 - 12:00</span>
                </div>
                <div class="hours-item">
                    <strong>Chủ nhật:</strong>
                    <span>Nghỉ</span>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-headset"></i> Hỗ trợ nhanh
            </h5>
            <div class="quick-support">
                <div class="support-item">
                    <i class="fas fa-phone text-teal"></i>
                    <div>
                        <strong>Hotline</strong>
                        <div class="support-detail">1900996686</div>
                    </div>
                </div>
                <div class="support-item">
                    <i class="fas fa-envelope text-teal"></i>
                    <div>
                        <strong>Email hỗ trợ</strong>
                        <div class="support-detail">dvsvpoly.hn@poly.edu.vn</div>
                    </div>
                </div>
                <div class="support-item">
                    <i class="fas fa-comments text-teal"></i>
                    <div>
                        <strong>Chat trực tuyến</strong>
                        <div class="support-detail">Có sẵn 24/7</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .contact-logo {
        display: flex;
        align-items: center;
        gap: 0.2rem;
    }
    
    .logo-fpt {
        background: linear-gradient(45deg, #0066cc, #00cc66);
        color: white;
        padding: 0.3rem 0.5rem;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.75rem;
    }
    
    .logo-education {
        color: #0066cc;
        font-weight: 600;
        font-size: 0.7rem;
    }
    
    .section-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .address-card {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #14b8a6;
    }
    
    .brand-name-large {
        color: #ff6600;
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 0.53rem;
        letter-spacing: 0.7px;
    }
    
    .address-detail {
        color: #666;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    .phone-card {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #14b8a6;
        text-align: center;
    }
    
    .phone-item {
        color: #666;
        margin-bottom: 0.67rem;
    }
    
    .phone-number {
        font-size: 0.95rem;
        font-weight: bold;
        color: #14b8a6;
    }
    
    .department-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.67rem;
        transition: all 0.2s ease;
        height: 100%;
    }
    
    .department-card:hover {
        box-shadow: 0 3px 8px rgba(20, 184, 166, 0.15);
        border-color: #14b8a6;
    }
    
    .department-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.67rem;
    }
    
    .department-icon {
        width: 21px;
        height: 21px;
        border-radius: 50%;
        background: #f0fdfa;
        color: #14b8a6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.53rem;
        font-size: 0.67rem;
    }
    
    .department-name {
        color: #333;
        font-weight: 600;
        margin: 0;
        font-size: 0.75rem;
    }
    
    .department-email {
        font-weight: bold;
        color: #14b8a6;
        font-size: 0.7rem;
        margin-bottom: 0.27rem;
    }
    
    .sub-department-list {
        margin-bottom: 0.5rem;
    }
    
    .sub-department-item {
        margin-bottom: 0.5rem;
        color: #666;
    }
    
    .sub-email {
        font-weight: bold;
        color: #14b8a6;
        margin-left: 0.5rem;
    }
    
    .department-description {
        color: #666;
        font-size: 0.65rem;
        line-height: 1.3;
    }
    
    .feedback-card {
        background: #f0fdfa;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .feedback-text {
        color: #333;
        margin: 0;
        font-size: 0.7rem;
        line-height: 1.4;
    }
    
    .feedback-email {
        color: #14b8a6;
        font-weight: bold;
    }
    
    .feedback-note {
        margin-top: 0.5rem;
    }
    
    .working-hours .hours-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .working-hours .hours-item:last-child {
        border-bottom: none;
    }
    
    .quick-support .support-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .quick-support .support-item:last-child {
        border-bottom: none;
    }
    
    .support-item i {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f0fdfa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.2rem;
    }
    
    .support-detail {
        color: #666;
        font-size: 0.9rem;
    }
</style>
@endpush
@endsection
