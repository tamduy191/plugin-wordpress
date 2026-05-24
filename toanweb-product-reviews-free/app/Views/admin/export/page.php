<?php
defined( 'ABSPATH' ) || exit;

$page_icon = '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
$page_title = 'Xuất đánh giá';
$page_desc  = 'Backup toàn bộ đánh giá ra CSV/Excel hoặc chuyển sang plugin/nền tảng khác dễ dàng.';
$features   = [
    'Xuất tất cả đánh giá ra CSV hoặc Excel',
    'Lọc theo sản phẩm, số sao, ngày tháng',
    'Lọc theo trạng thái: đã duyệt / chờ duyệt',
    'Bao gồm ảnh, video và tiêu chí đánh giá',
    'Tương thích với tính năng Import để round-trip',
];

include TWR_DIR . 'app/Views/admin/pro-upgrade.php';
