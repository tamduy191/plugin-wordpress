<?php
defined( 'ABSPATH' ) || exit;

$page_icon = '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
$page_title = 'Thêm đánh giá';
$page_desc  = 'Tạo đánh giá thủ công hoặc để AI tự động viết đánh giá chân thực, đa dạng cho sản phẩm của bạn chỉ trong vài giây.';
$features   = [
    'Thêm đánh giá thủ công cho bất kỳ sản phẩm nào',
    'Tạo đánh giá hàng loạt bằng AI (Claude / Gemini / ChatGPT / DeepSeek)',
    'Phân bổ số sao tùy chỉnh — tự nhiên và đa dạng',
    'Upload ảnh kèm theo từng đánh giá',
    'Đánh dấu "Đã mua hàng" (Verified Purchase)',
];

include TWR_DIR . 'app/Views/admin/pro-upgrade.php';
