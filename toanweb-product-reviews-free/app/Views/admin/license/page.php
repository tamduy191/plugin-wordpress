<?php
defined( 'ABSPATH' ) || exit;

$page_icon = '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
$page_title = 'Nâng cấp lên PRO';
$page_desc  = 'Mở khóa toàn bộ tính năng: AI tạo đánh giá, import/export hàng loạt, giao diện đa dạng và nhiều hơn nữa.';
$features   = [
        'Tạo đánh giá AI hàng loạt — Claude, Gemini, ChatGPT, DeepSeek',
    'Import / Export CSV & Excel — không giới hạn',
    'Layout: grid, masonry, filter bar kiểu TikTok',
    'Tiêu chí đánh giá đa chiều (Hiệu năng, Pin, Camera...)',
    'Tự động gửi mã giảm giá sau khi khách đánh giá',
    'Cập nhật tính năng mới liên tục, hỗ trợ ưu tiên',
];

include TWR_DIR . 'app/Views/admin/pro-upgrade.php';
