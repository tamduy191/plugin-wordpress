<?php
defined( 'ABSPATH' ) || exit;

$page_icon = '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>';
$page_title = 'Nhập đánh giá';
$page_desc  = 'Import hàng trăm đánh giá từ file CSV/Excel chỉ trong vài bước, hỗ trợ nhiều định dạng phổ biến.';
$features   = [
    'Import hàng loạt từ file CSV hoặc Excel (.xlsx)',
    'Hỗ trợ format Aliexpress, Shopee, WooCommerce...',
    'Mapping cột linh hoạt — không cần đúng tên cột',
    'Bỏ qua đánh giá trùng lặp tự động',
    'Xem trước dữ liệu trước khi import',
];

include TWR_DIR . 'app/Views/admin/pro-upgrade.php';
