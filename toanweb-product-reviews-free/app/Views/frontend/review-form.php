<?php defined( 'ABSPATH' ) || exit;
$max_files     = (int) TWR_SettingsModel::get( 'max_file_count' );
$max_size_kb   = (int) TWR_SettingsModel::get( 'max_file_size' );
$max_size_mb   = round( $max_size_kb / 1024, 1 );
$upload_accept = TWR_SettingsModel::get( 'upload_accept' );
$accept_attr   = match ( $upload_accept ) {
    'images' => 'image/*',
    'videos' => 'video/*',
    default  => 'image/*,video/*',
};
$upload_label  = match ( $upload_accept ) {
    'images' => __( 'Thêm hình ảnh', 'toanweb-product-reviews' ),
    'videos' => __( 'Thêm video', 'toanweb-product-reviews' ),
    default  => __( 'Thêm hình ảnh / video', 'toanweb-product-reviews' ),
};
$upload_hint   = match ( $upload_accept ) {
    'images' => __( 'Nhấn hoặc kéo thả ảnh vào đây', 'toanweb-product-reviews' ),
    'videos' => __( 'Nhấn hoặc kéo thả video vào đây', 'toanweb-product-reviews' ),
    default  => __( 'Nhấn hoặc kéo thả ảnh/video vào đây', 'toanweb-product-reviews' ),
};
?>

<!-- Review Form Modal -->
<div class="twr-modal-overlay" id="twr-form-overlay" aria-hidden="true">
    <div class="twr-modal" role="dialog" aria-labelledby="twr-form-title">
        <div class="twr-modal__header">
            <h3 class="twr-modal__title" id="twr-form-title"><?php esc_html_e( 'Viết đánh giá của bạn', 'toanweb-product-reviews' ); ?></h3>
            <button class="twr-modal__close" id="twr-close-form" aria-label="<?php esc_attr_e( 'Đóng', 'toanweb-product-reviews' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="twr-modal__body">
            <form id="twr-review-form" enctype="multipart/form-data" novalidate>

                <input type="hidden" name="action"     value="twr_submit_review">
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
                <!-- Honeypot: bots fill this, humans don't -->
                <div aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;pointer-events:none">
                    <input type="text" name="twr_website" tabindex="-1" autocomplete="off">
                </div>

                <!-- Overall Rating -->
                <div class="twr-field" id="twr-field-rating">
                    <label class="twr-label"><?php esc_html_e( 'Đánh giá tổng thể', 'toanweb-product-reviews' ); ?> <span class="twr-required">*</span></label>
                    <div class="twr-star-picker" data-name="rating">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <button type="button" class="twr-star-pick" data-value="<?php echo esc_attr( $i ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></button>
                        <?php endfor; ?>
                        <span class="twr-star-pick-label"></span>
                    </div>
                    <input type="hidden" name="rating" id="twr-rating-input">
                    <span class="twr-field-error" id="twr-error-rating"></span>
                </div>


                <?php if ( ! is_user_logged_in() ) : ?>
                <!-- Guest fields -->
                <div class="twr-row">
                    <div class="twr-field twr-field--half" id="twr-field-author">
                        <label class="twr-label" for="twr-author"><?php esc_html_e( 'Tên của bạn', 'toanweb-product-reviews' ); ?> <span class="twr-required">*</span></label>
                        <input class="twr-input" type="text" name="author" id="twr-author" autocomplete="name" placeholder="<?php esc_attr_e( 'Nguyễn Văn A', 'toanweb-product-reviews' ); ?>">
                        <span class="twr-field-error" id="twr-error-author"></span>
                    </div>
                    <div class="twr-field twr-field--half" id="twr-field-email">
                        <label class="twr-label" for="twr-email"><?php esc_html_e( 'Email', 'toanweb-product-reviews' ); ?> <span class="twr-required">*</span></label>
                        <input class="twr-input" type="email" name="email" id="twr-email" autocomplete="email" placeholder="<?php esc_attr_e( 'email@example.com', 'toanweb-product-reviews' ); ?>">
                        <span class="twr-field-error" id="twr-error-email"></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Comment -->
                <div class="twr-field">
                    <label class="twr-label" for="twr-comment"><?php esc_html_e( 'Nội dung đánh giá', 'toanweb-product-reviews' ); ?></label>
                    <textarea class="twr-textarea" name="comment" id="twr-comment" rows="4" placeholder="<?php esc_attr_e( 'Chia sẻ trải nghiệm của bạn về sản phẩm...', 'toanweb-product-reviews' ); ?>"></textarea>
                </div>

                <!-- Photo / Video Upload -->
                <div class="twr-field">
                    <label class="twr-label">
                        <?php echo esc_html( $upload_label ); ?>
                    </label>
                    <div class="twr-upload-area" id="twr-upload-area">
                        <input type="file" name="twr_media[]" id="twr-media-input" multiple accept="<?php echo esc_attr( $accept_attr ); ?>" class="twr-upload-input">
                        <div class="twr-upload-placeholder" id="twr-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span><?php echo esc_html( $upload_hint ); ?></span>
                            <small><?php printf( esc_html__( 'Tối đa %d file, mỗi file ≤ %dMB', 'toanweb-product-reviews' ), $max_files, $max_size_mb ); ?></small>
                        </div>
                        <div class="twr-upload-preview" id="twr-upload-preview"></div>
                    </div>
                </div>

                <span class="twr-form-error" id="twr-form-error"></span>

                <div class="twr-form-footer">
                    <button type="submit" class="twr-btn-submit" id="twr-submit-btn">
                        <?php esc_html_e( 'Gửi đánh giá', 'toanweb-product-reviews' ); ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Success toast -->
<div class="twr-success-toast" id="twr-success-toast" aria-hidden="true">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="twr-toast-msg"><?php esc_html_e( 'Cảm ơn bạn đã đánh giá!', 'toanweb-product-reviews' ); ?></span>
</div>
